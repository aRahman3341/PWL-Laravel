<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DownloadFiles;
use App\Services\SitePhotos;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Satu controller untuk Layanan, Proyek, Pengurus, dan Legalitas.
 * Field: [label, tipe, aturan validasi, (opsi untuk select)]
 * Tipe: text, textarea, number, decimal, date, select, checkbox
 */
class ContentController extends Controller
{
    private const TYPES = [
        'services' => [
            'label' => 'Layanan', 'table' => 'services', 'photo' => 'services', 'attach' => 'service','order' => 'category.asc,sort_order.asc',
            'list'  => ['name' => 'Nama', 'code' => 'Kode', 'category' => 'Kategori', 'is_active' => 'Tampil'],
            'fields' => [
                'category'      => ['Kategori', 'select', 'required', ['Konstruksi' => 'Konstruksi', 'Non Konstruksi' => 'Non Konstruksi']],
                'name'          => ['Nama layanan', 'text', 'required|max:255'],
                'code'          => ['Kode (mis. AR 001)', 'text', 'nullable|max:100'],
                'description'   => ['Deskripsi', 'textarea', 'nullable'],
                'qualification' => ['Kualifikasi', 'select', 'nullable', ['' => '-', 'Kecil' => 'Kecil', 'Menengah' => 'Menengah', 'Besar' => 'Besar']],
                'icon'          => ['Ikon (opsional)', 'text', 'nullable|max:100'],
                'sort_order'    => ['Urutan', 'number', 'nullable|integer'],
                'is_active'     => ['Tampilkan di website', 'checkbox'],
            ],
        ],
        'projects' => [
            'label' => 'Proyek', 'table' => 'projects', 'photo' => true, 'attach' => 'project', 'order' => 'year.desc.nullslast,sort_order.asc',
            'list'  => ['name' => 'Nama', 'client_name' => 'Pemberi kerja', 'year' => 'Tahun', 'category' => 'Kategori', 'is_active' => 'Tampil'],
            'fields' => [
                'category'            => ['Kategori', 'select', 'required', ['konstruksi' => 'Konstruksi', 'non-konstruksi' => 'Non Konstruksi']],
                'name'                => ['Nama pekerjaan', 'text', 'required|max:500'],
                'project_code'        => ['Kode proyek', 'text', 'nullable|max:100'],
                'classification_code' => ['Bidang / kode klasifikasi', 'text', 'nullable|max:100'],
                'client_name'         => ['Pemberi tugas', 'text', 'nullable|max:255'],
                'contract_number'     => ['Nomor / tanggal kontrak', 'text', 'nullable|max:150'],
                'contract_date'       => ['Tanggal kontrak', 'date', 'nullable|date'],
                'contract_value'      => ['Nilai kontrak (Rp)', 'decimal', 'nullable|numeric|min:0'],
                'location'            => ['Lokasi', 'text', 'nullable|max:255'],
                'description'         => ['Deskripsi', 'textarea', 'nullable'],
                'year'                => ['Tahun', 'number', 'nullable|integer|min:1990|max:2100'],
                'project_status'      => ['Status', 'text', 'nullable|max:50'],
                'is_featured'         => ['Unggulan', 'checkbox'],
                'sort_order'          => ['Urutan', 'number', 'nullable|integer'],
                'is_active'           => ['Tampilkan di website', 'checkbox'],
            ],
        ],
        'management' => [
            'label' => 'Pengurus', 'table' => 'management', 'order' => 'sort_order.asc',
            'list'  => ['name' => 'Nama', 'position' => 'Jabatan', 'sort_order' => 'Urutan', 'is_active' => 'Tampil'],
            'fields' => [
                'name'       => ['Nama lengkap', 'text', 'required|max:255'],
                'position'   => ['Jabatan', 'text', 'required|max:255'],
                'division'   => ['Divisi', 'text', 'nullable|max:255'],
                'education'  => ['Pendidikan', 'text', 'nullable|max:255'],
                'sort_order' => ['Urutan tampil', 'number', 'nullable|integer'],
                'is_active'  => ['Tampilkan di website', 'checkbox'],
            ],
        ],
        'legal' => [
            'label' => 'Legalitas', 'table' => 'legal_documents', 'order' => 'sort_order.asc',
            'list'  => ['name' => 'Dokumen', 'document_number' => 'Nomor', 'sort_order' => 'Urutan', 'is_active' => 'Tampil'],
            'fields' => [
                'name'            => ['Nama dokumen', 'text', 'required|max:255'],
                'document_type'   => ['Jenis', 'text', 'nullable|max:100'],
                'document_number' => ['Nomor dokumen', 'text', 'required|max:255'],
                'issuer'          => ['Penerbit', 'text', 'nullable|max:255'],
                'issued_date'     => ['Tanggal terbit', 'date', 'nullable|date'],
                'expiry_date'     => ['Tanggal kedaluwarsa', 'date', 'nullable|date'],
                'description'     => ['Keterangan', 'textarea', 'nullable'],
                'sort_order'      => ['Urutan tampil', 'number', 'nullable|integer'],
                'is_active'       => ['Tampilkan di website', 'checkbox'],
            ],
        ],
    ];

    public function __construct(private SupabaseAdmin $db) {}

    public function index(string $type)
    {
        $d = self::TYPES[$type];

        $attach = [];
        if (! empty($d['attach'])) {
            foreach ($this->db->get('confidential_files', ['owner_type' => 'eq.' . $d['attach'], 'select' => 'owner_id,is_active']) as $a) {
                $attach[$a['owner_id']] = (bool) $a['is_active'];
            }
        }

        return view('admin.content.index', [
            'type' => $type, 'def' => $d, 'attach' => $attach,
            'rows' => $this->db->get($d['table'], ['order' => $d['order']]),
        ]);
    }

    public function create(string $type)
    {
        return view('admin.content.form', ['type' => $type, 'def' => self::TYPES[$type], 'row' => null, 'attach' => null]);
    }

    public function store(Request $r, string $type)
    {
        $d = self::TYPES[$type];
        $data = $this->data($r, $d);
        $res = $this->db->insert($d['table'], $data);

        if ($res['ok']) {
            $newId = is_array($res['data']) ? ($res['data'][0]['id'] ?? null) : null;
            $this->photo($r, $d, $newId);
            $this->attachment($r, $d, $newId, (string) ($data['name'] ?? ''));
        }

        return $this->done($res, $type, 'create', $d['table'], null, $data);
    }

    public function edit(string $type, string $id)
    {
        $d = self::TYPES[$type];
        $row = $this->db->first($d['table'], ['id' => 'eq.' . $id]);
        abort_unless($row, 404);

        $attach = ! empty($d['attach'])
            ? $this->db->first('confidential_files', ['owner_type' => 'eq.' . $d['attach'], 'owner_id' => 'eq.' . $id])
            : null;

        return view('admin.content.form', ['type' => $type, 'def' => $d, 'row' => $row, 'attach' => $attach]);
    }

    public function update(Request $r, string $type, string $id)
    {
        $d = self::TYPES[$type];
        $data = $this->data($r, $d);
        $res = $this->db->update($d['table'], ['id' => 'eq.' . $id], $data);

        if ($res['ok']) {
            $this->photo($r, $d, $id);
            $this->attachment($r, $d, $id, (string) ($data['name'] ?? ''));
        }

        return $this->done($res, $type, 'update', $d['table'], $id, $data);
    }

    /** Ikon mata: tampilkan / sembunyikan sebuah data dari website tanpa menghapusnya. */
    public function toggle(string $type, string $id)
    {
        $d = self::TYPES[$type];
        $row = $this->db->first($d['table'], ['id' => 'eq.' . $id]);
        abort_unless($row, 404);

        $show = ! ($row['is_active'] ?? true);
        $res = $this->db->update($d['table'], ['id' => 'eq.' . $id], ['is_active' => $show]);

        if (! $res['ok']) {
            return back()->withErrors(['db' => 'Gagal mengubah status: ' . $res['error']]);
        }

        $this->db->flushPublic();
        $this->db->audit('update', $d['table'], $id, ['is_active' => $show]);

        return back()->with('ok', $show ? 'Sekarang TAMPIL di website.' : 'Sekarang DISEMBUNYIKAN dari website.');
    }

    public function destroy(string $type, string $id)
    {
        $d = self::TYPES[$type];
        $res = $this->db->delete($d['table'], ['id' => 'eq.' . $id]);

        if ($res['ok'] && ! empty($d['photo']) && preg_match('/^[0-9a-f\-]{36}$/i', $id)) {
            SitePhotos::remove(($d['photo'] === true ? 'projects' : $d['photo']) . '/' . $id);
        }
        if ($res['ok'] && ! empty($d['attach'])) {
            $cur = $this->db->first('confidential_files', ['owner_type' => 'eq.' . $d['attach'], 'owner_id' => 'eq.' . $id]);
            if ($cur) {
                DownloadFiles::delete($cur);
                $this->db->delete('confidential_files', ['id' => 'eq.' . $cur['id']]);
            }
        }

        return $this->done($res, $type, 'delete', $d['table'], $id, null);
    }

    private function data(Request $r, array $d): array
    {
        $rules = [];
        foreach ($d['fields'] as $k => $f) {
            if ($f[1] === 'checkbox') {
                continue;
            }
            $rules[$k] = $f[1] === 'select'
                ? [$f[2], Rule::in(array_keys($f[3]))]
                : $f[2];
        }
        if (! empty($d['photo'])) {
            $rules['photo'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192';
        }
        if (! empty($d['attach'])) {
            $rules['attachment'] = ['nullable', 'file', 'max:' . DownloadFiles::MAX_KB, 'mimes:' . DownloadFiles::ALLOWED,
                fn ($attr, $v, $fail) => DownloadFiles::validExt($v) || $fail('Ekstensi file tidak diizinkan.')];
        }
        $r->validate($rules, [
            'attachment.max'      => 'Ukuran lampiran maksimal 100 MB.',
            'attachment.mimes'    => 'Format lampiran tidak didukung. Gunakan: ' . str_replace(',', ', ', DownloadFiles::ALLOWED) . '.',
            'attachment.uploaded' => 'Upload gagal. Naikkan upload_max_filesize dan post_max_size di php.ini (lihat panduan).',
        ]);

        $out = [];
        foreach ($d['fields'] as $k => $f) {
            $out[$k] = match ($f[1]) {
                'checkbox' => $r->boolean($k),
                'number'   => $r->filled($k) ? (int) $r->input($k) : null,
                'decimal'  => $r->filled($k) ? (float) str_replace(',', '.', $r->input($k)) : null,
                default    => $r->filled($k) ? trim((string) $r->input($k)) : null,
            };
        }

        // sort_order di database tidak boleh null
        if (array_key_exists('sort_order', $out) && $out['sort_order'] === null) {
            $out['sort_order'] = 0;
        }

        return $out;
    }

    /** Lampiran file (opsional) untuk proyek/layanan: disimpan di server, diunduh lewat persetujuan + kode. */
    private function attachment(Request $r, array $d, ?string $id, string $title): void
    {
        if (empty($d['attach']) || ! $id || ! preg_match('/^[0-9a-f\-]{36}$/i', $id)) {
            return;
        }

        $q = ['owner_type' => 'eq.' . $d['attach'], 'owner_id' => 'eq.' . $id];
        $cur = $this->db->first('confidential_files', $q);

        if ($r->boolean('remove_attachment') && $cur) {
            DownloadFiles::delete($cur);
            $this->db->delete('confidential_files', ['id' => 'eq.' . $cur['id']]);
            $cur = null;
        }

        if ($r->hasFile('attachment')) {
            $f = DownloadFiles::store($r->file('attachment'));
            if (! $f) {
                return;
            }
            if ($cur) {
                DownloadFiles::delete($cur);
            }
            $row = [
                'owner_type' => $d['attach'], 'owner_id' => $id, 'storage' => 'local',
                'title' => $title, 'file_name' => $f['name'], 'storage_path' => $f['path'], 'file_size' => $f['size'],
                'is_active' => $r->boolean('attachment_active'), 'created_by' => $r->attributes->get('admin')['id'],
            ];
            $cur
                ? $this->db->update('confidential_files', ['id' => 'eq.' . $cur['id']], $row)
                : $this->db->insert('confidential_files', $row);
        } elseif ($cur) {
            $this->db->update('confidential_files', ['id' => 'eq.' . $cur['id']], [
                'title' => $title, 'is_active' => $r->boolean('attachment_active'),
            ]);
        }
    }

    /** Simpan / hapus foto proyek (file di public/company/projects). */
        /** Simpan / hapus foto proyek / layanan (file di public/company/projects atau /services). */
    private function photo(Request $r, array $d, ?string $id): void
    {
        if (empty($d['photo']) || ! $id || ! preg_match('/^[0-9a-f\-]{36}$/i', $id)) {
            return;
        }

        $dir = $d['photo'] === true ? 'projects' : $d['photo'];

        if ($r->boolean('remove_photo')) {
            SitePhotos::remove($dir . '/' . $id);
        }
        if ($r->hasFile('photo')) {
            SitePhotos::save($r->file('photo'), $dir . '/' . $id, 1200);
        }
    }

    private function done(array $res, string $type, string $action, string $table, ?string $id, ?array $data)
    {
        if (! $res['ok']) {
            return back()->withInput()->withErrors(['db' => 'Gagal menyimpan: ' . $res['error']]);
        }

        $this->db->flushPublic();
        $rowId = $id ?? (is_array($res['data']) ? ($res['data'][0]['id'] ?? null) : null);
        $this->db->audit($action, $table, $rowId, $data);

        return redirect()->route('admin.content.index', $type)->with('ok', 'Perubahan tersimpan.');
    }
}
