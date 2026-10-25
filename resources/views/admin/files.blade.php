@extends('admin.layout')
@section('title', 'Dokumen Download')
@section('subtitle', 'Dokumen perusahaan yang bisa diunduh pengunjung lewat persetujuan + kode')
@section('content')
<div class="card p-4 text-sm text-slate-600 flex gap-3">
    <x-admin.icon name="alert" class="w-5 h-5 text-[#0B3D91] shrink-0 mt-0.5" />
    <div>Dokumen yang <strong>tampil</strong> (ikon mata hijau) muncul di pop-up <em>Download</em> di website. Pengunjung mengisi nama dan kebutuhan, Anda menyetujuinya di
        <a class="underline text-[#0B3D91]" href="{{ route('admin.downloads') }}">Permintaan Download</a>, lalu kode 6 angka muncul otomatis di layar mereka.
        Untuk file milik <strong>proyek</strong> atau <strong>layanan</strong>, unggah lewat form Ubah di menu Proyek / Layanan.</div>
</div>

<form method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" class="card p-5 space-y-4">
    @csrf
    <h3 class="font-head font-bold text-slate-800 flex items-center gap-2"><x-admin.icon name="lock" class="w-5 h-5 text-[#0B3D91]" /> Unggah dokumen baru</h3>
    <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="lbl">Judul dokumen</label><input name="title" required class="fld" placeholder="Contoh: Company Profile 2026">
            <p class="hint">Judul ini terlihat pengunjung di pop-up Download.</p></div>
        <div><label class="lbl">File</label>
            <x-admin.dropzone name="file" :image="false" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.7z,.jpg,.jpeg,.png,.dwg,.txt,.csv" text="Klik atau seret file ke sini" />
            <p class="hint">Maks. 100 MB. PDF, Word, Excel, PowerPoint, ZIP, gambar, DWG.</p></div>
    </div>
    <button class="btn btn-primary" data-needs-file><x-admin.icon name="upload" class="w-4 h-4" /> Unggah</button>
</form>

<div class="space-y-3 stagger">
@forelse ($rows as $f)
    @php $on = $f['is_active'] ?? true; @endphp
    <div class="card p-4 flex flex-wrap items-center gap-4">
        <span class="w-10 h-10 rounded-xl bg-blue-100 text-[#0B3D91] flex items-center justify-center shrink-0"><x-admin.icon name="file" /></span>
        <div class="min-w-0 flex-1">
            <div class="font-semibold text-slate-800 truncate">{{ $f['title'] }}
                <span class="pill {{ $on ? 'pill-green' : 'pill-gray' }} ml-1">{{ $on ? 'Tampil' : 'Disembunyikan' }}</span></div>
            <div class="text-xs text-slate-500 truncate">{{ $f['file_name'] }}
                @if (! empty($f['file_size']))&middot; {{ number_format($f['file_size'] / 1048576, 1) }} MB @endif
                &middot; diunggah {{ \Illuminate\Support\Str::substr($f['created_at'], 0, 10) }}</div>
        </div>
        <form method="POST" action="{{ route('admin.files.toggle', $f['id']) }}">
            @csrf @method('PUT')
            <button class="btn btn-ghost btn-sm" data-no-loading title="{{ $on ? 'Klik untuk menyembunyikan' : 'Klik untuk menampilkan' }}">
                <x-admin.icon :name="$on ? 'eye' : 'eye-off'" class="w-4 h-4 {{ $on ? 'text-green-600' : 'text-slate-400' }}" />
            </button>
        </form>
        <form method="POST" action="{{ route('admin.files.destroy', $f['id']) }}" data-confirm="File ini akan dihapus permanen.">
            @csrf @method('DELETE')<button class="btn btn-danger btn-sm" data-no-loading><x-admin.icon name="trash" class="w-4 h-4" /> Hapus</button>
        </form>
    </div>
@empty
    <div class="card empty-state"><x-admin.icon name="lock" class="w-10 h-10 mx-auto mb-2" /><p class="text-sm">Belum ada dokumen. Unggah Company Profile dan dokumen lain di atas.</p></div>
@endforelse
</div>
@endsection
