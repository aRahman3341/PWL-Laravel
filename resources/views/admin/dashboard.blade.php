@extends('admin.layout')
@section('title', 'Beranda Admin')
@section('subtitle', 'Ringkasan website dan hal yang perlu dilengkapi')
@section('content')
@php
    $total = count($steps);
    $pct = $total ? (int) round($done / $total * 100) : 0;
    $tone = ['layers' => 'bg-blue-100 text-blue-700', 'briefcase' => 'bg-indigo-100 text-indigo-700', 'users' => 'bg-emerald-100 text-emerald-700', 'mail' => 'bg-rose-100 text-rose-700'];
@endphp

<div class="rounded-2xl p-6 text-white bg-gradient-to-r from-[#0B3D91] to-[#2563c9] relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/10"></div>
    <div class="relative">
        <p class="text-blue-200 text-sm">{{ $greeting }},</p>
        <h2 class="font-head text-2xl font-extrabold">{{ $admin['full_name'] }}</h2>
        <p class="text-sm text-blue-100 mt-1">Perubahan yang Anda simpan langsung tampil di website.</p>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger">
    @foreach ($stats as [$label, $n, $icon, $url])
        <a href="{{ $url }}" class="card card-hover p-4 block">
            <div class="flex items-center justify-between">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tone[$icon] }}"><x-admin.icon :name="$icon" /></span>
                <x-admin.icon name="external" class="w-4 h-4 text-slate-300" />
            </div>
            <div class="mt-3 text-3xl font-extrabold text-slate-800" data-count="{{ $n }}">0</div>
            <div class="text-sm text-slate-500">{{ $label }}</div>
        </a>
    @endforeach
</div>

<div class="grid lg:grid-cols-5 gap-5">
    <div class="card p-5 lg:col-span-3">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-head font-bold text-slate-800">Kelengkapan website</h3>
            <span class="pill {{ $pct === 100 ? 'pill-green' : 'pill-blue' }}">{{ $done }} dari {{ $total }} selesai</span>
        </div>
        <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden mb-4">
            <div class="bar h-full rounded-full bg-gradient-to-r from-[#0B3D91] to-[#4A90D9]" style="width:0" data-progress="{{ $pct }}"></div>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach ($steps as [$text, $ok, $url])
                <li class="flex items-center gap-3 py-2.5 text-sm">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $ok ? 'bg-green-100 text-green-600' : 'border-2 border-slate-300' }}">
                        @if ($ok)<x-admin.icon name="check" class="w-3.5 h-3.5" />@endif
                    </span>
                    <span class="{{ $ok ? 'text-slate-400 line-through' : 'text-slate-700' }} flex-1">{{ $text }}</span>
                    @unless ($ok)<a href="{{ $url }}" class="btn btn-ghost btn-sm">Kerjakan</a>@endunless
                </li>
            @endforeach
        </ul>
    </div>

    <div class="space-y-5 lg:col-span-2">
        <div class="card p-5">
            <h3 class="font-head font-bold text-slate-800 mb-3">Pintasan</h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('admin.content.create', 'projects') }}" class="btn btn-ghost justify-start"><x-admin.icon name="plus" class="w-4 h-4" /> Proyek</a>
                <a href="{{ route('admin.content.create', 'services') }}" class="btn btn-ghost justify-start"><x-admin.icon name="plus" class="w-4 h-4" /> Layanan</a>
                <a href="{{ route('admin.photos') }}" class="btn btn-ghost justify-start"><x-admin.icon name="image" class="w-4 h-4" /> Foto</a>
                <a href="{{ route('admin.company') }}" class="btn btn-ghost justify-start"><x-admin.icon name="building" class="w-4 h-4" /> Profil</a>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-head font-bold text-slate-800">Pesan terbaru</h3>
                <a href="{{ route('admin.messages') }}" class="text-xs text-[#0B3D91] font-semibold hover:underline">Lihat semua</a>
            </div>
            @forelse ($recent as $m)
                <a href="{{ route('admin.messages') }}" class="flex gap-3 py-2 rounded-lg px-2 -mx-2 row-hover">
                    <span class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($m['name'] ?? '?', 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-700 truncate">{{ $m['name'] ?? '-' }}
                            @if (($m['status'] ?? '') === 'baru')<span class="pill pill-blue ml-1">Baru</span>@endif</span>
                        <span class="block text-xs text-slate-500 truncate">{{ $m['body'] ?? '' }}</span>
                    </span>
                </a>
            @empty
                <p class="text-sm text-slate-400 py-2">Belum ada pesan masuk.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
