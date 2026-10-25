<?php

namespace App\Http\Controllers;

use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Dokumen perusahaan yang dilindungi kode akses.
 * Kode bisa berlaku untuk SEMUA dokumen atau SATU dokumen saja (diatur di admin).
 * Akses disimpan di sesi sebagai daftar izin: ['*' => waktu_habis] atau [id_dokumen => waktu_habis].
 */
class ConfidentialController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    /** Izin yang masih berlaku. */
    private function grants(): array
    {
        return array_filter((array) session('conf_grants', []), fn ($t) => (int) $t > time());
    }

    private function canOpen(string $fileId): bool
    {
        $g = $this->grants();

        return isset($g['*']) || isset($g[$fileId]);
    }

    public function index()
    {
        $g = $this->grants();
        $files = [];

        if ($g) {
            $q = ['is_active' => 'eq.true', 'order' => 'created_at.desc', 'select' => 'id,title,file_name,file_size', 'owner_type' => 'eq.company'];
            if (! isset($g['*'])) {
                $q['id'] = 'in.(' . implode(',', array_keys($g)) . ')';
            }
            $files = $this->db->get('confidential_files', $q);
        }

        return view('confidential', ['granted' => (bool) $g, 'files' => $files]);
    }

    public function verify(Request $r)
    {
        $r->validate(['code' => 'required|string|max:50']);

        $row = $this->db->first('access_codes', [
            'code_hash' => 'eq.' . hash('sha256', strtoupper(trim($r->code))),
            'is_active' => 'eq.true',
        ]);

        $valid = $row
            && (! $row['expires_at'] || Carbon::parse($row['expires_at'])->isFuture())
            && (! $row['max_uses'] || $row['used_count'] < $row['max_uses']);

        if (! $valid) {
            return back()->withErrors(['code' => 'Kode tidak valid, kedaluwarsa, atau sudah habis dipakai.']);
        }

        $this->db->update('access_codes', ['id' => 'eq.' . $row['id']], ['used_count' => $row['used_count'] + 1]);

        $g = $this->grants();
        $g[$row['file_id'] ?? '*'] = now()->addMinutes(30)->timestamp;

        $r->session()->regenerate();
        session(['conf_grants' => $g]);

        return redirect()->route('rahasia');
    }

    public function download(string $id)
    {
        abort_unless($this->canOpen($id), 403);

        $f = $this->db->first('confidential_files', ['id' => 'eq.' . $id, 'is_active' => 'eq.true']);
        abort_unless($f, 404);

        return \App\Services\DownloadFiles::response($f);
    }

    public function leave()
    {
        session()->forget('conf_grants');

        return redirect()->route('rahasia');
    }
}
