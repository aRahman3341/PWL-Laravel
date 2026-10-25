<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Pegawai — PWL Konsultan</title>
    @if ($logo = \App\Services\SitePhotos::url('logo'))<link rel="icon" href="{{ $logo }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes up{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
        @keyframes spin{to{transform:rotate(360deg)}}
        .card-in{animation:up .6s cubic-bezier(.2,.8,.2,1) both}
        .blob{position:absolute;border-radius:50%;filter:blur(60px);opacity:.35;animation:float 9s ease-in-out infinite}
        .spinner{width:1em;height:1em;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite}
        .fld{width:100%;border:1px solid #cbd5e1;border-radius:.6rem;padding:.65rem .85rem;font-size:.9rem;outline:none;transition:border-color .2s,box-shadow .2s}
        .fld:focus{border-color:#0B3D91;box-shadow:0 0 0 3px rgba(11,61,145,.15)}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-gradient-to-br from-[#0B3D91] via-[#123f93] to-[#082d6e]">
    <div class="blob w-72 h-72 bg-[#4A90D9] -top-10 -left-10"></div>
    <div class="blob w-80 h-80 bg-blue-300 -bottom-16 -right-10" style="animation-delay:-4s"></div>

    <form method="POST" action="{{ route('admin.login.post') }}" id="login-form" class="card-in relative bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm space-y-5">
        @csrf
        <div class="text-center">
            @if ($logo)<img src="{{ $logo }}" alt="Logo" class="h-16 w-16 mx-auto rounded-xl object-contain mb-3">@endif
            <h1 class="font-head text-xl font-extrabold text-[#0B3D91]">Masuk Pegawai</h1>
            <p class="text-sm text-slate-500">PT. Purna Wahana Lestari Konsultan</p>
        </div>

        @error('email')
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $message }}</div>
        @enderror

        <div>
            <label class="block text-sm font-semibold mb-1 text-slate-700" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus class="fld">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1 text-slate-700" for="password">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" placeholder="Password Anda" required class="fld pr-20">
                <button type="button" id="pw-toggle" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-semibold text-[#0B3D91] px-2 py-1 rounded hover:bg-slate-100">Lihat</button>
            </div>
        </div>
        <button id="login-btn" class="w-full bg-[#0B3D91] hover:bg-[#082d6e] text-white rounded-lg py-2.5 font-semibold transition active:scale-[.98]">Masuk</button>
        <a href="{{ route('beranda') }}" class="block text-center text-xs text-slate-400 hover:text-[#0B3D91]">&larr; Kembali ke website</a>
    </form>

<script>
    var pw = document.getElementById('password'), tg = document.getElementById('pw-toggle');
    tg.addEventListener('click', function () {
        var show = pw.type === 'password'; pw.type = show ? 'text' : 'password'; tg.textContent = show ? 'Sembunyikan' : 'Lihat';
    });
    document.getElementById('login-form').addEventListener('submit', function () {
        var b = document.getElementById('login-btn');
        setTimeout(function () { b.disabled = true; b.innerHTML = '<span class="spinner"></span> Memeriksa...'; }, 0);
    });
</script>
</body>
</html>
