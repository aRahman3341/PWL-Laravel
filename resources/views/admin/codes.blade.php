@extends('admin.layout')
@section('title', 'Kode Manual')
@section('subtitle', 'Cadangan: kode tetap untuk orang tertentu, tanpa menunggu persetujuan')
@section('content')
<div class="card p-4 text-sm text-slate-600 flex gap-3">
    <x-admin.icon name="alert" class="w-5 h-5 text-[#0B3D91] shrink-0 mt-0.5" />
    <div>
        <strong>Apa fungsi halaman ini?</strong> Alur utama otomatis: pengunjung minta lewat tombol Download, Anda setujui di
        <a class="underline text-[#0B3D91]" href="{{ route('admin.downloads') }}">Permintaan Download</a>, lalu kode 6 angka muncul sendiri.
        <em>Kode Manual</em> untuk orang yang sudah Anda percaya: Anda buat kode untuk <strong>nama tertentu</strong>, lalu berikan kode itu langsung.
        Di pop-up Download, orang tersebut mengetik <strong>nama yang sama persis</strong> dengan "Nama pemilik kode" + kode ini, dan file langsung bisa diunduh tanpa menunggu.
        Kalau nama yang diketik berbeda, kode ditolak.
    </div>
</div>

@if (session('new_code'))
    <div class="rounded-2xl border-2 border-yellow-300 bg-yellow-50 p-5" style="animation:pop .3s ease">
        <div class="text-sm text-yellow-900 mb-1">Kode baru berhasil dibuat. Salin dan berikan ke pemiliknya (kode ini juga bisa dilihat lagi di daftar di bawah):</div>
        <div class="flex flex-wrap items-center gap-3">
            <span id="new-code" class="text-3xl font-mono font-bold tracking-widest text-slate-800 select-all">{{ session('new_code') }}</span>
            <button type="button" data-copy="#new-code" data-no-loading class="btn btn-ghost btn-sm"><x-admin.icon name="copy" class="w-4 h-4" /> Salin</button>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('admin.codes.store') }}" class="card p-5">
    @csrf
    <h3 class="font-head font-bold text-slate-800 mb-4 flex items-center gap-2"><x-admin.icon name="key" class="w-5 h-5 text-[#0B3D91]" /> Buat kode baru</h3>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
        <div><label class="lbl">Nama pemilik kode</label><input name="label" required class="fld" placeholder="Contoh: Asep"></div>
        <div><label class="lbl">Membuka dokumen</label>
            <select name="file_id" class="fld">
                <option value="">Semua dokumen</option>
                @foreach ($docs as $d)<option value="{{ $d['id'] }}">{{ $d['title'] }}</option>@endforeach
            </select></div>
        <div><label class="lbl">Berlaku sampai (opsional)</label><input type="date" name="expires_at" class="fld"></div>
        <div><label class="lbl">Maks. dipakai (opsional)</label><input type="number" name="max_uses" min="1" class="fld" placeholder="Tanpa batas"></div>
    </div>
    <p class="hint mt-3">Kosongkan tanggal dan batas pemakaian jika tidak ingin membatasi.</p>
    <button class="btn btn-primary mt-3"><x-admin.icon name="plus" class="w-4 h-4" /> Buat kode</button>
</form>

<div class="space-y-3 stagger">
@forelse ($rows as $c)
    @php
        $max = $c['max_uses'] ?? null;
        $pct = $max ? min(100, (int) round($c['used_count'] / $max * 100)) : 0;
        $expired = ! empty($c['expires_at']) && \Illuminate\Support\Carbon::parse($c['expires_at'])->isPast();
    @endphp
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $c['is_active'] && ! $expired ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400' }}"><x-admin.icon name="key" /></span>
            <div class="min-w-0 flex-1">
                <div class="font-semibold text-slate-800">{{ $c['label'] }}
                    @if ($expired)<span class="pill pill-red ml-1">Kedaluwarsa</span>
                    @elseif ($c['is_active'])<span class="pill pill-green ml-1">Aktif</span>
                    @else<span class="pill pill-gray ml-1">Nonaktif</span>@endif</div>
                <div class="text-xs text-slate-500">Dipakai {{ $c['used_count'] }}{{ $max ? ' dari ' . $max : '' }} kali &middot;
                    Membuka: <strong>{{ ! empty($c['file_id']) ? ($docTitles[$c['file_id']] ?? 'dokumen (sudah dihapus)') : 'semua dokumen' }}</strong> &middot;
                    {{ ! empty($c['expires_at']) ? 'berlaku sampai ' . \Illuminate\Support\Str::substr($c['expires_at'], 0, 10) : 'tanpa batas waktu' }}</div>
                @if ($max)<div class="h-1.5 mt-2 rounded-full bg-slate-100 overflow-hidden max-w-xs"><div class="bar h-full bg-[#4A90D9]" style="width:0" data-progress="{{ $pct }}"></div></div>@endif
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.codes.toggle', $c['id']) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $c['is_active'] ? 0 : 1 }}">
                    <button class="btn btn-ghost btn-sm">{{ $c['is_active'] ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.codes.destroy', $c['id']) }}" data-confirm="Kode ini akan dihapus. Pemiliknya tidak bisa memakainya lagi.">
                    @csrf @method('DELETE')<button class="btn btn-danger btn-sm" data-no-loading><x-admin.icon name="trash" class="w-4 h-4" /></button>
                </form>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3">
            @if (! empty($c['code']))
                <span class="text-xs text-slate-500">Kode:</span>
                <span id="cdm-{{ $c['id'] }}" class="font-mono tracking-widest text-sm bg-slate-100 rounded px-2 py-1">••••-••••-••••</span>
                <span id="cdv-{{ $c['id'] }}" class="hidden font-mono tracking-widest text-sm bg-yellow-50 border border-yellow-200 rounded px-2 py-1 select-all">{{ $c['code'] }}</span>
                <button type="button" data-reveal-code="{{ $c['id'] }}" data-no-loading class="btn btn-ghost btn-sm">Lihat kode</button>
                <button type="button" data-copy="#cdv-{{ $c['id'] }}" data-no-loading class="btn btn-ghost btn-sm"><x-admin.icon name="copy" class="w-4 h-4" /> Salin</button>
            @else
                <span class="text-xs text-slate-400">Kode lama ini dibuat sebelum pembaruan, jadi tidak bisa ditampilkan lagi. Hapus lalu buat ulang kalau perlu melihat kodenya.</span>
            @endif
        </div>
    </div>
@empty
    <div class="card empty-state"><x-admin.icon name="key" class="w-10 h-10 mx-auto mb-2" /><p class="text-sm">Belum ada kode manual.</p></div>
@endforelse
</div>

<script>
document.querySelectorAll('[data-reveal-code]').forEach(function (b) {
    b.addEventListener('click', function () {
        var id = b.getAttribute('data-reveal-code'), m = document.getElementById('cdm-' + id), v = document.getElementById('cdv-' + id);
        var show = v.classList.contains('hidden');
        v.classList.toggle('hidden', !show); m.classList.toggle('hidden', show);
        b.textContent = show ? 'Sembunyikan' : 'Lihat kode';
    });
});
</script>
@endsection