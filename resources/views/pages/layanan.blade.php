@php
    // $k: null = ringkas (4 per kategori) | konstruksi | non-konstruksi | semua = tampilkan semuanya
    $full  = $k !== null;
    $limit = 4;
    $tabs  = [null => 'Ringkasan', 'konstruksi' => 'Konstruksi', 'non-konstruksi' => 'Non Konstruksi', 'semua' => 'Semua Layanan'];
@endphp
<x-layouts.app title="Layanan">
<x-page-hero title="Layanan Kami" subtitle="Solusi konsultansi terpadu untuk kebutuhan proyek konstruksi dan non konstruksi Anda." />

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2 mb-14" data-reveal>
            @foreach ($tabs as $key => $label)
                <a href="{{ $key ? route('layanan', ['k' => $key]) : route('layanan') }}"
                   class="font-head px-5 py-2 rounded-full text-sm font-bold transition hover:-translate-y-0.5 {{ ($k ?? null) === $key ? 'bg-[#0B3D91] text-white shadow-lg' : 'bg-gray-100 text-[#0B3D91] hover:bg-blue-100' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if (! $svc)
            <p class="text-gray-500">Data layanan belum tersedia.</p>
        @endif

        <div class="space-y-24">
        @foreach ($svc as $cat => $items)
            @php $slug = \Illuminate\Support\Str::slug($cat); $shown = $full ? $items : array_slice($items, 0, $limit); @endphp
            <div id="{{ $slug }}" class="scroll-mt-28">
                <div class="flex items-center gap-4 mb-10" data-reveal>
                    <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] text-white flex items-center justify-center shadow-lg"><x-icon :name="$slug === 'konstruksi' ? 'building' : 'layers'" class="w-7 h-7" /></span>
                    <div>
                        <div class="font-head text-xs font-bold tracking-widest text-[#4A90D9] uppercase mb-1">Layanan</div>
                        <h2 class="font-head text-2xl sm:text-3xl font-extrabold text-[#0B3D91]">{{ $cat }}
                            <span class="text-base font-semibold text-gray-400">({{ count($items) }})</span></h2>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 {{ $full ? 'lg:grid-cols-3' : '' }} gap-6">
                    @foreach ($shown as $s)
                        <div data-reveal data-delay="{{ ($loop->index % 3) * 80 }}">
                            @php
                                $svcImg = \App\Services\SitePhotos::serviceUrl($s['id'] ?? null, $cat);
                                $detail = [
                                    't' => $s['title'], 'img' => $svcImg, 'label' => 'Deskripsi layanan',
                                    'chips' => array_values(array_filter([$cat, $s['code'] ?? null, ! empty($s['qualification']) ? 'Kualifikasi ' . $s['qualification'] : null])),
                                    'meta' => [], 'desc' => trim((string) ($s['description'] ?? '')), 'dl' => $files['service:' . ($s['id'] ?? '')] ?? null,
                                ];
                            @endphp
                            <div tabindex="0" role="button" aria-label="Buka detail {{ $s['title'] }}" data-detail="{{ json_encode($detail, JSON_UNESCAPED_UNICODE) }}"
                                 class="group lift cursor-pointer p-6 bg-white border border-gray-100 rounded-2xl h-full relative overflow-hidden focus:outline-none focus-visible:ring-4 focus-visible:ring-[#4A90D9]/40">
                                <span class="absolute left-0 top-0 h-full w-1 bg-[#4A90D9] scale-y-0 group-hover:scale-y-100 origin-top transition-transform duration-300"></span>
                                @php $svcImg = \App\Services\SitePhotos::serviceUrl($s['id'] ?? null, $cat); @endphp
                                @if ($svcImg)
                                    <div class="-mx-6 -mt-6 mb-5 h-44 overflow-hidden bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]">
                                        <img src="{{ $svcImg }}" alt="{{ $s['title'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                    </div>
                                @endif
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-8 h-1 bg-[#4A90D9] rounded transition-all group-hover:w-14"></div>
                                    @if (! empty($s['code']))
                                        <span class="text-xs font-semibold text-[#0B3D91] bg-[#0B3D91]/10 rounded-full px-2.5 py-0.5">{{ $s['code'] }}</span>
                                    @endif
                                </div>
                                <h3 class="font-head font-extrabold text-[#0B3D91] mb-3 leading-snug">{{ $s['title'] }}</h3>
                                                                @if (! empty($s['description']))
                                    <p class="text-gray-500 text-sm leading-relaxed line-clamp-3">{{ $s['description'] }}</p>
                                @endif
                                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[#4A90D9] group-hover:text-[#0B3D91]">Lihat detail <x-icon name="arrow" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" /></span>
                                @if (! empty($files['service:' . ($s['id'] ?? '')]))
                                    <button type="button" data-dl-file="{{ $files['service:' . $s['id']] }}" data-dl-title="{{ $s['title'] }}"
                                            class="mt-4 inline-flex items-center gap-2 text-xs font-bold text-[#0B3D91] border border-[#0B3D91]/25 rounded-full px-3.5 py-1.5 hover:bg-[#0B3D91] hover:text-white transition">
                                        <x-icon name="download" class="w-4 h-4" /> Download dokumen
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (! $full && count($items) > $limit)
                    <div class="mt-8" data-reveal>
                        <a href="{{ route('layanan', ['k' => $slug]) }}" class="link-u font-head inline-flex items-center gap-2 text-[#0B3D91] font-bold text-sm pb-0.5 hover:text-[#4A90D9]">
                            Lihat semua layanan {{ $cat }} ({{ count($items) }}) <x-icon name="arrow" class="w-4 h-4" />
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
        </div>

        <div class="text-center mt-16" data-reveal>
            @if (! $full)
                <a href="{{ route('layanan', ['k' => 'semua']) }}" class="btn-shine font-head inline-flex items-center gap-2 px-8 py-3.5 bg-[#0B3D91] text-white font-bold rounded-lg hover:bg-[#082d6e] text-sm transition hover:-translate-y-1 hover:shadow-xl">Lihat Semua Layanan <x-icon name="arrow" class="w-4 h-4" /></a>
            @else
                <a href="{{ route('layanan') }}" class="font-head inline-block px-8 py-3.5 border-2 border-[#0B3D91] text-[#0B3D91] font-bold rounded-lg hover:bg-[#0B3D91] hover:text-white text-sm transition">Kembali ke ringkasan layanan</a>
            @endif
        </div>
    </div>
</section>
<x-detail-modal />
</x-layouts.app>
