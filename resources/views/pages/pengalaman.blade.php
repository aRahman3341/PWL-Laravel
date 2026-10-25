@php $step = 12; $limit = 6; @endphp
<x-layouts.app title="Pengalaman">
<x-page-hero title="Pengalaman Kami" subtitle="Rekam jejak proyek yang telah kami selesaikan dengan profesional dan tepat waktu." />

<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-10" data-reveal>
            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('pengalaman', ['k' => $key]) }}"
                       class="font-head inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold transition hover:-translate-y-0.5 {{ $key === $f ? 'bg-[#0B3D91] text-white shadow-lg' : 'bg-white text-[#0B3D91] border border-[#0B3D91]/20 hover:border-[#0B3D91]' }}">
                        {{ $label }}
                        <span class="text-xs rounded-full px-2 py-0.5 {{ $key === $f ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
            <p class="text-sm text-gray-500">Menampilkan <strong class="text-[#0B3D91]">{{ count($projects) }}</strong> dari {{ $total }} proyek</p>
        </div>

        @if (! $projects)
            <p class="text-gray-500">Belum ada data proyek untuk kategori ini.</p>
        @endif

        <div id="proj-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($projects as $p)
                @php
                    $cat = $p['category'] ?? '';
                    $img = \App\Services\SitePhotos::projectUrl($p['id'] ?? null, $cat);
                    $hide = $all && $loop->index >= $step;
                @endphp
                <div class="{{ $hide ? 'hidden' : '' }}" @if ($hide) data-more @else data-reveal data-delay="{{ ($loop->index % 3) * 90 }}" @endif>
                    @php
                        $detail = [
                            't' => $p['title'], 'img' => $img, 'label' => 'Catatan deskripsi kegiatan',
                            'chips' => array_values(array_filter([$tabs[$cat] ?? null, ! empty($p['year']) ? (string) (int) $p['year'] : null])),
                            'meta' => array_values(array_filter([
                                ['briefcase', 'Pemberi tugas', $p['client'] ?? ''], ['pin', 'Lokasi', $p['location'] ?? ''], ['layers', 'Bidang', $p['classification_code'] ?? ''],
                            ], fn ($m) => $m[2] !== '')),
                            'desc' => trim((string) ($p['description'] ?? '')), 'dl' => $files['project:' . ($p['id'] ?? '')] ?? null,
                        ];
                    @endphp
                    <article tabindex="0" role="button" aria-label="Buka detail {{ $p['title'] }}" data-detail="{{ json_encode($detail, JSON_UNESCAPED_UNICODE) }}"
                             class="group lift cursor-pointer bg-white rounded-2xl overflow-hidden border border-gray-100 h-full flex flex-col focus:outline-none focus-visible:ring-4 focus-visible:ring-[#4A90D9]/40">
                        <div class="relative h-52 overflow-hidden bg-gradient-to-br from-[#0B3D91] to-[#4A90D9]">
                            @if ($img)
                                <img src="{{ $img }}" alt="{{ $p['title'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                            @else
                                <span class="absolute inset-0 flex items-center justify-center text-white/30"><x-icon name="building" class="w-16 h-16" /></span>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0B3D91]/70 via-transparent to-transparent"></div>
                            <span class="absolute inset-0 flex items-center justify-center bg-[#0B3D91]/0 group-hover:bg-[#0B3D91]/40 transition"><span class="opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition inline-flex items-center gap-2 bg-white text-[#0B3D91] text-xs font-bold rounded-full px-4 py-2 shadow-lg"><x-icon name="eye" class="w-4 h-4" /> Lihat detail</span></span>
                            @if ($cat)
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold text-white backdrop-blur {{ $cat === 'konstruksi' ? 'bg-[#0B3D91]/85' : 'bg-[#4A90D9]/90' }}">{{ $tabs[$cat] ?? '' }}</span>
                            @endif
                            @if (! empty($p['year']))
                                <span class="absolute bottom-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/90 text-[#0B3D91] text-xs font-bold"><x-icon name="calendar" class="w-3.5 h-3.5" />{{ (int) $p['year'] }}</span>
                            @endif
                        </div>
                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="font-head font-extrabold text-[#0B3D91] mb-3 leading-snug line-clamp-3">{{ $p['title'] }}</h3>
                            @if (! empty($p['description']))<p class="text-sm text-gray-500 leading-relaxed mb-3 line-clamp-2">{{ $p['description'] }}</p>@endif
                            <div class="mt-auto space-y-1.5 text-xs text-gray-500">
                                @if (! empty($p['client']))<div class="flex items-start gap-2"><x-icon name="briefcase" class="w-4 h-4 text-[#4A90D9] shrink-0" /><span class="line-clamp-2">{{ $p['client'] }}</span></div>@endif
                                @if (! empty($p['location']))<div class="flex items-start gap-2"><x-icon name="pin" class="w-4 h-4 text-[#4A90D9] shrink-0" /><span class="line-clamp-1">{{ $p['location'] }}</span></div>@endif
                            </div>
                            @if (! empty($files['project:' . ($p['id'] ?? '')]))
                                <button type="button" data-dl-file="{{ $files['project:' . $p['id']] }}" data-dl-title="{{ $p['title'] }}"
                                        class="mt-4 self-start inline-flex items-center gap-2 text-xs font-bold text-[#0B3D91] border border-[#0B3D91]/25 rounded-full px-3.5 py-1.5 hover:bg-[#0B3D91] hover:text-white transition">
                                    <x-icon name="download" class="w-4 h-4" /> Download dokumen
                                </button>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12" data-reveal>
            @if (! $all && $total > $limit)
                <a href="{{ route('pengalaman', ['k' => $f, 'semua' => 1]) }}" class="btn-shine font-head inline-flex items-center gap-2 px-8 py-3.5 bg-[#0B3D91] text-white font-bold rounded-lg hover:bg-[#082d6e] text-sm transition hover:-translate-y-1 hover:shadow-xl">Lihat Semua Pengalaman ({{ $total }}) <x-icon name="arrow" class="w-4 h-4" /></a>
            @endif
            @if ($all && $total > $step)
                <button type="button" id="more-btn" class="btn-shine font-head inline-flex items-center gap-2 px-8 py-3.5 bg-[#0B3D91] text-white font-bold rounded-lg hover:bg-[#082d6e] text-sm transition hover:-translate-y-1 hover:shadow-xl">Tampilkan lebih banyak <x-icon name="chevron" class="w-4 h-4" /></button>
                <p id="more-info" class="text-xs text-gray-400 mt-3"></p>
            @endif
            @if ($all)
                <div class="mt-6"><a href="{{ route('pengalaman', ['k' => $f]) }}" class="text-sm text-gray-500 hover:text-[#0B3D91] underline">Kembali ke ringkasan</a></div>
            @endif
        </div>
    </div>
</section>

<x-docs-banner :c="$c ?? []" />
<x-detail-modal />

@if ($all && $total > $step)
<script>
(function () {
    var btn = document.getElementById('more-btn'), info = document.getElementById('more-info'), step = {{ $step }};
    if (!btn) return;
    function left() { return document.querySelectorAll('#proj-grid [data-more].hidden').length; }
    function upd() {
        var n = left();
        if (info) info.textContent = n ? 'Masih ada ' + n + ' proyek lagi' : '';
        if (!n) btn.style.display = 'none';
    }
    btn.addEventListener('click', function () {
        var els = [].slice.call(document.querySelectorAll('#proj-grid [data-more].hidden')).slice(0, step);
        els.forEach(function (e, i) {
            e.classList.remove('hidden');
            e.style.opacity = 0; e.style.transform = 'translateY(24px)';
            e.style.transition = 'opacity .6s ease ' + (i % 6) * 70 + 'ms, transform .6s ease ' + (i % 6) * 70 + 'ms';
            requestAnimationFrame(function () { requestAnimationFrame(function () { e.style.opacity = 1; e.style.transform = 'none'; }); });
        });
        upd();
    });
    upd();
})();
</script>
@endif
</x-layouts.app>
