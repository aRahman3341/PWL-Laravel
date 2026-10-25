<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Foto website disimpan sebagai FILE di folder public/company/ (bukan di database).
 *   public/company/logo.png, hero.jpg, about.jpg, ...        -> foto situs
 *   public/company/projects/{id-proyek}.jpg                  -> foto per proyek
 * Satu "slot" = satu nama file. Mengunggah ulang berarti mengganti file lama.
 */
class SitePhotos
{
        public const SLOTS = [
        'logo' => ['Logo perusahaan', 'Tampil di: navbar, tab browser, dan footer. PNG latar transparan paling bagus, minimal 500 px.', 800, true],
        'hero' => ['Foto utama Beranda', 'Tampil di: latar besar paling atas halaman Beranda. Disarankan lanskap, lebar minimal 1600 px.', 1920, false],
        'about' => ['Foto "Tentang Kami"', 'Tampil di: samping teks Tentang Kami (Beranda). Disarankan lanskap.', 1400, false],
        'docs' => ['Latar banner Dokumen', 'Tampil di: latar banner "Ingin Mengenal Jauh Tentang Kami?" (Beranda, Profil, Pengalaman).', 1600, false],
        'default_layanan_konstruksi' => ['Foto umum Layanan Konstruksi', 'Tampil di: kartu layanan Konstruksi yang belum punya foto sendiri (halaman Layanan) dan kartu Layanan Unggulan di Beranda.', 1200, false],
        'default_layanan_nonkonstruksi' => ['Foto umum Layanan Non Konstruksi', 'Tampil di: kartu layanan Non Konstruksi yang belum punya foto sendiri (halaman Layanan) dan kartu Layanan Unggulan di Beranda.', 1200, false],
        'default_konstruksi' => ['Foto umum Proyek Konstruksi', 'Tampil di: kartu proyek Konstruksi yang belum punya foto sendiri (halaman Pengalaman).', 1200, false],
        'default_nonkonstruksi' => ['Foto umum Proyek Non Konstruksi', 'Tampil di: kartu proyek Non Konstruksi yang belum punya foto sendiri (halaman Pengalaman).', 1200, false],
    ];

    private const EXT = ['jpg', 'jpeg', 'png', 'webp'];

    private static ?array $projectMap = null;

    public static function dir(): string
    {
        return public_path('company');
    }

    private static function find(string $name): ?string
    {
        foreach (self::EXT as $e) {
            $f = self::dir() . '/' . $name . '.' . $e;
            if (is_file($f)) {
                return $f;
            }
        }

        return null;
    }

    private static function urlOf(string $file): string
    {
        $rel = ltrim(str_replace('\\', '/', substr($file, strlen(self::dir()))), '/');

        return asset('company/' . $rel) . '?v=' . @filemtime($file);
    }

    public static function url(string $slot): ?string
    {
        $f = self::find($slot);

        return $f ? self::urlOf($f) : null;
    }

    public static function projectMap(): array
    {
        if (self::$projectMap === null) {
            self::$projectMap = [];
            foreach (glob(self::dir() . '/projects/*.*') ?: [] as $f) {
                self::$projectMap[pathinfo($f, PATHINFO_FILENAME)] = self::urlOf($f);
            }
        }

        return self::$projectMap;
    }

    public static function projectUrl(?string $id, ?string $category): ?string
    {
        $own = $id ? (self::projectMap()[$id] ?? null) : null;
        if ($own) {
            return $own;
        }

        return match ($category) {
            'konstruksi'     => self::url('default_konstruksi'),
            'non-konstruksi' => self::url('default_nonkonstruksi'),
            default          => null,
        };
    }
        private static array $ownMaps = [];

    public static function ownMap(string $dir): array
    {
        if (! isset(self::$ownMaps[$dir])) {
            self::$ownMaps[$dir] = [];
            foreach (glob(self::dir() . '/' . $dir . '/*.*') ?: [] as $f) {
                self::$ownMaps[$dir][pathinfo($f, PATHINFO_FILENAME)] = self::urlOf($f);
            }
        }

        return self::$ownMaps[$dir];
    }

    public static function serviceUrl(?string $id, ?string $category): ?string
    {
        $own = $id ? (self::ownMap('services')[$id] ?? null) : null;
        if ($own) {
            return $own;
        }

        return match (\Illuminate\Support\Str::slug((string) $category)) {
            'konstruksi'     => self::url('default_layanan_konstruksi'),
            'non-konstruksi' => self::url('default_layanan_nonkonstruksi'),
            default          => null,
        };
    }

    public static function remove(string $name): void
    {
        foreach (self::EXT as $e) {
            @unlink(self::dir() . '/' . $name . '.' . $e);
        }
        self::$projectMap = null;
    }

    /** Simpan unggahan ke slot $name (hapus file lama). Foto diperkecil agar halaman tidak berat. */
    public static function save(UploadedFile $file, string $name, int $maxWidth = 1400, bool $png = false): bool
    {
        $dir = dirname(self::dir() . '/' . $name);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return false;
        }

        self::remove($name);
        $base = self::dir() . '/' . $name;

        if (self::encode($file->getRealPath(), $base, $maxWidth, $png) !== null) {
            return true;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::EXT, true)) {
            return false;
        }

        return @copy($file->getRealPath(), $base . '.' . $ext);
    }

    public static function encode(string $src, string $destBase, int $maxWidth, bool $png): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $data = @file_get_contents($src);
        $im = $data ? @imagecreatefromstring($data) : false;
        if (! $im) {
            return null;
        }

        if (! $png && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data($src)['Orientation'] ?? 1);
            $angle = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($angle && ($r = @imagerotate($im, $angle, 0))) {
                $im = $r;
            }
        }

        $w = imagesx($im);
        $h = imagesy($im);
        $nw = min($w, $maxWidth);
        $nh = (int) round($h * $nw / $w);

        $out = imagecreatetruecolor($nw, $nh);
        if ($png) {
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        } else {
            imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        }
        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = $png ? imagepng($out, $destBase . '.png', 6) : imagejpeg($out, $destBase . '.jpg', 84);

        return $ok ? ($png ? 'png' : 'jpg') : null;
    }
}
