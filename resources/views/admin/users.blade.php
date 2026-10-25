@extends('admin.layout')
@section('title', 'Akun Pegawai')
@section('subtitle', $admin['role'] === 'management' ? 'Anda dapat mengelola semua akun' : 'Anda dapat mengelola akun dengan role Admin')
@section('content')
@php
    $label = ['management' => 'Management', 'super_admin' => 'Super Admin', 'admin' => 'Admin'];
    $tone  = ['management' => 'pill-purple', 'super_admin' => 'pill-blue', 'admin' => 'pill-gray'];
    $help  = ['management' => 'Mengatur semuanya', 'super_admin' => 'Isi website, akun admin, kode akses', 'admin' => 'Hanya mengisi konten website'];
@endphp

<div class="card p-4 flex items-center justify-between gap-3">
    <div class="text-sm text-slate-600">{{ count($users) }} akun terdaftar</div>
    <button type="button" data-toggle="#new-user" aria-expanded="false" class="btn btn-primary"><x-admin.icon name="plus" class="w-4 h-4" /> Tambah akun <x-admin.icon name="chevron" class="w-4 h-4 chev" /></button>
</div>

<div id="new-user" class="collapse-box {{ $errors->any() && old('email') ? 'open' : '' }}"><div>
    <form method="POST" action="{{ route('admin.users.store') }}" class="card p-5 grid sm:grid-cols-2 gap-4">
        @csrf
        <div><label class="lbl">Nama lengkap</label><input name="full_name" required class="fld" value="{{ old('full_name') }}"></div>
        <div><label class="lbl">Email (untuk login)</label><input name="email" type="email" required class="fld" value="{{ old('email') }}"></div>
        <div><label class="lbl">Password</label><input name="password" type="password" required minlength="8" class="fld" placeholder="Minimal 8 karakter"></div>
        <div><label class="lbl">Peran</label>
            <select name="role" class="fld">@foreach ($roles as $r)<option value="{{ $r }}">{{ $label[$r] }} - {{ $help[$r] }}</option>@endforeach</select></div>
        <div class="sm:col-span-2"><button class="btn btn-primary"><x-admin.icon name="check" class="w-4 h-4" /> Buat akun</button></div>
    </form>
</div></div>

<div class="space-y-3 stagger">
@foreach ($users as $u)
    @php
        $can = in_array($u['role'], $roles, true) && $u['id'] !== $admin['id'];
        $last = ! empty($u['last_login_at']) ? \Illuminate\Support\Carbon::parse($u['last_login_at'])->locale('id')->diffForHumans() : 'belum pernah masuk';
    @endphp
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-full bg-[#0B3D91] text-white font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($u['full_name'], 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-800">{{ $u['full_name'] }}</span>
                    @if ($u['id'] === $admin['id'])<span class="pill pill-green">Anda</span>@endif
                    <span class="pill {{ $tone[$u['role']] ?? 'pill-gray' }}">{{ $label[$u['role']] ?? $u['role'] }}</span>
                    @unless ($u['is_active'])<span class="pill pill-red">Nonaktif</span>@endunless
                </div>
                <div class="text-xs text-slate-500 truncate">{{ $u['email'] }} &middot; terakhir masuk {{ $last }}</div>
            </div>
            @if ($can)
                <button type="button" data-toggle="#ed-{{ $u['id'] }}" aria-expanded="false" class="btn btn-ghost btn-sm">Ubah <x-admin.icon name="chevron" class="w-4 h-4 chev" /></button>
            @else
                <span class="text-xs text-slate-400 flex items-center gap-1"><x-admin.icon name="lock" class="w-4 h-4" /> Terkunci</span>
            @endif
        </div>

        @if ($can)
        <div id="ed-{{ $u['id'] }}" class="collapse-box"><div>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <form method="POST" action="{{ route('admin.users.update', $u['id']) }}" class="grid sm:grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <div><label class="lbl">Nama lengkap</label><input name="full_name" value="{{ $u['full_name'] }}" required class="fld"></div>
                    <div><label class="lbl">Peran</label>
                        <select name="role" class="fld">@foreach ($roles as $r)<option value="{{ $r }}" @selected($u['role'] === $r)>{{ $label[$r] }}</option>@endforeach</select></div>
                    <div><label class="lbl">Password baru</label><input type="password" name="password" class="fld" placeholder="Kosongkan jika tidak diganti"></div>
                    <div class="flex items-end"><label class="switch"><input type="checkbox" name="is_active" value="1" @checked($u['is_active'])><span class="track"></span><span>Akun aktif</span></label></div>
                    <div class="sm:col-span-2"><button class="btn btn-primary"><x-admin.icon name="check" class="w-4 h-4" /> Simpan</button></div>
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $u['id']) }}" class="mt-2" data-confirm="Akun {{ $u['full_name'] }} akan dihapus permanen dan tidak bisa login lagi.">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" data-no-loading><x-admin.icon name="trash" class="w-4 h-4" /> Hapus akun ini</button>
                </form>
            </div>
        </div></div>
        @endif
    </div>
@endforeach
</div>
@endsection
