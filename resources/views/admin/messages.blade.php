@extends('admin.layout')
@section('title', 'Pesan Masuk')
@section('subtitle', 'Pesan dari formulir Kontak di website')
@section('content')
@php
    $tone = ['baru' => 'pill-blue', 'dibaca' => 'pill-gray', 'diproses' => 'pill-yellow', 'selesai' => 'pill-green', 'spam' => 'pill-red'];
    $count = collect($rows)->countBy(fn ($m) => $m['status'] ?? 'baru');
@endphp
<div data-filter class="space-y-4">
    <div class="card p-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <x-admin.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input data-filter-input type="search" placeholder="Cari nama, email, atau isi pesan..." class="fld" style="padding-left:2.2rem">
        </div>
        <div class="flex gap-1.5 flex-wrap">
            <button type="button" data-chip="all" class="pill pill-blue cursor-pointer">Semua ({{ count($rows) }})</button>
            @foreach ($status as $v => $l)
                <button type="button" data-chip="{{ $v }}" class="pill pill-gray cursor-pointer">{{ $l }} ({{ $count[$v] ?? 0 }})</button>
            @endforeach
        </div>
    </div>

    <div class="space-y-3 stagger">
    @foreach ($rows as $m)
        @php
            $st = $m['status'] ?? 'baru';
            $wa = preg_replace('/\D/', '', (string) ($m['phone'] ?? ''));
            if ($wa !== '' && str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
            $when = ! empty($m['created_at']) ? \Illuminate\Support\Carbon::parse($m['created_at'])->locale('id')->diffForHumans() : '';
        @endphp
        <div class="card p-4 {{ $st === 'baru' ? 'border-l-4 border-l-[#4A90D9]' : '' }}" data-row data-status="{{ $st }}"
             data-text="{{ strtolower(($m['name'] ?? '') . ' ' . ($m['email'] ?? '') . ' ' . ($m['body'] ?? '')) }}">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($m['name'] ?? '?', 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-slate-800">{{ $m['name'] ?? '-' }}</span>
                        <span class="pill {{ $tone[$st] ?? 'pill-gray' }}">{{ $status[$st] ?? $st }}</span>
                        <span class="text-xs text-slate-400">{{ $when }}</span>
                    </div>
                    <div class="text-xs text-slate-500">{{ $m['email'] ?? '-' }}@if (! empty($m['phone'])) &middot; {{ $m['phone'] }}@endif</div>
                    <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $m['body'] ?? '' }}</p>
                    @if (! empty($m['admin_note']))<p class="mt-2 text-xs rounded bg-yellow-50 text-yellow-800 px-2 py-1">Catatan: {{ $m['admin_note'] }}</p>@endif

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if (! empty($m['email']))<a href="mailto:{{ $m['email'] }}" class="btn btn-ghost btn-sm"><x-admin.icon name="mail" class="w-4 h-4" /> Balas email</a>@endif
                        @if ($wa !== '')<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><x-admin.icon name="phone" class="w-4 h-4" /> WhatsApp</a>@endif
                        <button type="button" data-toggle="#fu-{{ $m['id'] }}" aria-expanded="false" class="btn btn-ghost btn-sm">Tindak lanjut <x-admin.icon name="chevron" class="w-4 h-4 chev" /></button>
                    </div>

                    <div id="fu-{{ $m['id'] }}" class="collapse-box"><div>
                        <form method="POST" action="{{ route('admin.messages.update', $m['id']) }}" class="mt-3 grid sm:grid-cols-4 gap-2 items-end">
                            @csrf @method('PUT')
                            <div><label class="lbl">Status</label>
                                <select name="status" class="fld">@foreach ($status as $v => $l)<option value="{{ $v }}" @selected($st === $v)>{{ $l }}</option>@endforeach</select></div>
                            <div class="sm:col-span-2"><label class="lbl">Catatan internal</label><input name="admin_note" value="{{ $m['admin_note'] ?? '' }}" class="fld" placeholder="Opsional"></div>
                            <button class="btn btn-primary">Simpan</button>
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <div data-filter-none class="card empty-state" style="{{ count($rows) ? 'display:none' : '' }}">
        <x-admin.icon name="inbox" class="w-10 h-10 mx-auto mb-2" />
        <p class="text-sm">{{ count($rows) ? 'Tidak ada pesan yang cocok.' : 'Belum ada pesan masuk.' }}</p>
    </div>
    <span class="hidden" data-filter-count></span>
</div>
@endsection
