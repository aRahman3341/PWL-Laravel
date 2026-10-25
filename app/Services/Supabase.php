<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Klien sederhana untuk Supabase REST API (PostgREST).
 * Dipakai di sisi server saja — service key TIDAK boleh sampai ke browser.
 */
class Supabase
{
    private ?array $company = null;

    private const COMPANY_DEFAULTS = [
        'name'         => 'PT. Purna Wahana Lestari Konsultan',
        'founded_year' => 2007,
        'about'        => 'PT. Purna Wahana Lestari Konsultan didirikan di Bandung pada tahun 2007 dan menjadi Perseroan Terbatas pada 2017. Kami memberikan jasa konsultansi konstruksi dan non konstruksi yang efisien, efektif, dan tepat guna.',
        'vision'       => '',
        'mission'      => '',
        'address'      => 'Komp. Cibiru Asri Blok L No. 02 Desa Cibiru Wetan, Kec. Cileunyi, Kab. Bandung',
        'phone'        => '022-7813823',
        'email'        => 'pt.pwlkonsultan@gmail.com',
        'whatsapp'     => '6289644113257',
    ];

    public function configured(): bool
    {
        return config('supabase.url') !== '' && config('supabase.key') !== '';
    }

    /** Request mentah ke REST API. Selalu mengembalikan array: ok, code, data, error. */
    public function request(string $method, string $path, array $query = [], ?array $json = null): array
    {
        if (! $this->configured()) {
            return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'SUPABASE_URL / SUPABASE_SERVICE_KEY belum diisi di .env'];
        }

        $key = config('supabase.key');
        $url = config('supabase.url') . '/rest/v1/' . ltrim($path, '/');

        try {
            $http = Http::withHeaders(['apikey' => $key])->acceptJson()->timeout(10);
            // Key lama (service_role) berbentuk JWT "eyJ..." dan perlu header Authorization.
            // Key baru (sb_secret_...) cukup lewat header apikey.
            if (str_starts_with($key, 'eyJ')) {
                $http = $http->withToken($key);
            }

            $res = strtoupper($method) === 'POST'
                ? $http->withHeaders(['Prefer' => 'return=minimal'])->post($url, $json ?? [])
                : $http->get($url, $query);

            return [
                'ok'    => $res->successful(),
                'code'  => $res->status(),
                'data'  => $res->json() ?? [],
                'error' => $res->successful() ? '' : substr($res->body(), 0, 200),
            ];
        } catch (Throwable $e) {
            Log::warning('Supabase error: ' . $e->getMessage());
            return ['ok' => false, 'code' => 0, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    /** Ambil baris dari sebuah tabel (dengan cache). Gagal = array kosong. */
    public function select(string $table, array $query = []): array
    {
        $ttl = (int) config('supabase.cache_ttl');
        $cacheKey = 'sb:' . $table . ':' . md5(json_encode($query));

        if ($ttl > 0 && ($hit = Cache::get($cacheKey)) !== null) {
            return $hit;
        }

        $r = $this->request('GET', $table, $query + ['select' => '*']);
        $rows = ($r['ok'] && is_array($r['data']) && array_is_list($r['data'])) ? $r['data'] : [];

        if ($r['ok'] && $ttl > 0) {
            Cache::put($cacheKey, $rows, $ttl);
        }

        return $rows;
    }

    public function company(): array
    {
        if ($this->company === null) {
            $row = $this->select('company_info', ['limit' => 1])[0] ?? [];
            $row = array_filter($row, fn ($v) => $v !== null && $v !== '');
            $this->company = array_merge(self::COMPANY_DEFAULTS, $row);
        }

        return $this->company;
    }

    /** Layanan dikelompokkan per kategori: ['Konstruksi' => [...], 'Non Konstruksi' => [...]] */
    public function servicesByCategory(): array
    {
        $grouped = [];

        foreach ($this->select('services', [
            'is_active' => 'eq.true',
            'order' => 'sort_order.asc',
        ]) as $s) {
            $grouped[$s['category']][] = [
                'id' => $s['id'] ?? null,
                'title' => $s['name'] ?? '',
                'name' => $s['name'] ?? '',
                'code' => $s['code'] ?? '',
                'description' => $s['description'] ?? '',
                'qualification' => $s['qualification'] ?? '',
                'icon' => $s['icon'] ?? '',
                'sort_order' => $s['sort_order'] ?? 0,
            ];
        }

        return $grouped;
    }

    public function projects(string $filter = 'semua'): array
    {
        $q = ['is_active' => 'eq.true', 'order' => 'year.desc.nullslast,sort_order.asc'];
        if ($filter !== 'semua') {
            $q['category'] = 'eq.' . $filter;
        }

        // View memakai key 'title' dan 'client'; kolom aslinya 'name' dan 'client_name'.
        return array_map(fn ($p) => $p + [
            'title'  => $p['name'] ?? '',
            'client' => $p['client_name'] ?? '',
        ], $this->select('projects', $q));
    }

    /** Pengurus perusahaan (tabel management), urut sesuai sort_order. */
    public function management(): array
    {
        return $this->select('management', ['is_active' => 'eq.true', 'order' => 'sort_order.asc']);
    }

    /** Dokumen perusahaan yang bisa diminta pengunjung (hanya id dan judul; file tetap butuh persetujuan). */
    public function documents(): array
    {
        return $this->select('confidential_files', [
            'select' => 'id,title', 'is_active' => 'eq.true', 'owner_type' => 'eq.company', 'order' => 'created_at.desc',
        ]);
    }

    /** Peta lampiran proyek/layanan yang ditampilkan: "project:{id}" / "service:{id}" => id file. */
    public function attachments(): array
    {
        $rows = $this->select('confidential_files', [
            'select' => 'id,owner_type,owner_id', 'is_active' => 'eq.true', 'owner_type' => 'neq.company', 'owner_id' => 'not.is.null',
        ]);

        $map = [];
        foreach ($rows as $r) {
            $map[$r['owner_type'] . ':' . $r['owner_id']] = $r['id'];
        }

        return $map;
    }

    /** Pengaturan situs (tabel site_settings): key => value. */
    public function settings(): array
    {
        $out = [];
        foreach ($this->select('site_settings') as $r) {
            $out[$r['key']] = $r['value'];
        }

        return $out;
    }

    public function setting(string $key, ?string $default = null): ?string
    {
        $v = $this->settings()[$key] ?? null;

        return ($v !== null && $v !== '') ? $v : $default;
    }

    /** Dokumen legalitas yang boleh tampil publik (tabel legal_documents). */
    public function legalDocuments(): array
    {
        return $this->select('legal_documents', ['is_active' => 'eq.true', 'order' => 'sort_order.asc']);
    }

    public function insertMessage(array $data): array
    {
        return $this->request('POST', 'messages', [], $data);
    }
}
