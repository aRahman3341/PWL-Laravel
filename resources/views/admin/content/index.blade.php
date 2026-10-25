@extends('admin.layout')
@section('title', $def['label'])
@section('subtitle', count($rows) . ' data. Hanya yang berstatus "Tampil" muncul di website.')
@section('content')
@php
    $hasPhoto = ! empty($def['photo']);
    $thumbs = $hasPhoto ? \App\Services\SitePhotos::ownMap($def['photo'] === true ? 'projects' : $def['photo']) : [];
    $keys = array_keys($def['list']);
@endphp
<div data-filter class="space-y-4">
    <div class="card p-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px]">
            <x-admin.icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input data-filter-input type="search" placeholder="Cari {{ strtolower($def['label']) }}..." class="fld" style="padding-left:2.2rem">
        </div>
        @if (isset($def['fields']['category']))
            <div class="flex gap-1.5 flex-wrap items-center">
                <span class="text-xs text-slate-400">Kategori:</span>
                <button type="button" data-chip-cat="all" class="pill pill-purple cursor-pointer">Semua</button>
                <button type="button" data-chip-cat="konstruksi" class="pill pill-gray cursor-pointer">Konstruksi</button>
                <button type="button" data-chip-cat="non-konstruksi" class="pill pill-gray cursor-pointer">Non Konstruksi</button>
            </div>
        @endif
        <div class="flex gap-1.5 flex-wrap items-center">
            <span class="text-xs text-slate-400">Status:</span>
            <button type="button" data-chip="all" class="pill pill-blue cursor-pointer">Semua</button>
            <button type="button" data-chip="tampil" class="pill pill-gray cursor-pointer">Tampil</button>
            <button type="button" data-chip="sembunyi" class="pill pill-gray cursor-pointer">Disembunyikan</button>
        </div>
        <a href="{{ route('admin.content.create', $type) }}" class="btn btn-primary"><x-admin.icon name="plus" class="w-4 h-4" /> Tambah</a>
    </div>

    <div class="card overflow-hidden">
        <div class="px-4 py-2 text-xs text-slate-500 border-b border-slate-100">Menampilkan <strong data-filter-count>{{ count($rows) }}</strong> dari {{ count($rows) }} data</div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    @if ($hasPhoto)<th class="p-3 w-14">Foto</th>@endif
                    @foreach ($def['list'] as $label)<th class="p-3">{{ $label }}</th>@endforeach
                    <th class="p-3 w-36"></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($rows as $row)
                @php
                    $text = strtolower(implode(' ', array_map(fn ($k) => is_bool($row[$k] ?? null) ? '' : (string) ($row[$k] ?? ''), $keys)));
                    $active = (bool) ($row['is_active'] ?? true);
                @endphp
                <tr class="border-t border-slate-100 row-hover align-middle" data-row data-text="{{ $text }}" data-status="{{ $active ? 'tampil' : 'sembunyi' }}" data-cat="{{ \Illuminate\Support\Str::slug($row['category'] ?? '') }}">
                    @if ($hasPhoto)
                        <td class="p-3">
                            @if (! empty($thumbs[$row['id']]))
                                <img src="{{ $thumbs[$row['id']] }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-300 flex items-center justify-center"><x-admin.icon name="image" class="w-5 h-5" /></span>
                            @endif
                        </td>
                    @endif
                    @foreach ($def['list'] as $k => $label)
                        @php $v = $row[$k] ?? null; @endphp
                        <td class="p-3 {{ $loop->first ? 'font-semibold text-slate-800' : '' }}">
                            @if ($loop->first && isset($attach[$row['id']]))
                                <span class="pill {{ $attach[$row['id']] ? 'pill-blue' : 'pill-gray' }} mr-1" title="{{ $attach[$row['id']] ? 'Lampiran file tampil di website' : 'Lampiran file disembunyikan' }}"><x-admin.icon name="paperclip" class="w-3 h-3" /> File</span>
                            @endif
                            @if (is_bool($v))
                                <span class="pill {{ $v ? 'pill-green' : 'pill-gray' }}">{{ $v ? 'Tampil' : 'Disembunyikan' }}</span>
                            @elseif ($k === 'category' && $v)
                                <span class="pill pill-blue">{{ $v === 'non-konstruksi' ? 'Non Konstruksi' : ucfirst($v) }}</span>
                            @else
                                {{ \Illuminate\Support\Str::limit((string) ($v ?? '-'), 90) }}
                            @endif
                        </td>
                    @endforeach
                    <td class="p-3 whitespace-nowrap text-right">
                        <form method="POST" action="{{ route('admin.content.toggle', [$type, $row['id']]) }}" class="inline">
                            @csrf @method('PUT')
                            <button class="btn btn-ghost btn-sm" data-no-loading title="{{ $active ? 'Tampil di website. Klik untuk menyembunyikan' : 'Disembunyikan. Klik untuk menampilkan' }}">
                                <x-admin.icon :name="$active ? 'eye' : 'eye-off'" class="w-4 h-4 {{ $active ? 'text-green-600' : 'text-slate-400' }}" />
                            </button>
                        </form>
                        <a href="{{ route('admin.content.edit', [$type, $row['id']]) }}" class="btn btn-ghost btn-sm" title="Ubah"><x-admin.icon name="edit" class="w-4 h-4" /></a>
                        <form method="POST" action="{{ route('admin.content.destroy', [$type, $row['id']]) }}" class="inline" data-confirm="Data ini akan dihapus permanen dan hilang dari website.">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" title="Hapus" data-no-loading><x-admin.icon name="trash" class="w-4 h-4" /></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div data-filter-none class="empty-state" style="{{ count($rows) ? 'display:none' : '' }}">
            <x-admin.icon name="inbox" class="w-10 h-10 mx-auto mb-2" />
            <p class="text-sm">{{ count($rows) ? 'Tidak ada data yang cocok.' : 'Belum ada data. Klik "Tambah" untuk mulai.' }}</p>
        </div>
    </div>
</div>
@endsection
