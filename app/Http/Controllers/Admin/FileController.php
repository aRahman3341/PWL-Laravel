<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DownloadFiles;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;

/** Dokumen perusahaan yang bisa diunduh pengunjung (lewat persetujuan + kode). */
class FileController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function index()
    {
        return view('admin.files', ['rows' => $this->db->get('confidential_files', [
            'owner_type' => 'eq.company', 'order' => 'created_at.desc',
        ])]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'title' => 'required|max:200',
            'file'  => ['required', 'file', 'max:' . DownloadFiles::MAX_KB, 'mimes:' . DownloadFiles::ALLOWED,
                fn ($attr, $v, $fail) => DownloadFiles::validExt($v) || $fail('Ekstensi file tidak diizinkan.')],
        ], [
            'file.required' => 'Pilih file dulu.',
            'file.max'      => 'Ukuran file maksimal 100 MB.',
            'file.mimes'    => 'Format file tidak didukung. Gunakan: ' . str_replace(',', ', ', DownloadFiles::ALLOWED) . '.',
            'file.uploaded' => 'Upload gagal. Naikkan upload_max_filesize dan post_max_size di php.ini (lihat panduan).',
        ]);

        $saved = DownloadFiles::store($r->file('file'));
        if (! $saved) {
            return back()->withErrors(['db' => 'File gagal disimpan di server. Pastikan folder storage/app bisa ditulisi.']);
        }

        $res = $this->db->insert('confidential_files', [
            'owner_type' => 'company', 'storage' => 'local',
            'title' => $r->title, 'file_name' => $saved['name'], 'storage_path' => $saved['path'],
            'file_size' => $saved['size'], 'created_by' => $r->attributes->get('admin')['id'],
        ]);
        if (! $res['ok']) {
            DownloadFiles::delete(['storage' => 'local', 'storage_path' => $saved['path']]);

            return back()->withErrors(['db' => 'Data file gagal disimpan (sudah menjalankan SQL 06?): ' . $res['error']]);
        }

        $this->db->flushPublic();
        $this->db->audit('upload', 'confidential_files', $res['data'][0]['id'] ?? null, ['title' => $r->title]);

        return back()->with('ok', 'File diunggah.');
    }

    /** Ikon mata: tampil / sembunyikan dari pop-up Download di website. */
    public function toggle(string $id)
    {
        $row = $this->db->first('confidential_files', ['id' => 'eq.' . $id]);
        abort_unless($row, 404);

        $show = ! ($row['is_active'] ?? true);
        $this->db->update('confidential_files', ['id' => 'eq.' . $id], ['is_active' => $show]);
        $this->db->flushPublic();
        $this->db->audit('update', 'confidential_files', $id, ['is_active' => $show]);

        return back()->with('ok', $show ? 'Dokumen sekarang TAMPIL di website.' : 'Dokumen sekarang DISEMBUNYIKAN dari website.');
    }

    public function destroy(string $id)
    {
        $row = $this->db->first('confidential_files', ['id' => 'eq.' . $id]);
        if ($row) {
            DownloadFiles::delete($row);
            $this->db->delete('confidential_files', ['id' => 'eq.' . $id]);
            $this->db->flushPublic();
            $this->db->audit('delete', 'confidential_files', $id, ['title' => $row['title']]);
        }

        return back()->with('ok', 'File dihapus.');
    }
}
