@props([
    'title' => 'Beranda',
])
@php
    $logo = \App\Services\SitePhotos::url('logo');
    $company = $c['name'] ?? 'PT. Purna Wahana Lestari Konsultan';
    $waGreeting = app(\App\Services\Supabase::class)->setting('wa_greeting', 'saya ingin bertanya mengenai layanan konsultansi.');
    $waLink = \App\Services\Whatsapp::link($c['whatsapp'] ?? '', "Halo {$company}, {$waGreeting}");

    // Menu publik. "Layanan" punya dropdown; klik "Layanan" saja menampilkan dua kategori sekaligus.
    $menu = [
        ['label' => 'Beranda', 'route' => 'beranda'],
        ['label' => 'Layanan', 'route' => 'layanan', 'children' => [
            ['Semua Layanan', route('layanan')],
            ['Konstruksi', route('layanan', ['k' => 'konstruksi'])],
            ['Non Konstruksi', route('layanan', ['k' => 'non-konstruksi'])],
        ]],
        ['label' => 'Pengalaman', 'route' => 'pengalaman'],
        ['label' => 'Profil Perusahaan', 'route' => 'profil'],
        ['label' => 'Kontak Kami', 'route' => 'kontak'],
    ];
    $on  = 'bg-[#4A90D9] text-white';
    $off = 'text-blue-100 hover:text-white hover:bg-white/10';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ $company }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($c['about'] ?? '', 155) }}">
    @if ($logo)<link rel="icon" href="{{ $logo }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>document.documentElement.classList.add('js')</script>
    <style>
        /* ===== PENSTABIL TAMPILAN (HP, tablet, laptop, monitor lebar) ===== */
        html{scroll-behavior:smooth;-webkit-text-size-adjust:100%;text-size-adjust:100%;
             font-size:clamp(14.5px,13.4px + .18vw,19px)} /* ukuran dasar mengikuti lebar layar */
        body{overflow-x:clip;min-width:320px}
        img,svg,video,iframe{max-width:100%}
        h1,h2,h3{overflow-wrap:anywhere}
        .min-h-screen{min-height:100vh;min-height:100svh} /* tinggi layar HP yang benar saat bilah alamat muncul */
        #site-header{padding-top:env(safe-area-inset-top)} /* layar berponi */
        @media (min-width:1700px){.max-w-7xl{max-width:86rem}.max-w-6xl{max-width:74rem}}  /* monitor lebar: isi tidak terlalu melebar */
        @media (min-aspect-ratio:21/9) and (min-width:1200px){.hero-sec{min-height:88vh}}   /* layar ultra-lebar */
        @media (max-height:640px) and (orientation:landscape){                              /* HP diputar / layar pendek */
            .hero-sec{min-height:auto}.hero-pad{padding-top:6.5rem!important;padding-bottom:3rem!important}
            .scroll-hint{display:none}#site-header .hbar{height:3.2rem}
        }
        @media (max-width:380px){.hero-title{font-size:1.85rem!important}}                    /* HP kecil */
        @media (hover:none){.lift:hover{transform:none;box-shadow:none}}                    /* layar sentuh: tanpa efek hover menempel */
        @keyframes toastIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}
        /* muncul saat digulir */
        .js [data-reveal]{opacity:0;transform:translateY(28px);transition:opacity .8s cubic-bezier(.2,.8,.2,1),transform .8s cubic-bezier(.2,.8,.2,1)}
        .js [data-reveal=left]{transform:translateX(-40px)}
        .js [data-reveal=right]{transform:translateX(40px)}
        .js [data-reveal=zoom]{transform:scale(.92)}
        .js [data-reveal].in{opacity:1;transform:none}
        /* navbar */
        #site-header{transition:background .3s,box-shadow .3s}
        #site-header .hbar{height:4rem;transition:height .3s}
        #site-header.scrolled{background:rgba(11,61,145,.95);backdrop-filter:blur(10px);box-shadow:0 10px 30px rgba(8,45,110,.35)}
        #site-header.scrolled .hbar{height:3.4rem}
        /* bentuk melayang */
        .shape{position:absolute;background:#fff;opacity:.1;border-radius:1.5rem;animation:floaty 9s ease-in-out infinite;pointer-events:none}
        @keyframes floaty{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-24px) rotate(8deg)}}
        @keyframes shine{to{background-position:200% center}}
        .shimmer{background:linear-gradient(90deg,#4A90D9,#dbeafe,#4A90D9);background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;animation:shine 4s linear infinite}
        @keyframes bounceY{0%,100%{transform:translateY(0)}50%{transform:translateY(8px)}}
        @keyframes ring{0%{transform:scale(1);opacity:.55}100%{transform:scale(1.9);opacity:0}}
        @keyframes tip{0%,8%{opacity:0;transform:translateX(10px)}14%,70%{opacity:1;transform:none}80%,100%{opacity:0;transform:translateX(10px)}}
        /* kartu */
        .lift{transition:transform .35s cubic-bezier(.2,.8,.2,1),box-shadow .35s,border-color .35s}
        .lift:hover{transform:translateY(-8px);box-shadow:0 22px 44px rgba(11,61,145,.16);border-color:#4A90D9}
        .icon-pop{transition:transform .35s}
        .group:hover .icon-pop{transform:scale(1.14) rotate(-6deg)}
        .btn-shine{position:relative;overflow:hidden}
        .btn-shine::after{content:"";position:absolute;top:0;left:-70%;width:45%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.55),transparent);transform:skewX(-20deg);transition:left .7s}
        .btn-shine:hover::after{left:130%}
        .link-u{background:linear-gradient(currentColor,currentColor) left bottom/0 2px no-repeat;transition:background-size .3s}
        .link-u:hover{background-size:100% 2px}
        /* tombol melayang */
        #wa-float{position:fixed;right:max(1.25rem,env(safe-area-inset-right));bottom:max(1.25rem,env(safe-area-inset-bottom));z-index:60}
        #wa-float .ring{position:absolute;inset:0;border-radius:50%;background:#25D366;animation:ring 2.2s ease-out infinite}
        #wa-float .tip{position:absolute;right:4.2rem;top:50%;margin-top:-1.1rem;white-space:nowrap;background:#fff;color:#1e293b;font-size:.78rem;font-weight:600;padding:.45rem .8rem;border-radius:.7rem;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:tip 9s ease-in-out 3s 1;opacity:0;pointer-events:none}
        #wa-float:hover .tip{opacity:1;animation:none;transform:none}
        #to-top{position:fixed;right:1.55rem;bottom:5.6rem;z-index:55;opacity:0;visibility:hidden;transform:translateY(12px);transition:all .3s}
        #to-top.show{opacity:1;visibility:visible;transform:none}
        @media (prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{animation:none!important;transition:none!important}.js [data-reveal]{opacity:1;transform:none}}
    </style>
</head>
<body class="font-sans text-gray-700 antialiased bg-white">

<header id="site-header" class="fixed top-0 inset-x-0 z-50 bg-[#0B3D91] shadow-md">
    <div class="hbar max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-3 group">
            @if ($logo)
                <img src="{{ $logo }}" alt="Logo {{ $company }}" class="h-10 w-10 rounded bg-white p-0.5 object-contain transition group-hover:scale-110 group-hover:rotate-3">
            @endif
            <span class="font-head font-extrabold text-white text-sm sm:text-base tracking-wide">PWL <span class="text-[#4A90D9]">KONSULTAN</span></span>
        </a>

        <nav class="hidden lg:flex items-center gap-1">
            @foreach ($menu as $m)
                @if (! empty($m['children']))
                    <div class="relative group">
                        <a href="{{ route($m['route']) }}"
                           class="font-head px-3 xl:px-4 py-2 rounded text-sm font-semibold inline-flex items-center gap-1 transition {{ request()->routeIs($m['route']) ? $on : $off }}">
                            {{ $m['label'] }}
                            <x-icon name="chevron" class="w-3.5 h-3.5 transition-transform group-hover:rotate-180" />
                        </a>
                        <div class="absolute left-0 top-full pt-2 min-w-[13rem] invisible opacity-0 translate-y-2 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:visible group-focus-within:opacity-100 group-focus-within:translate-y-0 transition duration-200">
                            <div class="bg-white rounded-xl shadow-2xl border border-gray-100 py-2 overflow-hidden">
                                @foreach ($m['children'] as [$cl, $cu])
                                    <a href="{{ $cu }}" class="font-head block px-4 py-2.5 text-sm font-semibold text-[#0B3D91] hover:bg-blue-50 hover:pl-6 hover:text-[#4A90D9] transition-all">{{ $cl }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route($m['route']) }}"
                       class="font-head px-3 xl:px-4 py-2 rounded text-sm font-semibold transition {{ request()->routeIs($m['route']) ? $on : $off }}">{{ $m['label'] }}</a>
                @endif
            @endforeach
        </nav>

        <button type="button" id="menu-btn" class="lg:hidden text-white p-2" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <nav id="menu-mobile" class="hidden lg:hidden border-t border-white/10 bg-[#0B3D91] px-4 py-3 space-y-1">
        @foreach ($menu as $m)
            <a href="{{ route($m['route']) }}" class="font-head block px-3 py-2 rounded text-sm font-semibold {{ request()->routeIs($m['route']) ? $on : 'text-blue-100' }}">{{ $m['label'] }}</a>
            @foreach ($m['children'] ?? [] as [$cl, $cu])
                @if (! $loop->first)
                    <a href="{{ $cu }}" class="font-head block pl-8 pr-3 py-1.5 text-sm text-blue-200">&rsaquo; {{ $cl }}</a>
                @endif
            @endforeach
        @endforeach
    </nav>
</header>

<main>
    {{ $slot }}
</main>

<footer class="relative overflow-hidden bg-[#082d6e] text-blue-100">
    <span class="shape w-64 h-64 -top-24 -right-16" style="opacity:.05"></span>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid md:grid-cols-3 gap-10">
        <div data-reveal>
            <div class="flex items-center gap-3 mb-3">
                @if ($logo)<img src="{{ $logo }}" alt="" class="h-11 w-11 rounded bg-white p-0.5 object-contain">@endif
                <div class="font-head font-extrabold text-white text-lg leading-tight">{{ $company }}</div>
            </div>
            <p class="text-sm leading-relaxed text-blue-200">{{ \Illuminate\Support\Str::limit($c['about'] ?? '', 280) }}</p>
        </div>
        <div data-reveal data-delay="100">
            <div class="font-head font-bold text-white mb-3">Menu</div>
            <ul class="space-y-2 text-sm">
                @foreach ($menu as $m)
                    <li><a href="{{ route($m['route']) }}" class="inline-block hover:text-white hover:translate-x-1 transition">{{ $m['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
        <div data-reveal data-delay="200">
            <div class="font-head font-bold text-white mb-3">Kontak</div>
            <ul class="space-y-3 text-sm">
                <li class="flex gap-3"><x-icon name="pin" class="w-5 h-5 shrink-0 text-[#4A90D9]" /><span>{{ $c['address'] ?? '' }}</span></li>
                <li class="flex gap-3"><x-icon name="phone" class="w-5 h-5 shrink-0 text-[#4A90D9]" /><span>{{ $c['phone'] ?? '' }}</span></li>
                <li class="flex gap-3"><x-icon name="mail" class="w-5 h-5 shrink-0 text-[#4A90D9]" /><span>{{ $c['email'] ?? '' }}</span></li>
                <li><a href="{{ $waLink }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 mt-1 bg-[#25D366] hover:bg-[#1ebe5a] text-white font-semibold rounded-full px-4 py-2 text-xs transition hover:scale-105"><x-wa-icon class="w-4 h-4" /> Chat WhatsApp</a></li>
            </ul>
        </div>
    </div>
    <div class="relative border-t border-white/10 py-5 text-center text-xs text-blue-300">
        &copy; {{ date('Y') }} {{ $company }}. Hak cipta dilindungi.
    </div>
</footer>

<x-download-modal />

{{-- tombol melayang --}}
<button type="button" id="to-top" class="w-11 h-11 rounded-full bg-[#0B3D91] text-white shadow-lg flex items-center justify-center hover:bg-[#4A90D9]" aria-label="Kembali ke atas"><x-icon name="up" class="w-5 h-5" /></button>
<a id="wa-float" href="{{ $waLink }}" target="_blank" rel="noopener" aria-label="Chat via WhatsApp">
    <span class="tip">Ada pertanyaan? Chat kami di WhatsApp</span>
    <span class="ring"></span>
    <span class="relative flex items-center justify-center w-14 h-14 rounded-full bg-[#25D366] text-white shadow-xl transition hover:scale-110"><x-wa-icon class="w-8 h-8" /></span>
</a>

<script>
(function () {
    var d = document;
    d.getElementById('menu-btn').addEventListener('click', function () {
        d.getElementById('menu-mobile').classList.toggle('hidden');
    });

    /* navbar berubah saat digulir + tombol kembali ke atas */
    var header = d.getElementById('site-header'), top = d.getElementById('to-top');
    function onScroll() {
        var y = window.scrollY || 0;
        header.classList.toggle('scrolled', y > 20);
        top.classList.toggle('show', y > 500);
    }
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    /* muncul perlahan saat terlihat di layar */
    var items = [].slice.call(d.querySelectorAll('[data-reveal]'));
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (e) { e.classList.add('in'); });
    } else {
        var io = new IntersectionObserver(function (list) {
            list.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        items.forEach(function (e) {
            var dl = e.getAttribute('data-delay'); if (dl) e.style.transitionDelay = dl + 'ms';
            io.observe(e);
        });
    }

    /* angka naik */
    function run(el) {
        var to = parseInt(el.getAttribute('data-countup'), 10) || 0, suf = el.getAttribute('data-suffix') || '', t0 = null;
        function step(ts) {
            t0 = t0 || ts; var p = Math.min((ts - t0) / 1400, 1);
            el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))) + suf;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    var counters = [].slice.call(d.querySelectorAll('[data-countup]'));
    if (!('IntersectionObserver' in window)) {
        counters.forEach(function (e) { e.textContent = e.getAttribute('data-countup') + (e.getAttribute('data-suffix') || ''); });
    } else {
        var co = new IntersectionObserver(function (list) {
            list.forEach(function (x) { if (x.isIntersecting) { run(x.target); co.unobserve(x.target); } });
        }, { threshold: 0.4 });
        counters.forEach(function (e) { co.observe(e); });
    }
})();
</script>
</body>
</html>
