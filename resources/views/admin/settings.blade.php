@extends('admin.layout')
@section('title', 'Pengaturan')
@section('subtitle', 'Nomor WhatsApp dan email penerima notifikasi')
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" data-dirty class="space-y-5">
    @csrf @method('PUT')

    <div class="card p-5">
        <h3 class="font-head font-bold text-slate-800 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 rounded-lg bg-green-100 text-green-700 flex items-center justify-center"><x-admin.icon name="phone" class="w-4 h-4" /></span>WhatsApp</h3>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="lbl" for="wa">Nomor WhatsApp</label>
                <input id="wa" name="whatsapp" value="{{ old('whatsapp', $whatsapp) }}" class="fld" placeholder="089644113257" inputmode="tel" required>
                <p class="hint">Boleh diawali 08... atau 62... Dipakai tombol WhatsApp melayang, footer, dan halaman Kontak di website.</p>
            </div>
            <div>
                <label class="lbl" for="wag">Kalimat pembuka pesan</label>
                <input id="wag" name="wa_greeting" value="{{ old('wa_greeting', $wa_greeting) }}" class="fld" maxlength="300">
                <p class="hint">Pesan otomatis: "Halo {nama perusahaan}, <em>kalimat ini</em>".</p>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="font-head font-bold text-slate-800 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 rounded-lg bg-blue-100 text-[#0B3D91] flex items-center justify-center"><x-admin.icon name="mail" class="w-4 h-4" /></span>Notifikasi email</h3>
        <label class="lbl" for="ne">Email penerima permintaan download</label>
        <input id="ne" type="email" name="notify_email" value="{{ old('notify_email', $notify_email) }}" class="fld" required>
        <p class="hint">Setiap ada pengunjung yang meminta file, email berisi tombol "Tinjau permintaan" dikirim ke alamat ini (Gmail perusahaan).</p>
    </div>

    <div data-dirty-bar class="card sticky bottom-4 z-20 flex items-center justify-between gap-3 px-4 py-3 shadow-lg transition">
        <span data-dirty-label class="text-sm text-slate-500">Semua perubahan sudah tersimpan</span>
        <button class="btn btn-primary"><x-admin.icon name="check" class="w-4 h-4" /> Simpan pengaturan</button>
    </div>
</form>

<div class="card p-5">
    <h3 class="font-head font-bold text-slate-800 mb-1">Cek pengiriman email</h3>
    <p class="text-sm text-slate-500 mb-3">Pengiriman saat ini memakai <strong>{{ $mailer }}</strong>@if ($mailHost) ({{ $mailHost }}, akun {{ $mailUser ?: 'belum diisi' }})@endif.
        @if (in_array($mailer, ['log', 'array']) || in_array($mailHost, ['mailpit', 'mailhog', 'localhost', '127.0.0.1']))<span class="text-red-600 font-semibold">Email belum benar-benar terkirim: atur MAIL_* di file .env (lihat panduan).</span>@endif</p>
    <form method="POST" action="{{ route('admin.settings.testmail') }}">@csrf
        <button class="btn btn-ghost"><x-admin.icon name="send" class="w-4 h-4" /> Kirim email percobaan</button></form>
</div>
@endsection
