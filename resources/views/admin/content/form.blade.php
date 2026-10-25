@extends('admin.layout')
@section('title', ($row ? 'Ubah ' : 'Tambah ') . $def['label'])
@section('subtitle', 'Isi data lalu klik Simpan')
@section('content')
<form method="POST" enctype="multipart/form-data" data-dirty
      action="{{ $row ? route('admin.content.update', [$type, $row['id']]) : route('admin.content.store', $type) }}" class="space-y-5">
    @csrf @if ($row) @method('PUT') @endif

    <div class="card p-5">
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($def['fields'] as $k => $f)
                @php
                    $val = old($k, $row[$k] ?? ($f[1] === 'checkbox' ? ! $row : ''));
                    $wide = in_array($f[1], ['textarea', 'checkbox'], true) || $k === 'name';
                @endphp
                <div class="{{ $wide ? 'sm:col-span-2' : '' }}">
                    @if ($f[1] === 'checkbox')
                        <label class="switch">
                            <input type="checkbox" name="{{ $k }}" value="1" @checked($val)>
                            <span class="track"></span><span>{{ $f[0] }}</span>
                        </label>
                    @else
                        <label class="lbl" for="f-{{ $k }}">{{ $f[0] }}</label>
                        @if ($f[1] === 'textarea')
                            <textarea id="f-{{ $k }}" name="{{ $k }}" rows="4" class="fld">{{ $val }}</textarea>
                        @elseif ($f[1] === 'select')
                            <select id="f-{{ $k }}" name="{{ $k }}" class="fld">
                                @foreach ($f[3] as $v => $l)<option value="{{ $v }}" @selected((string) $val === (string) $v)>{{ $l }}</option>@endforeach
                            </select>
                        @else
                            <input id="f-{{ $k }}" type="{{ in_array($f[1], ['number', 'decimal']) ? 'number' : $f[1] }}" @if ($f[1] === 'decimal') step="any" @endif name="{{ $k }}" value="{{ $val }}" class="fld">
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if (! empty($def['photo']))
        @php $cur = $row ? (\App\Services\SitePhotos::ownMap($def['photo'] === true ? 'projects' : $def['photo'])[$row['id']] ?? null) : null; @endphp
        <div class="card p-5">
            <h3 class="font-head font-bold text-slate-800 mb-1">Foto {{ strtolower($def['label']) }} <span class="text-xs font-normal text-slate-400">(opsional)</span></h3>
            <p class="hint mb-3">Foto ini tampil di kartu {{ strtolower($def['label']) }} di website. Tanpa foto, kartu memakai foto umum kategorinya (menu Foto Website). JPG, PNG, atau WebP, maks. 8 MB.</p>
            <x-admin.dropzone name="photo" :current="$cur" text="Klik atau seret foto ke sini" />
            @if ($cur)
                <label class="switch mt-3"><input type="checkbox" name="remove_photo" value="1"><span class="track"></span><span>Hapus foto yang ada</span></label>
            @endif
        </div>
    @endif

    @if (! empty($def['attach']))
        <div class="card p-5">
            <h3 class="font-head font-bold text-slate-800 flex items-center gap-2 mb-1"><x-admin.icon name="paperclip" class="w-5 h-5 text-[#0B3D91]" /> Lampiran file <span class="text-xs font-normal text-slate-400">(opsional)</span></h3>
            <p class="hint mb-3">Ini dokumen untuk diunduh (<strong>bukan foto</strong> yang tampil di kartu). Pengunjung mengunduhnya lewat tombol"Download dokumen" di kartu {{ strtolower($def['label']) }}, dengan persetujuan tim dan kode 6 angka. Maks. 100 MB (PDF, Word, Excel, PowerPoint, ZIP, gambar, DWG).</p>
            @if ($attach)
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-200 p-3 mb-3 text-sm">
                    <x-admin.icon name="file" class="w-6 h-6 text-[#0B3D91] shrink-0" />
                    <div class="min-w-0 flex-1"><div class="font-semibold truncate">{{ $attach['file_name'] }}</div>
                        <div class="text-xs text-slate-500">{{ ! empty($attach['file_size']) ? number_format($attach['file_size'] / 1048576, 1) . ' MB' : '' }}</div></div>
                </div>
            @endif
            <x-admin.dropzone name="attachment" :image="false" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.7z,.jpg,.jpeg,.png,.dwg,.txt,.csv" :text="$attach ? 'Klik atau seret file baru untuk mengganti' : 'Klik atau seret file ke sini'" />
            <div class="mt-4 flex flex-wrap items-center gap-6">
                <label class="switch"><input type="checkbox" name="attachment_active" value="1" @checked(old('attachment_active', $attach ? ($attach['is_active'] ?? true) : true))><span class="track"></span><span>Tampilkan tombol download di website</span></label>
                @if ($attach)
                    <label class="switch"><input type="checkbox" name="remove_attachment" value="1"><span class="track"></span><span>Hapus lampiran ini</span></label>
                @endif
            </div>
        </div>
    @endif

    <div data-dirty-bar class="card sticky bottom-4 z-20 flex items-center justify-between gap-3 px-4 py-3 shadow-lg transition">
        <a href="{{ route('admin.content.index', $type) }}" class="btn btn-ghost">Batal</a>
        <div class="flex items-center gap-3">
            <span data-dirty-label class="text-sm text-slate-500 hidden sm:inline"></span>
            <button class="btn btn-primary"><x-admin.icon name="check" class="w-4 h-4" /> Simpan</button>
        </div>
    </div>
</form>
@endsection
