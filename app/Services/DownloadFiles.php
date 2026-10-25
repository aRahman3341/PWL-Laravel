<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan file yang bisa diunduh (dokumen perusahaan + lampiran proyek/layanan).
 * File BARU disimpan di server proyek (storage/app/downloads), TIDAK di folder public,
 * sehingga hanya bisa diambil lewat alur kode. Batas 100 MB (tidak terikat batas Supabase).
 * File lama yang sudah ada di Supabase Storage tetap bisa diunduh.
 */
class DownloadFiles
{
    public const ALLOWED = 'pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,jpg,jpeg,png,dwg,txt,csv';
    public const MAX_KB  = 102400; // 100 MB

    /** Ekstensi nama file harus termasuk daftar aman (mencegah file berisi PDF tetapi bernama .exe / .php). */
    public static function validExt(?UploadedFile $f): bool
    {
        return $f === null || in_array(strtolower($f->getClientOriginalExtension()), explode(',', self::ALLOWED), true);
    }

    /** @return array{path:string,name:string,size:int}|null */
    public static function store(UploadedFile $f): ?array
    {
        if (! self::validExt($f)) {
            return null;
        }
        $ext = strtolower($f->getClientOriginalExtension());
        $path = Storage::disk('local')->putFileAs('downloads', $f, Str::uuid() . ($ext ? '.' . $ext : ''));

        return $path ? ['path' => $path, 'name' => $f->getClientOriginalName(), 'size' => (int) $f->getSize()] : null;
    }

    public static function delete(array $row): void
    {
        if (($row['storage'] ?? 'supabase') === 'local') {
            Storage::disk('local')->delete($row['storage_path']);
        } else {
            app(SupabaseAdmin::class)->removeFile($row['storage_path']);
        }
    }

    /** Respons unduhan: stream dari server, atau tautan bertanda tangan untuk file lama di Supabase. */
    public static function response(array $row)
    {
        if (($row['storage'] ?? 'supabase') === 'local') {
            abort_unless(Storage::disk('local')->exists($row['storage_path']), 404, 'File tidak ditemukan di server.');

            return Storage::disk('local')->download($row['storage_path'], $row['file_name']);
        }

        $url = app(SupabaseAdmin::class)->signedUrl($row['storage_path'], 60);
        abort_unless($url, 500, 'Gagal membuat link unduhan.');

        return redirect()->away($url . '&download=' . urlencode($row['file_name']));
    }
}
