@extends('admin.layout')
@section('title', 'Profil Perusahaan')
@section('subtitle', 'Semua isian di sini tampil di website')
@section('content')
@php
    $groups = [
        ['Identitas', 'building', [
            'name' => ['Nama perusahaan', 'text', 'Nama lengkap, tampil di judul dan footer website.'],
            'short_name' => ['Nama singkat', 'text', 'Boleh dikosongkan.'],
            'founded_year' => ['Tahun berdiri', 'number', 'Dipakai untuk menghitung "tahun pengalaman".'],
        ]],
        ['Tentang perusahaan', 'file', [
            'about' => ['Tentang kami', 'textarea', 'Perkenalan singkat perusahaan.', true],
            'vision' => ['Visi', 'textarea', '', true],
            'mission' => ['Misi', 'textarea', '', true],
        ]],
        ['Kontak', 'phone', [
            'address' => ['Alamat', 'text', '', true],
            'phone' => ['Telepon', 'text'],
            'whatsapp' => ['WhatsApp', 'text'],
            'email' => ['Email', 'email'],
            'website' => ['Website', 'text'],
        ]],
        ['Legalitas', 'shield', [
            'npwp' => ['NPWP', 'text'],
            'nib' => ['NIB', 'text'],
            'perkindo_number' => ['No. Perkindo', 'text'],
        ]],
        ['Pimpinan', 'user', [
            'leader_name' => ['Nama pimpinan', 'text'],
            'operational_manager' => ['Manajer operasional', 'text'],
        ]],
    ];
@endphp
<form method="POST" action="{{ route('admin.company.update') }}" data-dirty class="space-y-5">
    @csrf @method('PUT')

    @foreach ($groups as [$title, $icon, $fields])
        <div class="card p-5">
            <h3 class="font-head font-bold text-slate-800 flex items-center gap-2 mb-4">
                <span class="w-8 h-8 rounded-lg bg-blue-100 text-[#0B3D91] flex items-center justify-center"><x-admin.icon :name="$icon" class="w-4 h-4" /></span>{{ $title }}
            </h3>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($fields as $k => $f)
                    <div class="{{ ! empty($f[3]) || $f[1] === 'textarea' ? 'sm:col-span-2' : '' }}">
                        <label class="lbl" for="f-{{ $k }}">{{ $f[0] }}</label>
                        @if ($f[1] === 'textarea')
                            <textarea id="f-{{ $k }}" name="{{ $k }}" rows="4" class="fld">{{ old($k, $row[$k] ?? '') }}</textarea>
                        @else
                            <input id="f-{{ $k }}" type="{{ $f[1] }}" name="{{ $k }}" value="{{ old($k, $row[$k] ?? '') }}" class="fld">
                        @endif
                        @if (! empty($f[2]))<p class="hint">{{ $f[2] }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div data-dirty-bar class="card sticky bottom-4 z-20 flex items-center justify-between gap-3 px-4 py-3 shadow-lg transition">
        <span data-dirty-label class="text-sm text-slate-500">Semua perubahan sudah tersimpan</span>
        <button class="btn btn-primary"><x-admin.icon name="check" class="w-4 h-4" /> Simpan perubahan</button>
    </div>
</form>
@endsection
