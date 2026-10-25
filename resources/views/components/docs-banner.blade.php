@props(['c' => []])
@php $bg = \App\Services\SitePhotos::url('docs'); @endphp
<section class="py-16 bg-white" id="dokumen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-reveal="zoom" class="relative overflow-hidden rounded-3xl shadow-2xl text-center text-white">
            <div class="absolute inset-0 bg-center bg-cover bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]" @if ($bg) style="background-image:url('{{ $bg }}')" @endif></div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#0B3D91]/80 to-[#2f6fc6]/90"></div>
            <span class="shape w-40 h-40 -top-10 -left-10"></span>
            <span class="shape w-32 h-32 -bottom-8 right-10" style="animation-delay:-4s"></span>
            <div class="relative px-6 py-14 sm:py-16">
                <h2 class="font-head text-3xl sm:text-4xl font-extrabold">Ingin Mengenal Jauh Tentang Kami?</h2>
                <p class="mt-3 text-blue-100 text-lg">Unduh dokumen resmi perusahaan kami</p>
                <button type="button" data-open-dl class="btn-shine mt-8 inline-flex items-center gap-2 bg-white text-slate-900 font-head font-bold rounded-2xl px-8 py-3.5 shadow-lg hover:-translate-y-1 hover:shadow-2xl transition">
                    <x-icon name="download" class="w-5 h-5" /> Download
                </button>
            </div>
        </div>
    </div>
</section>
