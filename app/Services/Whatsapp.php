<?php

namespace App\Services;

/** Pembantu tautan WhatsApp. Nomor 0896... otomatis menjadi 62896... */
class Whatsapp
{
    public static function number(?string $raw): string
    {
        $d = preg_replace('/\D/', '', (string) $raw);
        if ($d === '') {
            return '';
        }
        if (str_starts_with($d, '0')) {
            return '62' . substr($d, 1);
        }
        if (str_starts_with($d, '8')) {
            return '62' . $d;
        }

        return $d;
    }

    public static function link(?string $raw, string $text = ''): string
    {
        $n = self::number($raw) ?: '6289644113257';

        return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }

    /** Templat pesan untuk meminta kode akses sebuah dokumen. */
    public static function docRequest(string $company, ?string $docTitle = null): string
    {
        $doc = $docTitle ? 'dokumen "' . $docTitle . '"' : 'dokumen perusahaan';

        return "Halo {$company},\n\n"
            . "Saya ingin membuka {$doc}.\n"
            . "Saya membutuhkan kode akses untuk membukanya. Mohon informasikan kode tersebut.\n\n"
            . "Nama:\nInstansi / Perusahaan:\n\nTerima kasih.";
    }
}
