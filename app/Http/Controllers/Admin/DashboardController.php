<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SitePhotos;
use App\Services\SupabaseAdmin;

class DashboardController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function __invoke()
    {
        $c = $this->db->first('company_info') ?? [];
        $n = fn (string $t, array $q = []) => count($this->db->get($t, ['select' => 'id'] + $q));

        $services = $n('services');
        $projects = $n('projects');
        $mgmt     = $n('management');
        $newMsg   = $n('messages', ['status' => 'eq.baru']);

        // Daftar langkah agar website lengkap: [teks, sudah?, tautan]
        $steps = [
            ['Unggah logo perusahaan', (bool) SitePhotos::url('logo'), route('admin.photos')],
            ['Pasang foto utama Beranda', (bool) SitePhotos::url('hero'), route('admin.photos')],
            ['Pasang foto "Tentang Kami"', (bool) SitePhotos::url('about'), route('admin.photos')],
            ['Isi visi dan misi', ! empty($c['vision']) && ! empty($c['mission']), route('admin.company')],
            ['Isi data pengurus', $mgmt > 0, route('admin.content.index', 'management')],
            ['Isi daftar layanan', $services > 0, route('admin.content.index', 'services')],
            ['Isi pengalaman proyek', $projects > 0, route('admin.content.index', 'projects')],
        ];
        $done = count(array_filter($steps, fn ($s) => $s[1]));

        $hour = (int) now('Asia/Jakarta')->format('G');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

        return view('admin.dashboard', [
            'greeting' => $greeting,
            'stats' => [
                ['Layanan', $services, 'layers', route('admin.content.index', 'services')],
                ['Proyek', $projects, 'briefcase', route('admin.content.index', 'projects')],
                ['Pengurus', $mgmt, 'users', route('admin.content.index', 'management')],
                ['Pesan baru', $newMsg, 'mail', route('admin.messages')],
            ],
            'steps'  => $steps,
            'done'   => $done,
            'recent' => $this->db->get('messages', ['order' => 'created_at.desc', 'limit' => 3]),
        ]);
    }

    /** Riwayat aktivitas (khusus management). */
    public function audit()
    {
        return view('admin.audit', [
            'rows'  => $this->db->get('audit_logs', ['order' => 'created_at.desc', 'limit' => 200]),
            'names' => collect($this->db->get('profiles', ['select' => 'id,full_name']))->pluck('full_name', 'id'),
        ]);
    }
}
