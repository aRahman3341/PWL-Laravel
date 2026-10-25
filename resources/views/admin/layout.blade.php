<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') — PWL Admin</title>
    @if ($logo = \App\Services\SitePhotos::url('logo'))<link rel="icon" href="{{ $logo }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ---------- penstabil tampilan: ukuran dasar mengikuti lebar layar ---------- */
        html{font-size:clamp(14.5px,13.4px + .18vw,19px);-webkit-text-size-adjust:100%;text-size-adjust:100%}
        body{overflow-x:clip;min-width:320px}
        img,svg{max-width:100%}
        @media (min-width:1700px){.max-w-6xl{max-width:76rem}}
        @media (hover:none){.card-hover:hover{transform:none;box-shadow:0 1px 2px rgba(15,23,42,.04)}}
        /* ---------- kerangka: sidebar tetap + bisa dilipat ---------- */
        #sidebar{position:fixed;inset:0 auto 0 0;z-index:40;width:16rem;display:flex;flex-direction:column;background:linear-gradient(180deg,#0B3D91 0%,#082d6e 100%);color:#bfdbfe;transition:width .3s ease,transform .3s ease}
        #main{padding-left:16rem;transition:padding-left .3s ease;min-height:100vh}
        body.sb-collapsed #sidebar{width:4.5rem}
        body.sb-collapsed #main{padding-left:4.5rem}
        .sb-label{white-space:nowrap;transition:opacity .2s ease}
        body.sb-collapsed .sb-label{opacity:0;width:0;overflow:hidden;position:absolute}
        body.sb-collapsed .sb-section{opacity:0;height:0;margin:0;overflow:hidden}
        body.sb-collapsed #sb-collapse svg{transform:rotate(180deg)}
        #sb-overlay{position:fixed;inset:0;z-index:35;background:rgba(15,23,42,.5);opacity:0;pointer-events:none;transition:opacity .3s}
        @media (max-width:1023px){
            #sidebar,body.sb-collapsed #sidebar{width:16rem;transform:translateX(-100%)}
            body.sb-open #sidebar{transform:translateX(0);box-shadow:0 0 40px rgba(0,0,0,.35)}
            body.sb-open #sb-overlay{opacity:1;pointer-events:auto}
            #main,body.sb-collapsed #main{padding-left:0}
            body.sb-collapsed .sb-label{opacity:1;width:auto;position:static}
            body.sb-collapsed .sb-section{opacity:1;height:auto;margin:inherit}
            #sb-collapse{display:none}
        }
        .nav-item{position:relative;display:flex;align-items:center;gap:.75rem;padding:.6rem .85rem;border-radius:.65rem;font-size:.875rem;font-weight:500;color:#bfdbfe;transition:background .2s,color .2s,transform .2s}
        .nav-item:hover{background:rgba(255,255,255,.1);color:#fff;transform:translateX(3px)}
        .nav-item svg{flex-shrink:0;transition:transform .2s}
        .nav-item:hover svg{transform:scale(1.12)}
        .nav-item.active{background:rgba(255,255,255,.16);color:#fff;font-weight:600}
        .nav-item.active::before{content:"";position:absolute;left:-.75rem;top:22%;bottom:22%;width:4px;border-radius:0 4px 4px 0;background:#4A90D9}
        .nav-badge{margin-left:auto;background:#ef4444;color:#fff;border-radius:999px;font-size:.68rem;font-weight:700;padding:.05rem .45rem;animation:pulseDot 2s infinite}
        .sb-section{font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:#7aa7e6;padding:.9rem .85rem .3rem;transition:opacity .2s}
        /* ---------- animasi ---------- */
        @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
        @keyframes toastIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}
        @keyframes pulseDot{0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.55)}50%{box-shadow:0 0 0 6px rgba(239,68,68,0)}}
        @keyframes pop{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:none}}
        @keyframes spin{to{transform:rotate(360deg)}}
        .page-enter>*{opacity:0;animation:fadeUp .5s cubic-bezier(.2,.8,.2,1) forwards}
        .page-enter>*:nth-child(1){animation-delay:.02s}.page-enter>*:nth-child(2){animation-delay:.08s}.page-enter>*:nth-child(3){animation-delay:.14s}
        .page-enter>*:nth-child(4){animation-delay:.2s}.page-enter>*:nth-child(5){animation-delay:.26s}.page-enter>*:nth-child(n+6){animation-delay:.3s}
        .stagger>*{opacity:0;animation:fadeUp .45s cubic-bezier(.2,.8,.2,1) forwards}
        .stagger>*:nth-child(1){animation-delay:.05s}.stagger>*:nth-child(2){animation-delay:.1s}.stagger>*:nth-child(3){animation-delay:.15s}
        .stagger>*:nth-child(4){animation-delay:.2s}.stagger>*:nth-child(5){animation-delay:.25s}.stagger>*:nth-child(6){animation-delay:.3s}.stagger>*:nth-child(n+7){animation-delay:.35s}
        .toast{animation:toastIn .35s cubic-bezier(.2,.8,.2,1);transition:opacity .3s,transform .3s}
        .toast.out{opacity:0;transform:translateX(30px)}
        .spinner{width:1em;height:1em;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;display:inline-block;animation:spin .7s linear infinite}
        .collapse-box{display:grid;grid-template-rows:0fr;transition:grid-template-rows .3s ease}
        .collapse-box.open{grid-template-rows:1fr}
        .collapse-box>div{overflow:hidden}
        [aria-expanded=true] .chev{transform:rotate(180deg)}
        .chev{transition:transform .25s}
        .bar{transition:width 1s cubic-bezier(.2,.8,.2,1)}
        /* ---------- komponen sederhana ---------- */
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:.9rem;box-shadow:0 1px 2px rgba(15,23,42,.04)}
        .card-hover{transition:box-shadow .25s,transform .25s,border-color .25s}
        .card-hover:hover{box-shadow:0 10px 28px rgba(15,23,42,.09);transform:translateY(-3px);border-color:#bfdbfe}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border-radius:.6rem;padding:.5rem .95rem;font-size:.875rem;font-weight:600;border:1px solid transparent;cursor:pointer;transition:background .2s,transform .15s,box-shadow .2s,color .2s}
        .btn:active{transform:scale(.97)}
        .btn:disabled{opacity:.65;cursor:not-allowed}
        .btn-primary{background:#0B3D91;color:#fff}.btn-primary:hover{background:#082d6e;box-shadow:0 6px 16px rgba(11,61,145,.28)}
        .btn-ghost{background:#fff;border-color:#cbd5e1;color:#334155}.btn-ghost:hover{background:#f1f5f9}
        .btn-danger{color:#dc2626;background:transparent}.btn-danger:hover{background:#fef2f2}
        .btn-sm{padding:.3rem .65rem;font-size:.8rem}
        .fld{width:100%;border:1px solid #cbd5e1;border-radius:.55rem;padding:.5rem .75rem;font-size:.875rem;background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
        .fld:focus{border-color:#0B3D91;box-shadow:0 0 0 3px rgba(11,61,145,.15)}
        .lbl{display:block;font-size:.8rem;font-weight:600;color:#334155;margin-bottom:.3rem}
        .hint{font-size:.75rem;color:#64748b;margin-top:.25rem}
        .pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;padding:.12rem .6rem;font-size:.72rem;font-weight:600;white-space:nowrap}
        .pill-green{background:#dcfce7;color:#166534}.pill-red{background:#fee2e2;color:#991b1b}.pill-blue{background:#dbeafe;color:#1e40af}
        .pill-yellow{background:#fef9c3;color:#854d0e}.pill-gray{background:#f1f5f9;color:#475569}.pill-purple{background:#ede9fe;color:#5b21b6}
        .switch{position:relative;display:inline-flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.875rem}
        .switch input{position:absolute;opacity:0;width:0;height:0}
        .switch .track{width:2.5rem;height:1.4rem;background:#cbd5e1;border-radius:999px;position:relative;transition:background .25s;flex-shrink:0}
        .switch .track::after{content:"";position:absolute;top:2px;left:2px;width:1.1rem;height:1.1rem;background:#fff;border-radius:50%;transition:transform .25s;box-shadow:0 1px 3px rgba(0,0,0,.3)}
        .switch input:checked+.track{background:#0B3D91}
        .switch input:checked+.track::after{transform:translateX(1.1rem)}
        .switch input:focus-visible+.track{box-shadow:0 0 0 3px rgba(11,61,145,.25)}
        .row-hover{transition:background .15s}.row-hover:hover{background:#f8fafc}
        .dz{border:2px dashed #cbd5e1;border-radius:.8rem;transition:border-color .2s,background .2s;cursor:pointer}
        .dz:hover,.dz.over{border-color:#0B3D91;background:#eff6ff}
        .empty-state{text-align:center;padding:3rem 1rem;color:#94a3b8}
    </style>
</head>
<body class="bg-slate-50 text-slate-700 antialiased">
<script>try{if(localStorage.getItem('pwl_sb')==='1')document.body.classList.add('sb-collapsed')}catch(e){}</script>
@php
    $is = fn (string ...$p) => request()->is(...$p);
    $roleLabel = ['management' => 'Management', 'super_admin' => 'Super Admin', 'admin' => 'Admin'][$admin['role']] ?? $admin['role'];
    $manage = in_array($admin['role'], ['super_admin', 'management'], true);
@endphp

<div id="sb-overlay"></div>

<aside id="sidebar">
    <div class="flex items-center gap-3 px-4 h-16 border-b border-white/10 shrink-0">
        @if ($logo)<img src="{{ $logo }}" alt="" class="h-9 w-9 rounded-lg bg-white p-0.5 object-contain shrink-0">@endif
        <div class="sb-label leading-tight">
            <div class="font-head font-extrabold text-white text-sm">PWL Admin</div>
            <div class="text-[11px] text-blue-300">Panel pegawai</div>
        </div>
        <button id="sb-collapse" type="button" class="ml-auto text-blue-200 hover:text-white p-1 rounded hover:bg-white/10" title="Lipat / buka menu">
            <x-admin.icon name="collapse" class="w-5 h-5 transition-transform duration-300" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
        <x-admin.nav-item :href="route('admin.dashboard')" icon="dashboard" label="Beranda Admin" :active="$is('admin')" />

        <div class="sb-section">Isi website</div>
        <x-admin.nav-item :href="route('admin.company')" icon="building" label="Profil Perusahaan" :active="$is('admin/company')" />
        <x-admin.nav-item :href="route('admin.photos')" icon="image" label="Foto Website" :active="$is('admin/photos')" />
        <x-admin.nav-item :href="route('admin.content.index', 'services')" icon="layers" label="Layanan" :active="$is('admin/content/services*')" />
        <x-admin.nav-item :href="route('admin.content.index', 'projects')" icon="briefcase" label="Proyek" :active="$is('admin/content/projects*')" />
        <x-admin.nav-item :href="route('admin.content.index', 'management')" icon="users" label="Pengurus" :active="$is('admin/content/management*')" />
        <x-admin.nav-item :href="route('admin.content.index', 'legal')" icon="shield" label="Legalitas" :active="$is('admin/content/legal*')" />
        <x-admin.nav-item :href="route('admin.messages')" icon="mail" label="Pesan Masuk" :active="$is('admin/messages*')" :badge="$newMessages ?? 0" />

        @if ($manage)
            <div class="sb-section">Pengelolaan</div>
            <x-admin.nav-item :href="route('admin.downloads')" icon="bell" label="Permintaan Download" :active="$is('admin/downloads*')" :badge="$pendingDownloads ?? 0" />
            <x-admin.nav-item :href="route('admin.files.index')" icon="lock" label="Dokumen Download" :active="$is('admin/files*')" />
            <x-admin.nav-item :href="route('admin.users.index')" icon="user" label="Akun Pegawai" :active="$is('admin/users*')" />
            <x-admin.nav-item :href="route('admin.settings')" icon="settings" label="Pengaturan" :active="$is('admin/settings*')" />
            <x-admin.nav-item :href="route('admin.codes.index')" icon="key" label="Kode Manual" :active="$is('admin/codes*')" />
        @endif
        @if ($admin['role'] === 'management')
            <x-admin.nav-item :href="route('admin.audit')" icon="clock" label="Riwayat Aktivitas" :active="$is('admin/audit*')" />
        @endif
    </nav>

    <div class="border-t border-white/10 p-3 space-y-1 shrink-0">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="h-9 w-9 rounded-full bg-[#4A90D9] text-white font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($admin['full_name'], 0, 1)) }}</div>
            <div class="sb-label leading-tight min-w-0">
                <div class="text-sm font-semibold text-white truncate">{{ $admin['full_name'] }}</div>
                <div class="text-[11px] text-blue-300">{{ $roleLabel }}</div>
            </div>
        </div>
        <a href="{{ route('beranda') }}" target="_blank" class="nav-item" title="Lihat website"><x-admin.icon name="external" /><span class="sb-label">Lihat website</span></a>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf
            <button class="nav-item w-full text-left" title="Keluar"><x-admin.icon name="logout" /><span class="sb-label">Keluar</span></button>
        </form>
    </div>
</aside>

<div id="main">
    <header class="sticky top-0 z-30 bg-white/85 backdrop-blur border-b border-slate-200">
        <div class="flex items-center gap-3 px-4 sm:px-6 h-16">
            <button id="sb-open" type="button" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100" aria-label="Buka menu"><x-admin.icon name="menu" class="w-6 h-6" /></button>
            <div class="min-w-0">
                <h1 class="font-head font-extrabold text-[#0B3D91] text-lg leading-tight truncate">@yield('title', 'Admin')</h1>
                @hasSection('subtitle')<p class="text-xs text-slate-500 truncate">@yield('subtitle')</p>@endif
            </div>
            <a href="{{ route('beranda') }}" target="_blank" class="btn btn-ghost btn-sm ml-auto hidden sm:inline-flex"><x-admin.icon name="external" class="w-4 h-4" /> Lihat website</a>
        </div>
    </header>

    <main class="p-4 sm:p-6 max-w-6xl mx-auto">
        <div class="page-enter space-y-5">
            @yield('content')
        </div>
    </main>
</div>

{{-- notifikasi --}}
<div id="toasts" class="fixed top-4 right-4 z-[70] space-y-2 w-[min(22rem,calc(100vw-2rem))]">
    @if (session('ok'))
        <div class="toast flex gap-3 items-start rounded-xl bg-white border border-green-200 shadow-lg p-3" data-auto="4500">
            <x-admin.icon name="ok" class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
            <div class="text-sm flex-1">{{ session('ok') }}</div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-close-toast><x-admin.icon name="x" class="w-4 h-4" /></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="toast flex gap-3 items-start rounded-xl bg-white border border-red-200 shadow-lg p-3">
            <x-admin.icon name="alert" class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
            <div class="text-sm flex-1 space-y-0.5">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-close-toast><x-admin.icon name="x" class="w-4 h-4" /></button>
        </div>
    @endif
</div>

{{-- dialog konfirmasi --}}
<div id="confirm-modal" class="fixed inset-0 z-[80] items-center justify-center p-4 bg-slate-900/50" style="display:none">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" style="animation:pop .2s ease">
        <div class="w-11 h-11 rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-3"><x-admin.icon name="alert" /></div>
        <h3 class="font-head font-bold text-slate-800 mb-1">Yakin?</h3>
        <p id="confirm-text" class="text-sm text-slate-600"></p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="confirm-no" class="btn btn-ghost">Batal</button>
            <button type="button" id="confirm-yes" class="btn" style="background:#dc2626;color:#fff">Ya, lanjutkan</button>
        </div>
    </div>
</div>

<script>
(function () {
    var body = document.body;

    /* --- sidebar: lipat (desktop) & drawer (HP) --- */
    var col = document.getElementById('sb-collapse');
    if (col) col.addEventListener('click', function () {
        body.classList.toggle('sb-collapsed');
        try { localStorage.setItem('pwl_sb', body.classList.contains('sb-collapsed') ? '1' : '0'); } catch (e) {}
    });
    var openBtn = document.getElementById('sb-open'), ov = document.getElementById('sb-overlay');
    if (openBtn) openBtn.addEventListener('click', function () { body.classList.add('sb-open'); });
    if (ov) ov.addEventListener('click', function () { body.classList.remove('sb-open'); });

    /* --- notifikasi --- */
    function closeToast(t) { t.classList.add('out'); setTimeout(function () { t.remove(); }, 320); }
    function bindToast(t) {
        var b = t.querySelector('[data-close-toast]');
        if (b) b.addEventListener('click', function () { closeToast(t); });
        var a = parseInt(t.getAttribute('data-auto') || '0', 10);
        if (a) setTimeout(function () { closeToast(t); }, a);
    }
    document.querySelectorAll('#toasts .toast').forEach(bindToast);
    window.toast = function (msg) {
        var t = document.createElement('div');
        t.className = 'toast flex gap-3 items-start rounded-xl bg-white border border-green-200 shadow-lg p-3';
        t.setAttribute('data-auto', '2500');
        t.innerHTML = '<div class="text-sm flex-1"></div>';
        t.firstChild.textContent = msg;
        document.getElementById('toasts').appendChild(t); bindToast(t);
    };

    /* --- dialog konfirmasi + animasi tombol saat kirim --- */
    var modal = document.getElementById('confirm-modal'), mText = document.getElementById('confirm-text'), pending = null;
    function showModal() { modal.style.display = 'flex'; }
    function hideModal() { modal.style.display = 'none'; pending = null; }
    document.getElementById('confirm-no').addEventListener('click', hideModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) hideModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { hideModal(); body.classList.remove('sb-open'); } });
    document.getElementById('confirm-yes').addEventListener('click', function () {
        var f = pending; hideModal();
        if (f) { f.__ok = true; if (f.requestSubmit) f.requestSubmit(); else f.submit(); }
    });

    var dirty = false;
    document.addEventListener('submit', function (e) {
        var f = e.target, msg = f.getAttribute && f.getAttribute('data-confirm');
        if (msg && !f.__ok) { e.preventDefault(); pending = f; mText.textContent = msg; showModal(); return; }
        dirty = false;
        var btn = e.submitter || f.querySelector('button:not([type=button])');
        if (btn && !btn.hasAttribute('data-no-loading')) {
            setTimeout(function () {
                btn.dataset.html = btn.innerHTML; btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Memproses...';
            }, 0);
        }
    }, true);
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('button[data-html]').forEach(function (b) { b.innerHTML = b.dataset.html; b.disabled = false; });
    });

    /* --- penanda "ada perubahan belum disimpan" --- */
    document.querySelectorAll('form[data-dirty]').forEach(function (f) {
        var label = f.querySelector('[data-dirty-label]'), bar = f.querySelector('[data-dirty-bar]');
        function mark() {
            dirty = true;
            if (label) label.textContent = 'Ada perubahan yang belum disimpan';
            if (bar) bar.classList.add('ring-2', 'ring-[#4A90D9]');
        }
        f.addEventListener('input', mark); f.addEventListener('change', mark);
    });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    /* --- kotak unggah foto/file: klik, seret-lepas, pratinjau --- */
    document.querySelectorAll('[data-dropzone]').forEach(function (dz) {
        var input = dz.querySelector('input[type=file]'), img = dz.querySelector('[data-preview]'),
            name = dz.querySelector('[data-fname]'), ph = dz.querySelector('[data-placeholder]'),
            form = dz.closest('form'), submit = form ? form.querySelector('[data-needs-file]') : null;
        if (submit) submit.disabled = true;
        function show(file) {
            if (!file) return;
            if (name) name.textContent = file.name + ' (' + (file.size / 1048576).toFixed(1) + ' MB)';
            if (submit) submit.disabled = false;
            if (img && file.type.indexOf('image/') === 0) {
                var r = new FileReader();
                r.onload = function (ev) { img.src = ev.target.result; img.classList.remove('hidden'); if (ph) ph.classList.add('hidden'); };
                r.readAsDataURL(file);
            }
        }
        dz.addEventListener('click', function (e) { if (e.target !== input) input.click(); });
        input.addEventListener('change', function () { show(input.files[0]); });
        ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('over'); }); });
        dz.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; show(input.files[0]); }
        });
    });

    /* --- buka/tutup panel (animasi lipat) --- */
    document.querySelectorAll('[data-toggle]').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = document.querySelector(b.getAttribute('data-toggle'));
            if (!t) return;
            var open = t.classList.toggle('open');
            b.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    /* --- cari + saring daftar (status + kategori) --- */
    document.querySelectorAll('[data-filter]').forEach(function (box) {
        var input = box.querySelector('[data-filter-input]'), chips = box.querySelectorAll('[data-chip]'),
            cats = box.querySelectorAll('[data-chip-cat]'),
            rows = box.querySelectorAll('[data-row]'), count = box.querySelector('[data-filter-count]'),
            none = box.querySelector('[data-filter-none]'), cur = 'all', curCat = 'all';
        function run() {
            var q = input ? input.value.trim().toLowerCase() : '', n = 0;
            rows.forEach(function (r) {
                var ok = (!q || (r.getAttribute('data-text') || '').indexOf(q) > -1)
                    && (cur === 'all' || r.getAttribute('data-status') === cur)
                    && (curCat === 'all' || r.getAttribute('data-cat') === curCat);
                r.style.display = ok ? '' : 'none'; if (ok) n++;
            });
            if (count) count.textContent = n;
            if (none) none.style.display = n === 0 ? '' : 'none';
        }
        if (input) input.addEventListener('input', run);
        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                cur = c.getAttribute('data-chip');
                chips.forEach(function (x) { x.classList.toggle('pill-blue', x === c); x.classList.toggle('pill-gray', x !== c); });
                run();
            });
        });
        cats.forEach(function (c) {
            c.addEventListener('click', function () {
                curCat = c.getAttribute('data-chip-cat');
                cats.forEach(function (x) { x.classList.toggle('pill-purple', x === c); x.classList.toggle('pill-gray', x !== c); });
                run();
            });
        });
        run();
    });

    /* --- salin teks --- */
    document.querySelectorAll('[data-copy]').forEach(function (b) {
        b.addEventListener('click', function () {
            var t = document.querySelector(b.getAttribute('data-copy'));
            if (t && navigator.clipboard) navigator.clipboard.writeText(t.textContent.trim()).then(function () { window.toast('Kode disalin'); });
        });
    });

    /* --- angka naik & bar progres --- */
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var to = parseInt(el.getAttribute('data-count'), 10) || 0, t0 = null;
        function step(ts) {
            t0 = t0 || ts; var p = Math.min((ts - t0) / 800, 1);
            el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });
    setTimeout(function () {
        document.querySelectorAll('[data-progress]').forEach(function (b) { b.style.width = b.getAttribute('data-progress') + '%'; });
    }, 150);
})();
</script>
</body>
</html>
