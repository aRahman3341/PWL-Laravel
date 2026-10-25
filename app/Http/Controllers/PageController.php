<?php

namespace App\Http\Controllers;

use App\Services\Supabase;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(private Supabase $sb) {}

    public function beranda()
    {
        $c = $this->sb->company();

        return view('pages.beranda', [
            'c'      => $c,
            'svc'    => $this->sb->servicesByCategory(),
            'years'  => (int) date('Y') - (int) $c['founded_year'],
            'recent' => array_slice($this->sb->projects('semua'), 0, 4),
        ]);
    }

    public function profil()
    {
        $c = $this->sb->company();

        // Legalitas: dari tabel legal_documents; jika kosong, pakai kolom di company_info.
        $legal = array_values(array_filter(array_map(
            fn ($l) => ['label' => $l['name'] ?? '', 'value' => $l['document_number'] ?? ''],
            $this->sb->legalDocuments()
        ), fn ($i) => $i['label'] !== '' && $i['value'] !== ''));

        if (! $legal) {
            $legal = array_values(array_filter([
                ['label' => 'NPWP',         'value' => $c['npwp'] ?? null],
                ['label' => 'NIB',          'value' => $c['nib'] ?? null],
                ['label' => 'No. Perkindo', 'value' => $c['perkindo_number'] ?? null],
            ], fn ($i) => ! empty($i['value'])));
        }

        // Pengurus: dari tabel management; jika kosong, pakai kolom di company_info.
        $mgmt = array_values(array_filter(array_map(
            fn ($m) => ['role' => $m['position'] ?? '', 'name' => $m['name'] ?? ''],
            $this->sb->management()
        ), fn ($i) => $i['name'] !== ''));

        if (! $mgmt) {
            $mgmt = array_values(array_filter([
                ['role' => 'Direktur Utama',      'name' => $c['leader_name'] ?? null],
                ['role' => 'Manajer Operasional', 'name' => $c['operational_manager'] ?? null],
            ], fn ($i) => ! empty($i['name'])));
        }

        return view('pages.profil', [
            'c' => $c,
            // Angka ringkas (hitung naik di halaman)
            'stats' => [
                ['value' => max(0, (int) date('Y') - (int) $c['founded_year']), 'suffix' => '+', 'label' => 'Tahun Berpengalaman'],
                ['value' => count($this->sb->projects('semua')), 'suffix' => '', 'label' => 'Proyek Terdokumentasi'],
                ['value' => array_sum(array_map('count', $this->sb->servicesByCategory())), 'suffix' => '', 'label' => 'Bidang Layanan'],
                ['value' => (int) (config('site.stats.0.value') ?? 0), 'suffix' => '', 'label' => 'Tenaga Ahli & Pendukung'],
            ],
            // Perjalanan perusahaan (ubah teks di sini bila perlu)
            'timeline' => [
                ['2007', 'Didirikan di Bandung', 'Berdiri sebagai CV konsultan melalui Akta Pendirian No. 01 tanggal 1 Maret 2007.'],
                ['2017', 'Menjadi Perseroan Terbatas', 'Berubah bentuk menjadi PT melalui Akta No. 14 tanggal 14 Februari 2017, dan memperoleh SIUP.'],
                ['2022', 'Pembaruan Akta & Pengesahan', 'Perubahan akta terakhir No. 09 tanggal 24 Maret 2022, disahkan Kemenkumham RI.'],
                ['Kini', 'Anggota PERKINDO', 'Melayani konsultansi konstruksi dan non konstruksi dengan tenaga ahli bersertifikat.'],
            ],
            'values' => [
                ['Integritas', 'Menjunjung kejujuran dalam setiap pekerjaan.', 'shield'],
                ['Profesional', 'Bekerja sesuai standar dan keahlian.', 'award'],
                ['Kolaboratif', 'Membangun kerja sama yang saling menguatkan.', 'users'],
                ['Inovatif', 'Terus mencari solusi yang lebih baik.', 'zap'],
            ],
            'mgmt' => $mgmt,
            'legal' => $legal,
        ]);
    }

    public function layanan(Request $request)
    {
        $k = $request->query('k');
        if (! in_array($k, ['konstruksi', 'non-konstruksi', 'semua'], true)) {
            $k = null; // tampilan ringkas: 4 layanan per kategori
        }

        $svc = $this->sb->servicesByCategory();
        if ($k === 'konstruksi' || $k === 'non-konstruksi') {
            $name = $k === 'konstruksi' ? 'Konstruksi' : 'Non Konstruksi';
            $svc = isset($svc[$name]) ? [$name => $svc[$name]] : [];
        }

        return view('pages.layanan', ['svc' => $svc, 'k' => $k, 'files' => $this->sb->attachments()]);
    }

    public function pengalaman(Request $request)
    {
        $f = $request->query('k', 'semua');
        if (! in_array($f, ['semua', 'konstruksi', 'non-konstruksi'], true)) {
            $f = 'semua';
        }

        $all   = $request->boolean('semua');
        $list  = $this->sb->projects($f);
        $total = count($list);

        return view('pages.pengalaman', [
            'c'        => $this->sb->company(),
            'files'    => $this->sb->attachments(),
            'f'        => $f,
            'all'      => $all,
            'total'    => $total,
            // Ringkas: 4 proyek pertama. "Lihat semua" menampilkan seluruhnya.
            'projects' => $all ? $list : array_slice($list, 0, 6),
            'counts'   => [
                'semua'          => count($this->sb->projects('semua')),
                'konstruksi'     => count($this->sb->projects('konstruksi')),
                'non-konstruksi' => count($this->sb->projects('non-konstruksi')),
            ],
            'tabs'     => ['semua' => 'Semua', 'konstruksi' => 'Konstruksi', 'non-konstruksi' => 'Non Konstruksi'],
        ]);
    }

    public function kontak()
    {
        return view('pages.kontak', ['c' => $this->sb->company()]);
    }
}
