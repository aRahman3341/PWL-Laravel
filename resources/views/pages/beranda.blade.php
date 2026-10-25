@php
    $hero  = \App\Services\SitePhotos::url('hero');
    $about = \App\Services\SitePhotos::url('about');

    $stats = array_merge(
        [['value' => $years . '+', 'label' => 'Tahun Berpengalaman']],
        config('site.stats')
    );

    $cards = [
        [
            'name' => 'Konstruksi', 'anchor' => 'konstruksi', 'icon' => 'building',
            'desc' => 'Perencanaan, pengawasan, dan rekayasa proyek konstruksi dengan standar mutu terbaik dan ketepatan waktu pelaksanaan.',
        ],
        [
            'name' => 'Non Konstruksi', 'anchor' => 'non-konstruksi', 'icon' => 'layers',
            'desc' => 'Konsultansi teknis dan studi meliputi survei, studi kelayakan, telematika, serta kajian teknis dan non-teknis.',
        ],
    ];

    $points = ['Berpengalaman sejak ' . $c['founded_year'], 'Tenaga ahli bersertifikat', 'Anggota asosiasi PERKINDO', 'Konstruksi & non konstruksi'];
@endphp

<x-layouts.app title="Beranda">

{{-- HERO --}}
<section class="hero-sec relative min-h-screen flex items-center overflow-hidden">
    <div class="absolute inset-0 bg-center bg-cover bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]"
         @if ($hero) style="background-image:url('{{ $hero }}')" @endif></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#0B3D91]/95 via-[#0B3D91]/80 to-[#0B3D91]/45"></div>
    <span class="shape w-56 h-56 top-24 right-[8%]"></span>
    <span class="shape w-32 h-32 bottom-28 right-[28%]" style="animation-delay:-3s;border-radius:50%"></span>
    <span class="shape w-24 h-24 top-1/3 left-[4%]" style="animation-delay:-6s"></span>
    <div class="hero-pad relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <div class="max-w-3xl">
            <div data-reveal class="inline-flex items-center gap-2 px-3 py-1 bg-[#4A90D9]/30 border border-[#4A90D9] rounded-full text-[#bfdbfe] text-xs font-semibold tracking-widest uppercase mb-6">
                <span class="w-2 h-2 rounded-full bg-[#4A90D9] animate-pulse"></span> Konsultan Konstruksi &amp; Non Konstruksi
            </div>
            <h1 data-reveal data-delay="100" class="hero-title font-head text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">Mitra Terpercaya dalam Konsultansi <span class="shimmer">Konstruksi &amp; Non Konstruksi</span></h1>
            <p data-reveal data-delay="200" class="text-blue-100 text-lg sm:text-xl mb-10 max-w-2xl leading-relaxed">{{ $c['name'] }} hadir memberikan solusi konsultansi profesional yang terintegrasi untuk mendukung keberhasilan proyek Anda dari perencanaan hingga pelaksanaan.</p>
            <div data-reveal data-delay="300" class="flex flex-wrap gap-4">
                <a href="{{ route('layanan') }}" class="btn-shine font-head inline-flex items-center gap-2 px-7 py-3.5 bg-[#4A90D9] hover:bg-[#357abd] text-white font-bold rounded-lg text-sm transition hover:-translate-y-1 hover:shadow-xl">Lihat Layanan Kami <x-icon name="arrow" class="w-4 h-4" /></a>
                <a href="{{ route('kontak') }}" class="font-head px-7 py-3.5 border-2 border-white text-white hover:bg-white hover:text-[#0B3D91] font-bold rounded-lg text-sm transition hover:-translate-y-1">Hubungi Kami</a>
            </div>
        </div>
    </div>
    <a href="#tentang" class="scroll-hint absolute bottom-8 left-1/2 -translate-x-1/2 text-white/70 hover:text-white" aria-label="Gulir ke bawah">
        <span class="block w-6 h-10 border-2 border-current rounded-full relative"><span class="absolute left-1/2 top-2 w-1 h-2 -ml-0.5 bg-current rounded-full" style="animation:bounceY 1.6s ease-in-out infinite"></span></span>
    </a>
</section>

{{-- TENTANG KAMI --}}
<section id="tentang" class="py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-16 items-center">
        <div data-reveal="left">
            <x-bar />
            <h2 class="font-head text-3xl sm:text-4xl font-extrabold text-[#0B3D91] mb-5">Tentang Kami</h2>
            <p class="text-gray-600 text-lg leading-relaxed mb-8">{{ $c['about'] }}</p>
            <ul class="grid sm:grid-cols-2 gap-3 mb-8">
                @foreach ($points as $pt)
                    <li class="flex items-center gap-3 text-sm font-semibold text-slate-700">
                        <span class="w-6 h-6 rounded-full bg-[#4A90D9]/15 text-[#0B3D91] flex items-center justify-center shrink-0"><x-icon name="check" class="w-3.5 h-3.5" /></span>{{ $pt }}
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('profil') }}" class="link-u font-head inline-flex items-center gap-2 text-[#0B3D91] font-bold text-sm pb-0.5 hover:text-[#4A90D9]">Selengkapnya <x-icon name="arrow" class="w-4 h-4" /></a>
        </div>
        <div data-reveal="right" class="relative">
            <div class="absolute -top-4 -right-4 w-full h-full rounded-2xl border-2 border-[#4A90D9]/40"></div>
            @if ($about)
                <img src="{{ $about }}" alt="Tim konsultan di lapangan" class="relative w-full h-96 object-cover rounded-2xl shadow-2xl">
            @else
                <div class="relative w-full h-96 rounded-2xl shadow-2xl bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]"></div>
            @endif
            <div class="absolute -bottom-6 -left-6 w-28 h-28 bg-[#0B3D91] rounded-2xl flex flex-col items-center justify-center shadow-xl" style="animation:floaty 7s ease-in-out infinite">
                <span class="font-head text-white text-3xl font-extrabold">{{ $years }}+</span>
                <span class="text-blue-200 text-xs text-center leading-tight">Tahun Pengalaman</span>
            </div>
        </div>
    </div>
</section>

{{-- LAYANAN UNGGULAN --}}
<section class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-3xl sm:text-4xl font-extrabold text-[#0B3D91]">Layanan Unggulan</h2>
            <p class="text-gray-500 mt-3">Dua bidang utama yang kami tangani dengan tenaga ahli berpengalaman.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            @foreach ($cards as $card)
                <div data-reveal data-delay="{{ $loop->index * 140 }}">
                        <div class="group lift bg-white rounded-2xl p-8 border border-gray-100 h-full flex flex-col overflow-hidden">
                        @php $catImg = \App\Services\SitePhotos::url($card['anchor'] === 'konstruksi' ? 'default_layanan_konstruksi' : 'default_layanan_nonkonstruksi'); @endphp
                        @if ($catImg)
                            <div class="-mx-8 -mt-8 mb-6 h-44 overflow-hidden"><img src="{{ $catImg }}" alt="{{ $card['name'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"></div>
                        @endif
                        <span class="icon-pop w-14 h-14 rounded-2xl bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] text-white flex items-center justify-center mb-5 shadow-lg"><x-icon :name="$card['icon']" class="w-7 h-7" /></span>
                        <h3 class="font-head text-xl font-extrabold text-[#0B3D91] mb-3">{{ $card['name'] }}</h3>
                        <p class="text-gray-500 mb-5 leading-relaxed">{{ $card['desc'] }}</p>
                        <ul class="space-y-2.5 mb-6 flex-1">
                            @foreach (array_slice($svc[$card['name']] ?? [], 0, 4) as $s)
                                <li class="flex items-start gap-2.5 text-sm text-gray-600 hover:text-[#0B3D91] hover:translate-x-1 transition-all"><x-icon name="check" class="w-4 h-4 mt-0.5 text-[#4A90D9] shrink-0" />{{ $s['title'] }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('layanan', ['k' => $card['anchor']]) }}" class="font-head text-sm font-bold text-[#4A90D9] group-hover:text-[#0B3D91] inline-flex items-center gap-1">Lihat Detail <x-icon name="arrow" class="w-4 h-4 transition-transform group-hover:translate-x-1" /></a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-12" data-reveal>
            <a href="{{ route('layanan', ['k' => 'semua']) }}" class="btn-shine font-head inline-flex items-center gap-2 px-8 py-3.5 bg-[#0B3D91] text-white font-bold rounded-lg hover:bg-[#082d6e] text-sm transition hover:-translate-y-1 hover:shadow-xl">Lihat Semua Layanan <x-icon name="arrow" class="w-4 h-4" /></a>
        </div>
    </div>
</section>

{{-- ANGKA --}}
<section class="relative overflow-hidden py-20 bg-[#0B3D91]">
    <div class="absolute inset-0 opacity-[.08]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:26px 26px"></div>
    <span class="shape w-60 h-60 -top-20 -left-16"></span>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 gap-8">
        @foreach ($stats as $st)
            @php $num = preg_match('/^\d+/', $st['value'], $mm) ? $mm[0] : null; @endphp
            <div class="text-center" data-reveal="zoom" data-delay="{{ $loop->index * 100 }}">
                <div class="font-head text-4xl sm:text-5xl font-extrabold text-white mb-2"
                     @if ($num !== null) data-countup="{{ $num }}" data-suffix="{{ substr($st['value'], strlen($num)) }}" @endif>{{ $st['value'] }}</div>
                <div class="text-blue-200 text-sm">{{ $st['label'] }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- PENGALAMAN TERBARU --}}
@if ($recent)
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-12" data-reveal>
            <div>
                <div class="w-12 h-1 bg-[#4A90D9] mb-5"></div>
                <h2 class="font-head text-3xl sm:text-4xl font-extrabold text-[#0B3D91]">Pengalaman Terbaru</h2>
                <p class="text-gray-500 mt-2">Sebagian proyek yang baru kami selesaikan.</p>
            </div>
            <a href="{{ route('pengalaman') }}" class="link-u font-head inline-flex items-center gap-2 text-[#0B3D91] font-bold text-sm pb-0.5 hover:text-[#4A90D9]">Lihat semua pengalaman <x-icon name="arrow" class="w-4 h-4" /></a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($recent as $p)
                @php $img = \App\Services\SitePhotos::projectUrl($p['id'] ?? null, $p['category'] ?? ''); @endphp
                <div data-reveal data-delay="{{ $loop->index * 100 }}">
                    <article class="group lift bg-white rounded-2xl overflow-hidden border border-gray-100 h-full">
                        <div class="relative h-40 overflow-hidden bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]">
                            @if ($img)<img src="{{ $img }}" alt="{{ $p['title'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">@endif
                            @if (! empty($p['year']))<span class="absolute top-3 right-3 bg-white/90 text-[#0B3D91] text-xs font-bold rounded-full px-2.5 py-1">{{ (int) $p['year'] }}</span>@endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-head font-bold text-[#0B3D91] text-sm leading-snug line-clamp-3">{{ $p['title'] }}</h3>
                            @if (! empty($p['client']))<p class="text-xs text-gray-400 mt-2 line-clamp-1">{{ $p['client'] }}</p>@endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- BANNER DOKUMEN --}}
<x-docs-banner :c="$c" />

{{-- AJAKAN --}}
<section class="pb-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 text-center" data-reveal>
        <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-6"></div>
        <h2 class="font-head text-3xl sm:text-4xl font-extrabold text-[#0B3D91] mb-5">Siap Mewujudkan Proyek Anda?</h2>
        <p class="text-gray-500 text-lg mb-8 max-w-2xl mx-auto">Konsultasikan kebutuhan proyek Anda bersama tim profesional kami. Kami siap memberikan solusi yang efisien dan tepat sasaran.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('kontak') }}" class="btn-shine font-head inline-flex items-center gap-2 px-8 py-4 bg-[#0B3D91] text-white font-bold rounded-lg hover:bg-[#082d6e] text-sm transition hover:-translate-y-1 hover:shadow-xl">Konsultasi Sekarang <x-icon name="arrow" class="w-4 h-4" /></a>
            <a href="{{ \App\Services\Whatsapp::link($c['whatsapp'] ?? '', 'Halo ' . $c['name'] . ', saya ingin berkonsultasi mengenai proyek saya.') }}" target="_blank" rel="noopener" class="font-head inline-flex items-center gap-2 px-8 py-4 bg-[#25D366] text-white font-bold rounded-lg hover:bg-[#1ebe5a] text-sm transition hover:-translate-y-1 hover:shadow-xl"><x-wa-icon class="w-5 h-5" /> Chat WhatsApp</a>
        </div>
    </div>
</section>

</x-layouts.app>
