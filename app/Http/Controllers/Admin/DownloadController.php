<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DownloadFlow;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/** Daftar permintaan download dan tombol Setujui / Tolak di panel admin. */
class DownloadController extends Controller
{
    public function __construct(private SupabaseAdmin $db, private DownloadFlow $flow) {}

    public function index()
    {
        $rows = array_map(function ($r) {
            // Tampilkan yang sudah lewat waktu sebagai "expired" tanpa menulis ke database
            if ($r['status'] === 'pending' && Carbon::parse($r['created_at'])->addHours(DownloadFlow::PENDING_HOURS)->isPast()) {
                $r['status'] = 'expired';
            }
            if ($r['status'] === 'approved' && ! empty($r['code_expires_at']) && Carbon::parse($r['code_expires_at'])->isPast()) {
                $r['status'] = 'expired';
            }

            return $r;
        }, $this->db->get('download_requests', ['order' => 'created_at.desc', 'limit' => 200]));

        return view('admin.downloads', [
            'rows'  => $rows,
            'files' => collect($this->db->get('confidential_files', ['select' => 'id,title,owner_type']))->keyBy('id'),
        ]);
    }

    public function decide(Request $r, string $id, string $act)
    {
        abort_unless(in_array($act, ['setujui', 'tolak'], true), 404);
        $by = $r->attributes->get('admin')['id'];

        $res = $act === 'setujui' ? $this->flow->approve($id, $by) : $this->flow->reject($id, $by);
        Cache::forget('admin_pending_downloads');

        if (! $res['ok']) {
            return back()->withErrors(['db' => $res['error'] ?? 'Gagal memproses permintaan.']);
        }

        $this->db->audit($act === 'setujui' ? 'approve' : 'reject', 'download_requests', $id);

        return back()->with('ok', $act === 'setujui'
            ? 'Disetujui. Kode ' . $res['code'] . ' otomatis muncul di layar pemohon (berlaku 1 jam).'
            : 'Permintaan ditolak.');
    }
}
