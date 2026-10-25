@extends('admin.layout')
@section('title', 'Riwayat Aktivitas')
@section('subtitle', '200 aktivitas terakhir semua pegawai')
@section('content')
@php
    $tone = ['create' => 'pill-green', 'update' => 'pill-blue', 'delete' => 'pill-red', 'login' => 'pill-gray', 'upload' => 'pill-purple'];
    $word = ['create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'login' => 'Masuk', 'upload' => 'Unggah', 'upload_photo' => 'Unggah foto', 'delete_photo' => 'Hapus foto'];
@endphp
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="p-3">Waktu</th><th class="p-3">Pegawai</th><th class="p-3">Aksi</th><th class="p-3">Bagian</th><th class="p-3">IP</th></tr></thead>
        <tbody>
        @forelse ($rows as $a)
            <tr class="border-t border-slate-100 row-hover">
                <td class="p-3 whitespace-nowrap">{{ \Illuminate\Support\Str::of($a['created_at'] ?? '')->substr(0, 16)->replace('T', ' ') }}</td>
                <td class="p-3">{{ $names[$a['user_id'] ?? ''] ?? '-' }}</td>
                <td class="p-3"><span class="pill {{ $tone[explode('_', $a['action'])[0]] ?? 'pill-gray' }}">{{ $word[$a['action']] ?? $a['action'] }}</span></td>
                <td class="p-3">{{ $a['table_name'] ?? '-' }}</td>
                <td class="p-3 text-slate-400">{{ $a['ip_address'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Belum ada aktivitas tercatat.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
