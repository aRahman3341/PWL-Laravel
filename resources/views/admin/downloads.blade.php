@extends('admin.layout')
@section('title', 'Permintaan Download')
@section('subtitle', 'Pengunjung yang meminta file. Setujui agar kode 6 angka muncul di layar mereka')
@section('content')
@php
    $tone  = ['pending' => 'pill-yellow', 'approved' => 'pill-green', 'rejected' => 'pill-red', 'expired' => 'pill-gray', 'used' => 'pill-blue'];
    $label = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa', 'used' => 'Selesai diunduh'];
    $owner = ['company' => 'Dokumen perusahaan', 'project' => 'Lampiran proyek', 'service' => 'Lampiran layanan'];
    $pending = collect($rows)->where('status', 'pending')->count();
@endphp

<div class="card p-4 text-sm text-slate-600 flex gap-3">
    <x-admin.icon name="bell" class="w-5 h-5 text-[#0B3D91] shrink-0 mt-0.5" />
    <div>Setiap permintaan juga dikirim ke email penerima (atur di <a class="underline text-[#0B3D91]" href="{{ route('admin.settings') }}">Pengaturan</a>).
        Setelah <strong>Setujui</strong>, kode 6 angka dibuat otomatis, muncul 15 detik sebagai notifikasi di layar pemohon, dan berlaku <strong>1 jam</strong> hanya di browser pemohon.</div>
</div>

<div class="space-y-3 stagger">
@forelse ($rows as $r)
    @php
        $st = $r['status'];
        $ids = $r['file_ids'] ?? [$r['file_id']];
        $titles = collect($ids)->map(fn ($i) => $files[$i]['title'] ?? '(file dihapus)')->join(', ');
        $f = $files[$ids[0]] ?? null;
    @endphp
    <div class="card p-4 {{ $st === 'pending' ? 'border-l-4 border-l-amber-400' : '' }}">
        <div class="flex flex-wrap items-start gap-4">
            <span class="w-11 h-11 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($r['requester_name'], 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-800">{{ $r['requester_name'] }}</span>
                    <span class="pill {{ $tone[$st] ?? 'pill-gray' }}">{{ $label[$st] ?? $st }}</span>
                    @if (! empty($r['manual_code_id']))<span class="pill pill-purple">Kode manual</span>@endif
                    <span class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($r['created_at'])->locale('id')->diffForHumans() }}</span>
                </div>
                <div class="text-sm mt-1 flex items-center gap-2 text-slate-700"><x-admin.icon name="file" class="w-4 h-4 text-[#0B3D91]" />
                    <strong>{{ $titles }}</strong><strong>{{ $f['title'] ?? '(file dihapus)' }}</strong> <span class="text-xs text-slate-400">{{ $owner[$f['owner_type'] ?? 'company'] ?? '' }}</span></div>
                <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $r['purpose'] }}</p>
                <div class="mt-1 text-xs text-slate-400">IP {{ $r['ip_address'] ?? '-' }}@if (! empty($r['downloaded_at'])) &middot; sudah diunduh @endif</div>
            </div>
            @if ($st === 'pending')
                <div class="flex gap-2 shrink-0">
                    <form method="POST" action="{{ route('admin.downloads.decide', [$r['id'], 'tolak']) }}" data-confirm="Tolak permintaan dari {{ $r['requester_name'] }}?">@csrf
                        <button class="btn btn-ghost btn-sm text-red-600" data-no-loading><x-admin.icon name="x" class="w-4 h-4" /> Tolak</button></form>
                    <form method="POST" action="{{ route('admin.downloads.decide', [$r['id'], 'setujui']) }}">@csrf
                        <button class="btn btn-primary btn-sm"><x-admin.icon name="check" class="w-4 h-4" /> Setujui</button></form>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card empty-state"><x-admin.icon name="inbox" class="w-10 h-10 mx-auto mb-2" /><p class="text-sm">Belum ada permintaan download.</p></div>
@endforelse
</div>
@endsection
