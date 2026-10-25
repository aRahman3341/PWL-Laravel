<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Operasi tulis (CRUD), akun (Supabase Auth) dan Storage untuk panel admin.
 * Hanya dipakai di server. Memakai service key.
 */
class SupabaseAdmin
{
    private function http(int $timeout = 20): PendingRequest
    {
        $key = (string) config('supabase.key');
        $h = Http::withHeaders(['apikey' => $key])->acceptJson()->timeout($timeout);

        // Key lama (service_role) berbentuk JWT "eyJ..." dan perlu header Authorization.
        return str_starts_with($key, 'eyJ') ? $h->withToken($key) : $h;
    }

    private function base(): string
    {
        return rtrim((string) config('supabase.url'), '/');
    }

    private function rest(string $path): string
    {
        return $this->base() . '/rest/v1/' . ltrim($path, '/');
    }

    private function storage(string $path): string
    {
        return $this->base() . '/storage/v1/' . ltrim($path, '/');
    }

    private function result(Response $res): array
    {
        return [
            'ok'    => $res->successful(),
            'data'  => $res->json() ?? [],
            'error' => $res->successful() ? '' : substr($res->body(), 0, 300),
        ];
    }

    /** Jalankan request; kegagalan jaringan menjadi hasil ok=false (bukan halaman error). */
    private function run(callable $fn, int $timeout = 20): array
    {
        try {
            return $this->result($fn($this->http($timeout)));
        } catch (Throwable $e) {
            return ['ok' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------- tabel

    public function get(string $table, array $query = []): array
    {
        $r = $this->run(fn ($h) => $h->get($this->rest($table), $query + ['select' => '*']));

        return ($r['ok'] && is_array($r['data']) && array_is_list($r['data'])) ? $r['data'] : [];
    }

    public function first(string $table, array $query = []): ?array
    {
        return $this->get($table, $query + ['limit' => 1])[0] ?? null;
    }

    public function insert(string $table, array $data): array
    {
        return $this->run(fn ($h) => $h->withHeaders(['Prefer' => 'return=representation'])
            ->post($this->rest($table), $data));
    }

    public function upsert(string $table, array $data, string $onConflict = 'id'): array
    {
        return $this->run(fn ($h) => $h->withHeaders(['Prefer' => 'resolution=merge-duplicates,return=representation'])
            ->post($this->rest($table) . '?on_conflict=' . $onConflict, $data));
    }

    public function update(string $table, array $filter, array $data): array
    {
        return $this->run(fn ($h) => $h->withHeaders(['Prefer' => 'return=representation'])
            ->patch($this->rest($table) . '?' . http_build_query($filter), $data));
    }

    public function delete(string $table, array $filter): array
    {
        return $this->run(fn ($h) => $h->delete($this->rest($table) . '?' . http_build_query($filter)));
    }

    // ----------------------------------------------------- akun (Supabase Auth)

    /** Verifikasi email+password. Mengembalikan data user Auth atau null. */
    public function signIn(string $email, string $password): ?array
    {
        $key = config('supabase.anon_key') ?: config('supabase.key');

        try {
            $res = Http::withHeaders(['apikey' => $key])->acceptJson()->timeout(15)
                ->post($this->base() . '/auth/v1/token?grant_type=password', ['email' => $email, 'password' => $password]);
        } catch (Throwable $e) {
            return null;
        }

        return $res->successful() ? ($res->json('user') ?: null) : null;
    }

    /** Buat akun Auth + baris profiles. Jika profil gagal, akun Auth dibatalkan. */
    public function createAccount(string $email, string $name, string $password, string $role): array
    {
        $u = $this->run(fn ($h) => $h->post($this->base() . '/auth/v1/admin/users', [
            'email' => strtolower($email), 'password' => $password,
            'email_confirm' => true, 'user_metadata' => ['full_name' => $name],
        ]));

        $id = is_array($u['data']) ? ($u['data']['id'] ?? null) : null;
        if (! $u['ok'] || ! $id) {
            return ['ok' => false, 'id' => null, 'error' => $u['error'] ?: 'Gagal membuat akun login.'];
        }

        $p = $this->upsert('profiles', [
            'id' => $id, 'full_name' => $name, 'email' => strtolower($email), 'role' => $role, 'is_active' => true,
        ]);
        if (! $p['ok']) {
            $this->deleteAccount($id);

            return ['ok' => false, 'id' => null, 'error' => 'Profil gagal dibuat: ' . $p['error']];
        }

        return ['ok' => true, 'id' => $id, 'error' => ''];
    }

    public function setPassword(string $id, string $password): array
    {
        return $this->run(fn ($h) => $h->put($this->base() . '/auth/v1/admin/users/' . $id, ['password' => $password]));
    }

    /** Hapus akun Auth (baris profiles ikut terhapus lewat on delete cascade). */
    public function deleteAccount(string $id): array
    {
        return $this->run(fn ($h) => $h->delete($this->base() . '/auth/v1/admin/users/' . $id));
    }

    // -------------------------------------------------------------- storage

    public function upload(string $path, string $localFile, string $mime): array
    {
        $url = $this->storage('object/' . config('supabase.bucket') . '/' . $path);

        return $this->run(fn ($h) => $h->withBody(fopen($localFile, 'r'), $mime)->post($url), 300);
    }

    public function removeFile(string $path): void
    {
        $this->run(fn ($h) => $h->delete($this->storage('object/' . config('supabase.bucket') . '/' . $path)));
    }

    public function signedUrl(string $path, int $seconds = 60): ?string
    {
        $r = $this->run(fn ($h) => $h->post(
            $this->storage('object/sign/' . config('supabase.bucket') . '/' . $path),
            ['expiresIn' => $seconds]
        ));
        $signed = is_array($r['data']) ? ($r['data']['signedURL'] ?? null) : null;
        if (! $r['ok'] || ! $signed) {
            return null;
        }

        return str_starts_with($signed, '/storage/v1') ? $this->base() . $signed : $this->storage($signed);
    }

    // ------------------------------------------------------------- lain-lain

    /** Catat aktivitas admin (gagal mencatat tidak boleh menghentikan proses). */
    public function audit(string $action, string $table, ?string $recordId = null, ?array $data = null): void
    {
        try {
            $admin = request()->attributes->get('admin');
            $isUuid = $recordId && preg_match('/^[0-9a-f\-]{36}$/i', $recordId);

            $this->insert('audit_logs', array_filter([
                'user_id'    => $admin['id'] ?? session('admin_id'),
                'action'     => $action,
                'table_name' => $table,
                'record_id'  => $isUuid ? $recordId : null,
                'new_data'   => $data,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 250),
            ], fn ($v) => $v !== null));
        } catch (Throwable $e) {
            // abaikan
        }
    }

    /** Hapus cache data publik supaya perubahan admin langsung tampil di website. */
    public function flushPublic(): void
    {
        Cache::flush();
    }
}
