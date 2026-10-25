<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

/** Alur: minta (1 atau beberapa file) -> persetujuan manusia ATAU kode manual -> kode 6 angka -> unduh -> selesai. */
class DownloadFlow
{
    public const CODE_MINUTES = 60;
    public const PENDING_HOURS = 24;

    public function __construct(private SupabaseAdmin $db) {}

    /** $manual = baris access_codes bila memakai kode manual (langsung disetujui). */
    public function create(array $files, string $name, string $purpose, ?string $ip, ?string $ua, ?array $manual = null): array
    {
        $token = Str::random(40);
        $ids = array_values(array_map(fn ($f) => $f['id'], $files));
        $row = [
            'file_id' => $ids[0], 'file_ids' => $ids, 'requester_name' => $name, 'purpose' => $purpose,
            'token_hash' => hash('sha256', $token), 'ip_address' => $ip, 'user_agent' => substr((string) $ua, 0, 250),
        ];
        if ($manual) {
            $row += [
                'status' => 'approved', 'manual_code_id' => $manual['id'],
                'code_expires_at' => now()->addMinutes(self::CODE_MINUTES)->toIso8601String(),
                'decided_at' => now()->toIso8601String(),
            ];
        }

        $res = $this->db->insert('download_requests', $row);
        $id = is_array($res['data']) ? ($res['data'][0]['id'] ?? null) : null;

        return $res['ok'] && $id
            ? ['ok' => true, 'id' => $id, 'token' => $token, 'row' => $res['data'][0]]
            : ['ok' => false, 'error' => $res['error'] ?: 'Gagal menyimpan permintaan.'];
    }

    public function ids(array $req): array
    {
        $ids = $req['file_ids'] ?? null;

        return is_array($ids) && $ids ? array_values($ids) : [$req['file_id']];
    }

    /** Baris file untuk semua id di permintaan (urutan sama). */
    public function filesOf(array $req): array
    {
        $ids = $this->ids($req);
        $rows = collect($this->db->get('confidential_files', ['id' => 'in.(' . implode(',', $ids) . ')']))->keyBy('id');

        return array_values(array_filter(array_map(fn ($i) => $rows[$i] ?? null, $ids)));
    }

    public function get(string $id): ?array
    {
        if (! preg_match('/^[0-9a-f\-]{36}$/i', $id)) {
            return null;
        }
        $r = $this->db->first('download_requests', ['id' => 'eq.' . $id]);

        return $r ? $this->fresh($r) : null;
    }

    public function fresh(array $r): array
    {
        $old = $r['status'] === 'pending' && Carbon::parse($r['created_at'])->addHours(self::PENDING_HOURS)->isPast();
        $dead = $r['status'] === 'approved' && ! empty($r['code_expires_at']) && Carbon::parse($r['code_expires_at'])->isPast();

        if ($old || $dead) {
            $this->db->update('download_requests', ['id' => 'eq.' . $r['id']], ['status' => 'expired']);
            $r['status'] = 'expired';
        }

        return $r;
    }

    public function approve(string $id, ?string $by): array
    {
        $r = $this->get($id);
        if (! $r) {
            return ['ok' => false, 'error' => 'Permintaan tidak ditemukan.'];
        }
        if ($r['status'] !== 'pending') {
            return ['ok' => false, 'error' => 'Permintaan ini sudah diproses (' . $r['status'] . ').', 'status' => $r['status']];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $res = $this->db->update('download_requests', ['id' => 'eq.' . $id, 'status' => 'eq.pending'], [
            'status' => 'approved', 'code_enc' => Crypt::encryptString($code),
            'code_expires_at' => now()->addMinutes(self::CODE_MINUTES)->toIso8601String(),
            'decided_by' => $by, 'decided_at' => now()->toIso8601String(),
        ]);

        return ($res['ok'] && ! empty($res['data']))
            ? ['ok' => true, 'code' => $code]
            : ['ok' => false, 'error' => 'Permintaan sudah diproses orang lain.'];
    }

    public function reject(string $id, ?string $by): array
    {
        $r = $this->get($id);
        if (! $r) {
            return ['ok' => false, 'error' => 'Permintaan tidak ditemukan.'];
        }
        if ($r['status'] !== 'pending') {
            return ['ok' => false, 'error' => 'Permintaan ini sudah diproses (' . $r['status'] . ').', 'status' => $r['status']];
        }

        $res = $this->db->update('download_requests', ['id' => 'eq.' . $id, 'status' => 'eq.pending'], [
            'status' => 'rejected', 'decided_by' => $by, 'decided_at' => now()->toIso8601String(),
        ]);

        return ['ok' => $res['ok'] && ! empty($res['data']), 'error' => 'Permintaan sudah diproses orang lain.'];
    }

    /** Pemohon membatalkan, atau menutup permintaan (selesai) supaya bisa mengajukan lagi. */
    public function close(array $r, string $status): void
    {
        if (in_array($r['status'], ['pending', 'approved'], true)) {
            $this->db->update('download_requests', ['id' => 'eq.' . $r['id']], ['status' => $status]);
        }
    }

    /** Catat file yang diunduh; bila semua file sudah diunduh, permintaan selesai ("used"). Mengembalikan true jika selesai. */
    public function markDownloaded(array $r, string $fileId): bool
    {
        $done = array_values(array_unique(array_merge((array) ($r['downloaded_ids'] ?? []), [$fileId])));
        $all = count(array_intersect($this->ids($r), $done)) >= count($this->ids($r));

        $this->db->update('download_requests', ['id' => 'eq.' . $r['id']], array_filter([
            'downloaded_ids' => $done, 'downloaded_at' => now()->toIso8601String(), 'status' => $all ? 'used' : null,
        ], fn ($v) => $v !== null));

        return $all;
    }

    /** Permintaan milik browser ini (terikat lewat token di sesi). Yang sudah selesai dianggap tidak ada. */
    public function forSession(): ?array
    {
        $cur = session('dl_current');
        if (! is_array($cur) || empty($cur['id']) || empty($cur['token'])) {
            return null;
        }

        $r = $this->get($cur['id']);
        if (! $r || ! hash_equals($r['token_hash'], hash('sha256', $cur['token'])) || $r['status'] === 'used') {
            return null;
        }

        return $r;
    }

    public function codeOf(array $r): ?string
    {
        try {
            return ! empty($r['code_enc']) ? Crypt::decryptString($r['code_enc']) : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}