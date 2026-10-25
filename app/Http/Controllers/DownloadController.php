<?php

namespace App\Http\Controllers;

use App\Mail\DownloadRequested;
use App\Services\DownloadFiles;
use App\Services\DownloadFlow;
use App\Services\Supabase;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

/** Alur publik: pilih file -> minta (atau kode manual) -> kode 6 angka -> unduh -> selesai. */
class DownloadController extends Controller
{
    public function __construct(private SupabaseAdmin $db, private DownloadFlow $flow) {}

    public function files(Supabase $sb)
    {
        return response()->json(array_map(fn ($d) => ['id' => $d['id'], 'title' => $d['title']], $sb->documents()));
    }

    /** File aktif sesuai id yang diminta (urutan sama). Null bila ada yang tidak tersedia. */
    private function pick(array $ids): ?array
    {
        $ids = array_values(array_unique($ids));
        $rows = collect($this->db->get('confidential_files', ['id' => 'in.(' . implode(',', $ids) . ')', 'is_active' => 'eq.true']))->keyBy('id');
        $out = array_map(fn ($i) => $rows[$i] ?? null, $ids);

        return in_array(null, $out, true) ? null : $out;
    }

    private function rules(): array
    {
        return [
            'files'   => 'required|array|min:1|max:5',
            'files.*' => 'uuid',
            'name'    => 'required|string|max:100',
        ];
    }

    private function begin(array $res): void
    {
        session(['dl_current' => ['id' => $res['id'], 'token' => $res['token']], 'dl_fail' => 0]);
        Cache::forget('admin_pending_downloads');
    }

    /** Pengunjung mengisi nama + kebutuhan. Email dikirim ke perusahaan. */
    public function request(Request $r, Supabase $sb)
    {
        if ($r->filled('website')) { // jebakan bot
            return response()->json(['ok' => true]);
        }

        $d = $r->validate($this->rules() + ['purpose' => 'required|string|min:5|max:500'], [
            'name.required' => 'Nama wajib diisi.', 'purpose.required' => 'Kebutuhan wajib diisi.',
            'purpose.min' => 'Jelaskan kebutuhan Anda minimal 5 karakter.', 'files.required' => 'Pilih minimal satu dokumen.',
        ]);

        $files = $this->pick($d['files']);
        if (! $files) {
            return response()->json(['message' => 'Sebagian dokumen tidak tersedia lagi.'], 404);
        }

        $res = $this->flow->create($files, trim($d['name']), trim($d['purpose']), $r->ip(), $r->userAgent());
        if (! $res['ok']) {
            return response()->json(['message' => 'Permintaan gagal dikirim. Coba lagi sebentar lagi.'], 500);
        }

        $this->begin($res);

        return response()->json(['ok' => true, 'mailed' => $this->notify($res['row'], $files, $sb)]);
    }

    /**
     * Kode manual (cadangan): berlaku hanya jika NAMA yang diisi sama dengan kolom "Untuk siapa" pada kode itu.
     * Langsung disetujui, tanpa menunggu email.
     */
    public function manual(Request $r)
    {
        $d = $r->validate($this->rules() + ['code' => 'required|string|max:30', 'purpose' => 'nullable|string|max:500'], [
            'name.required' => 'Nama wajib diisi.', 'code.required' => 'Masukkan kode manual.', 'files.required' => 'Pilih minimal satu dokumen.',
        ]);

        $row = $this->db->first('access_codes', [
            'code_hash' => 'eq.' . hash('sha256', strtoupper(trim($d['code']))), 'is_active' => 'eq.true',
        ]);
        $live = $row
            && (! $row['expires_at'] || Carbon::parse($row['expires_at'])->isFuture())
            && (! $row['max_uses'] || $row['used_count'] < $row['max_uses']);
        if (! $live) {
            return response()->json(['message' => 'Kode tidak valid, kedaluwarsa, atau sudah habis dipakai.'], 422);
        }

        $norm = fn ($s) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $s)));
        if ($norm($d['name']) !== $norm($row['label'])) {
            return response()->json(['message' => 'Nama tidak sesuai dengan pemilik kode ini.'], 422);
        }

        $files = $this->pick($d['files']);
        if (! $files) {
            return response()->json(['message' => 'Sebagian dokumen tidak tersedia lagi.'], 404);
        }
        if (! empty($row['file_id']) && count(array_filter($files, fn ($f) => $f['id'] !== $row['file_id']))) {
            return response()->json(['message' => 'Kode ini tidak berlaku untuk dokumen yang dipilih.'], 422);
        }

        $res = $this->flow->create($files, trim($d['name']), trim((string) ($d['purpose'] ?? '')) ?: 'Memakai kode manual', $r->ip(), $r->userAgent(), $row);
        if (! $res['ok']) {
            return response()->json(['message' => 'Gagal memproses. Coba lagi.'], 500);
        }

        $this->db->update('access_codes', ['id' => 'eq.' . $row['id']], ['used_count' => $row['used_count'] + 1]);
        $this->begin($res);
        $g = (array) session('dl_grants', []);
        $g[$res['id']] = Carbon::parse($res['row']['code_expires_at'])->timestamp;
        session(['dl_grants' => $g]);

        return response()->json(['ok' => true, 'manual' => true]);
    }

    private function notify(array $req, array $files, Supabase $sb): bool
    {
        try {
            $to = $sb->setting('notify_email') ?: ($sb->company()['email'] ?? null);
            if (! $to) {
                return false;
            }
            $owner = match ($files[0]['owner_type'] ?? 'company') {
                'project' => 'Lampiran proyek', 'service' => 'Lampiran layanan', default => 'Dokumen perusahaan',
            };
            Mail::to($to)->send(new DownloadRequested($req, $files, $owner, self::reviewUrl($req['id']), route('admin.downloads')));

            return true;
        } catch (Throwable $e) {
            Log::warning('Email permintaan download gagal: ' . $e->getMessage());

            return false;
        }
    }

    public static function reviewUrl(string $id): string
    {
        return URL::temporarySignedRoute('dl.review', now()->addDays(2), ['id' => $id]);
    }

    private function verified(array $r): bool
    {
        $g = (array) session('dl_grants', []);

        return isset($g[$r['id']]) && $g[$r['id']] > time();
    }

    private function fileList(array $r): array
    {
        $done = (array) ($r['downloaded_ids'] ?? []);

        return array_map(fn ($f) => [
            'id' => $f['id'], 'title' => $f['title'], 'size' => (int) ($f['file_size'] ?? 0), 'done' => in_array($f['id'], $done, true),
            'url' => route('dl.unduh', [$r['id'], $f['id']]),
        ], $this->flow->filesOf($r));
    }

    /** Status permintaan milik browser ini (dipantau halaman setiap beberapa detik). */
    public function status()
    {
        $r = $this->flow->forSession();
        if (! $r) {
            return response()->json(['state' => 'none']);
        }

        $files = $this->flow->filesOf($r);
        $out = ['state' => $r['status'], 'titles' => array_column($files, 'title'), 'manual' => ! empty($r['manual_code_id'])];

        if ($r['status'] === 'approved') {
            $out['verified'] = $this->verified($r);
            $out['expires_at'] = $r['code_expires_at'];
            if (! $out['manual']) {
                $out['code'] = $this->flow->codeOf($r);
            }
            if ($out['verified']) {
                $out['files'] = $this->fileList($r);
            }
        }

        return response()->json($out);
    }

    /** Pemohon memasukkan kode 6 angka. */
    public function verify(Request $r)
    {
        $d = $r->validate(['code' => 'required|digits:6'], ['code.digits' => 'Kode terdiri dari 6 angka.', 'code.required' => 'Masukkan kode 6 angka.']);

        $req = $this->flow->forSession();
        if (! $req || $req['status'] !== 'approved') {
            return response()->json(['ok' => false, 'message' => 'Belum ada kode aktif. Ajukan permintaan terlebih dahulu.'], 422);
        }

        if (! hash_equals((string) $this->flow->codeOf($req), $d['code'])) {
            $fails = (int) session('dl_fail', 0) + 1;
            session(['dl_fail' => $fails]);

            if ($fails >= 8) {
                $this->flow->close($req, 'expired');
                session()->forget(['dl_fail', 'dl_current']);

                return response()->json(['ok' => false, 'message' => 'Terlalu banyak percobaan salah. Ajukan permintaan baru.'], 429);
            }

            return response()->json(['ok' => false, 'message' => 'Kode salah. Periksa kembali kode 6 angka Anda.'], 422);
        }

        $g = (array) session('dl_grants', []);
        $g[$req['id']] = Carbon::parse($req['code_expires_at'])->timestamp;
        session(['dl_grants' => $g, 'dl_fail' => 0]);

        return response()->json(['ok' => true, 'files' => $this->fileList($req)]);
    }

    /** Batalkan permintaan (atau tutup yang sudah selesai) supaya bisa mengajukan lagi tanpa menunggu. */
    public function cancel()
    {
        $r = $this->flow->forSession();
        if ($r) {
            $this->flow->close($r, 'expired');
        }
        session()->forget(['dl_current', 'dl_fail']);
        Cache::forget('admin_pending_downloads');

        return response()->json(['ok' => true]);
    }

    public function finish()
    {
        $r = $this->flow->forSession();
        if ($r) {
            $this->flow->close($r, 'used');
        }
        session()->forget(['dl_current', 'dl_fail']);

        return response()->json(['ok' => true]);
    }

    /** Unduh satu file dari permintaan yang sudah terverifikasi (di browser yang sama, dalam 1 jam). */
    public function download(string $id, string $file)
    {
        $g = (array) session('dl_grants', []);
        abort_unless(isset($g[$id]) && $g[$id] > time(), 403, 'Akses unduhan sudah berakhir. Ajukan permintaan baru.');

        $req = $this->flow->get($id);
        abort_unless($req && $req['status'] === 'approved' && in_array($file, $this->flow->ids($req), true), 403, 'Akses unduhan sudah berakhir.');

        $row = collect($this->flow->filesOf($req))->firstWhere('id', $file);
        abort_unless($row && ($row['is_active'] ?? true), 404);

        if ($this->flow->markDownloaded($req, $file)) { // semua file sudah diunduh: kembali ke menu awal
            session()->forget(['dl_current', 'dl_fail']);
            Cache::forget('admin_pending_downloads');
        }

        return DownloadFiles::response($row);
    }

    /** Halaman tinjau dari tautan email (bertanda tangan). */
    public function review(string $id)
    {
        $req = $this->flow->get($id);
        abort_unless($req, 404);

        return view('dl.review', [
            'req'   => $req,
            'files' => $this->flow->filesOf($req),
            'urls'  => [
                'setujui' => URL::temporarySignedRoute('dl.decide', now()->addDays(2), ['id' => $id, 'act' => 'setujui']),
                'tolak'   => URL::temporarySignedRoute('dl.decide', now()->addDays(2), ['id' => $id, 'act' => 'tolak']),
            ],
        ]);
    }

    public function decide(string $id, string $act)
    {
        abort_unless(in_array($act, ['setujui', 'tolak'], true), 404);

        $res = $act === 'setujui' ? $this->flow->approve($id, null) : $this->flow->reject($id, null);
        Cache::forget('admin_pending_downloads');

        return redirect(self::reviewUrl($id))->with('hasil', $res + ['act' => $act]);
    }
}