<x-layouts.app title="Profil Perusahaan">
<x-page-hero title="Profil Perusahaan" :subtitle="$c['name'] . ' — mitra konsultansi terpercaya Anda.'" />

{{-- TENTANG + ANGKA --}}
<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-5 gap-12 items-center">
            <div class="lg:col-span-3" data-reveal="left">
                <x-bar />
                <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91] mb-6">Tentang Kami</h2>
                <p class="text-gray-600 text-lg leading-relaxed">{{ $c['about'] }}</p>
            </div>
            <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                @foreach ($stats as $s)
                    <div data-reveal="zoom" data-delay="{{ $loop->index * 100 }}">
                        <div class="group lift text-center rounded-2xl border border-gray-100 bg-gradient-to-br from-white to-blue-50 p-5 h-full">
                            <div class="font-head text-3xl font-extrabold text-[#0B3D91]" data-countup="{{ $s['value'] }}" data-suffix="{{ $s['suffix'] }}">{{ $s['value'] }}{{ $s['suffix'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- VISI & MISI --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91]">Visi &amp; Misi</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            <div data-reveal="left">
                <div class="group lift relative overflow-hidden h-full rounded-3xl p-8 sm:p-10 bg-gradient-to-br from-[#0B3D91] to-[#2f6fc6] text-white">
                    <span class="shape w-40 h-40 -top-10 -right-10" style="border-radius:50%"></span>
                    <span class="icon-pop relative w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mb-5"><x-icon name="eye" class="w-7 h-7" /></span>
                    <h3 class="relative font-head text-2xl font-extrabold mb-4">Visi</h3>
                    <p class="relative text-blue-50 leading-relaxed">{{ $c['vision'] ?: 'Visi perusahaan belum diisi.' }}</p>
                </div>
            </div>
            <div data-reveal="right">
                <div class="group lift relative overflow-hidden h-full rounded-3xl p-8 sm:p-10 bg-white border border-gray-100">
                    <span class="icon-pop w-14 h-14 rounded-2xl bg-[#0B3D91]/10 text-[#0B3D91] flex items-center justify-center mb-5"><x-icon name="target" class="w-7 h-7" /></span>
                    <h3 class="font-head text-2xl font-extrabold text-[#0B3D91] mb-4">Misi</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $c['mission'] ?: 'Misi perusahaan belum diisi.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- PERJALANAN --}}
<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91]">Perjalanan Kami</h2>
        </div>
        <div class="relative">
            <div class="absolute left-5 md:left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-[#4A90D9] to-[#0B3D91]/20 md:-translate-x-1/2"></div>
            @foreach ($timeline as [$yr, $ttl, $txt])
                @php $left = $loop->odd; @endphp
                <div class="relative mb-10 md:mb-14 md:grid md:grid-cols-2 md:gap-14 pl-14 md:pl-0">
                    <span class="absolute left-5 md:left-1/2 top-5 w-4 h-4 -translate-x-1/2 rounded-full bg-white border-4 border-[#0B3D91] z-10" data-reveal="zoom"></span>
                    <div class="{{ $left ? 'md:text-right' : 'md:col-start-2' }}" data-reveal="{{ $left ? 'left' : 'right' }}">
                        <div class="group lift bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                            <span class="inline-block font-head text-sm font-extrabold text-white bg-[#0B3D91] rounded-full px-3 py-1 mb-3">{{ $yr }}</span>
                            <h3 class="font-head font-bold text-[#0B3D91] mb-1">{{ $ttl }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $txt }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- NILAI --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91]">Nilai Perusahaan</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($values as $v)
                <div data-reveal data-delay="{{ $loop->index * 100 }}">
                    <div class="group lift text-center p-7 bg-white border border-gray-100 rounded-2xl h-full">
                        <span class="icon-pop w-14 h-14 mx-auto mb-4 bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] text-white rounded-2xl flex items-center justify-center shadow-lg"><x-icon :name="$v[2]" class="w-7 h-7" /></span>
                        <h3 class="font-head font-extrabold text-[#0B3D91] mb-2">{{ $v[0] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ $v[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- PENGURUS --}}
@if ($mgmt)
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91]">Manajemen</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($mgmt as $m)
                <div data-reveal data-delay="{{ ($loop->index % 4) * 90 }}">
                    <div class="group lift text-center p-6 border border-gray-100 rounded-2xl bg-white h-full">
                        <span class="icon-pop w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] text-white font-head text-2xl font-extrabold flex items-center justify-center shadow-lg">{{ strtoupper(mb_substr(preg_replace('/^(Ir\.|Dr\.|Drs\.)\s*/i', '', $m['name']), 0, 1)) }}</span>
                        <div class="font-head font-bold text-[#0B3D91] leading-snug">{{ $m['name'] }}</div>
                        <div class="text-xs font-bold text-[#4A90D9] uppercase tracking-wider mt-2">{{ $m['role'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- LEGALITAS --}}
@if ($legal)
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-reveal>
            <div class="w-12 h-1 bg-[#4A90D9] mx-auto mb-5"></div>
            <h2 class="font-head text-2xl sm:text-4xl font-extrabold text-[#0B3D91]">Legalitas &amp; Sertifikasi</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($legal as $l)
                <div data-reveal data-delay="{{ ($loop->index % 3) * 90 }}">
                    <div class="group lift flex items-start gap-4 p-5 bg-white border border-gray-100 rounded-2xl h-full">
                        <span class="icon-pop w-11 h-11 rounded-xl bg-[#0B3D91]/10 text-[#0B3D91] flex items-center justify-center shrink-0"><x-icon name="shield" class="w-5 h-5" /></span>
                        <div class="min-w-0">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">{{ $l['label'] }}</div>
                            <div class="font-semibold text-[#0B3D91] text-sm break-words">{{ $l['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<x-docs-banner :c="$c" />
</x-layouts.app>
