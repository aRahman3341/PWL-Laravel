@extends('admin.layout')
@section('title', 'Foto Website')
@section('subtitle', 'Klik kotak foto atau seret gambar ke dalamnya, lalu tekan Unggah')
@section('content')
<div class="card p-4 text-sm text-slate-600 flex gap-3">
    <x-admin.icon name="alert" class="w-5 h-5 text-[#0B3D91] shrink-0 mt-0.5" />
    <div>Foto disimpan sebagai file di folder <span class="font-mono">public/company</span> dan otomatis diperkecil.
        Foto tiap proyek / tiap layanan diunggah lewat menu <a class="underline text-[#0B3D91]" href="{{ route('admin.content.index', 'projects') }}">Proyek</a> atau <a class="underline text-[#0B3D91]" href="{{ route('admin.content.index', 'services') }}">Layanan</a> (tombol Ubah).</div>
</div>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5 stagger">
@foreach ($slots as $key => $s)
    <div class="card card-hover p-4 flex flex-col gap-3">
        <div>
            <div class="font-semibold text-slate-800 flex items-center gap-2">{{ $s[0] }}
                <span class="pill {{ $urls[$key] ? 'pill-green' : 'pill-gray' }}">{{ $urls[$key] ? 'Terpasang' : 'Kosong' }}</span></div>
            <div class="text-xs text-slate-500 mt-1">{{ $s[1] }}</div>
        </div>
        <form method="POST" action="{{ route('admin.photos.store', $key) }}" enctype="multipart/form-data" class="space-y-3 mt-auto">
            @csrf
            <x-admin.dropzone name="photo" :current="$urls[$key]" text="Klik atau seret foto ke sini" />
            <button class="btn btn-primary w-full" data-needs-file><x-admin.icon name="upload" class="w-4 h-4" /> {{ $urls[$key] ? 'Ganti foto' : 'Unggah foto' }}</button>
        </form>
        @if ($urls[$key])
            <form method="POST" action="{{ route('admin.photos.destroy', $key) }}" data-confirm="Foto ini akan dihapus dari website.">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm" data-no-loading><x-admin.icon name="trash" class="w-4 h-4" /> Hapus foto</button>
            </form>
        @endif
    </div>
@endforeach
</div>
@endsection
