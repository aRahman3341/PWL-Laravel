@props(['title', 'subtitle' => ''])
<section class="relative overflow-hidden pt-32 pb-20 bg-gradient-to-br from-[#0B3D91] via-[#12439a] to-[#2f6fc6] text-white">
    <span class="shape w-44 h-44 -top-12 left-[6%]"></span>
    <span class="shape w-28 h-28 top-24 right-[10%]" style="animation-delay:-3s"></span>
    <span class="shape w-20 h-20 bottom-6 left-[40%]" style="animation-delay:-6s;border-radius:50%"></span>
    <div class="absolute inset-0 opacity-[.08]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:24px 24px"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-xs text-blue-200 mb-4 flex items-center gap-2" data-reveal>
            <a href="{{ route('beranda') }}" class="hover:text-white">Beranda</a><span>/</span><span class="text-white font-semibold">{{ $title }}</span>
        </nav>
        <h1 class="font-head text-3xl sm:text-5xl font-extrabold" data-reveal data-delay="80">{{ $title }}</h1>
        <div class="w-16 h-1 bg-[#4A90D9] rounded mt-5" data-reveal="zoom" data-delay="160"></div>
        @if ($subtitle)
            <p class="mt-5 text-blue-100 text-lg max-w-2xl" data-reveal data-delay="220">{{ $subtitle }}</p>
        @endif
    </div>
</section>
