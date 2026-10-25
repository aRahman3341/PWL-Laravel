<x-layouts.app title="Dokumen Perusahaan">
<x-page-hero title="Dokumen Perusahaan" subtitle="Akses terbatas. Masukkan kode akses yang diberikan oleh perusahaan." />
<section class="py-16 bg-gray-50">
    <div class="max-w-2xl mx-auto px-4">
        @if (! $granted)
            <div data-reveal="zoom" class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8">
                <span class="w-14 h-14 rounded-2xl bg-[#0B3D91]/10 text-[#0B3D91] flex items-center justify-center mb-5"><x-icon name="lock" class="w-7 h-7" /></span>
                <h2 class="font-head text-xl font-extrabold text-[#0B3D91] mb-1">Masukkan kode akses</h2>
                <p class="text-sm text-gray-500 mb-6">Belum punya kode? Minta lewat tombol WhatsApp di pojok kanan bawah, atau buka banner "Download" di Beranda.</p>
                <form method="POST" action="{{ route('rahasia.verify') }}" class="space-y-4">
                    @csrf
                    @error('code')<div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $message }}</div>@enderror
                    <input name="code" placeholder="XXXX-XXXX-XXXX" required autocomplete="off"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 font-mono tracking-wider uppercase focus:outline-none focus:border-[#0B3D91] focus:ring-4 focus:ring-[#0B3D91]/10">
                    <button class="btn-shine font-head w-full bg-[#0B3D91] hover:bg-[#082d6e] text-white font-bold rounded-xl py-3 transition active:scale-95">Buka dokumen</button>
                </form>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4" data-reveal>Akses Anda berlaku 30 menit. Tautan unduhan hanya aktif sesaat setelah diklik.</p>
            <ul class="space-y-3">
                @forelse ($files as $f)
                    <li data-reveal data-delay="{{ $loop->index * 80 }}">
                        <div class="group lift flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl">
                            <span class="icon-pop w-12 h-12 rounded-xl bg-blue-50 text-[#0B3D91] flex items-center justify-center shrink-0"><x-icon name="file" class="w-6 h-6" /></span>
                            <span class="flex-1 min-w-0 font-semibold text-[#0B3D91]">{{ $f['title'] }}
                                @if (! empty($f['file_size']))<span class="block text-xs font-normal text-gray-400">{{ number_format($f['file_size'] / 1048576, 1) }} MB</span>@endif
                            </span>
                            <a href="{{ route('rahasia.unduh', $f['id']) }}" class="inline-flex items-center gap-2 bg-[#0B3D91] hover:bg-[#082d6e] text-white text-sm font-bold rounded-xl px-4 py-2.5 transition hover:scale-105"><x-icon name="download" class="w-4 h-4" /> Unduh</a>
                        </div>
                    </li>
                @empty
                    <li class="p-6 text-center text-gray-400 bg-white rounded-2xl border">Belum ada dokumen untuk kode ini.</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('rahasia.keluar') }}" class="mt-6">@csrf
                <button class="text-sm underline text-gray-500 hover:text-[#0B3D91]">Keluar</button></form>
        @endif
    </div>
</section>
</x-layouts.app>
