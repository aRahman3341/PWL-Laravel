@php
    $st  = session('kirim');
    $wa  = \App\Services\Whatsapp::link($c['whatsapp'] ?? '', 'Halo ' . $c['name'] . ', saya ingin berkonsultasi.');
    $info = [
        ['pin',   'Alamat',          $c['address'], 'https://www.google.com/maps?q=' . urlencode($c['address'])],
        ['phone', 'Telepon',         $c['phone'],   'tel:' . preg_replace('/[^\d+]/', '', $c['phone'])],
        ['mail',  'Email',           $c['email'],   'mailto:' . $c['email']],
        ['clock', 'Jam Operasional', config('site.hours'), null],
    ];
    $in = 'peer w-full px-4 pt-6 pb-2 border border-gray-200 rounded-xl text-sm text-gray-700 bg-white transition focus:outline-none focus:border-[#0B3D91] focus:ring-4 focus:ring-[#0B3D91]/10 placeholder-transparent';
    $lb = 'pointer-events-none absolute left-4 top-4 text-sm text-gray-400 origin-left transition-all peer-focus:top-1.5 peer-focus:text-[11px] peer-focus:font-bold peer-focus:text-[#0B3D91] peer-[:not(:placeholder-shown)]:top-1.5 peer-[:not(:placeholder-shown)]:text-[11px] peer-[:not(:placeholder-shown)]:font-bold';
@endphp
<x-layouts.app title="Kontak Kami">
<x-page-hero title="Kontak Kami" subtitle="Kami siap membantu Anda. Hubungi tim kami untuk konsultasi lebih lanjut." />

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- kartu kontak --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 -mt-24 relative z-10 mb-16">
            @foreach ($info as $row)
                <div data-reveal data-delay="{{ $loop->index * 100 }}">
                    @if ($row[3])<a href="{{ $row[3] }}" @if ($row[0] === 'pin') target="_blank" rel="noopener" @endif class="block h-full">@endif
                    <div class="group lift h-full bg-white rounded-2xl border border-gray-100 p-6 shadow-xl">
                        <span class="icon-pop w-12 h-12 rounded-2xl bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] text-white flex items-center justify-center mb-4 shadow-lg"><x-icon :name="$row[0]" class="w-6 h-6" /></span>
                        <div class="text-xs font-bold text-[#4A90D9] uppercase tracking-wider mb-1">{{ $row[1] }}</div>
                        <div class="text-gray-700 text-sm leading-relaxed break-words">{{ $row[2] }}</div>
                    </div>
                    @if ($row[3])</a>@endif
                </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-5 gap-10">
            {{-- formulir --}}
            <div class="lg:col-span-3" data-reveal="left">
                <x-bar />
                <h2 class="font-head text-2xl sm:text-3xl font-extrabold text-[#0B3D91] mb-2">Kirim Pesan</h2>
                <p class="text-gray-500 mb-8">Isi formulir berikut. Tim kami akan membalas secepatnya.</p>

                @if ($st === 'ok')
                    <div class="mb-6 flex items-start gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-semibold" style="animation:pop .4s ease">
                        <x-icon name="check" class="w-5 h-5 shrink-0 mt-0.5" /> Pesan berhasil terkirim! Tim kami akan segera menghubungi Anda.
                    </div>
                @endif
                @if ($st === 'gagal' || $errors->any())
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-semibold">
                        Pesan gagal dikirim. Periksa isian Anda lalu coba lagi.
                        @foreach ($errors->all() as $err)
                            <div class="font-normal mt-1">• {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('kontak.kirim') }}" method="post" class="space-y-4" id="contact-form">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="relative"><input class="{{ $in }}" id="k-name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Nama Lengkap"><label for="k-name" class="{{ $lb }}">Nama Lengkap</label></div>
                        <div class="relative"><input class="{{ $in }}" id="k-email" type="email" name="email" value="{{ old('email') }}" required placeholder="Email"><label for="k-email" class="{{ $lb }}">Email</label></div>
                    </div>
                    <div class="relative"><input class="{{ $in }}" id="k-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" placeholder="No. Telepon"><label for="k-phone" class="{{ $lb }}">No. Telepon (opsional)</label></div>
                    <div class="relative"><textarea class="{{ $in }} resize-none" id="k-body" name="body" rows="5" required maxlength="3000" placeholder="Pesan">{{ old('body') }}</textarea><label for="k-body" class="{{ $lb }}">Pesan</label></div>
                    <button id="k-send" class="btn-shine font-head inline-flex items-center justify-center gap-2 w-full sm:w-auto px-10 py-3.5 bg-[#0B3D91] hover:bg-[#082d6e] text-white font-bold rounded-xl text-sm transition hover:-translate-y-1 hover:shadow-xl active:scale-95"><x-icon name="send" class="w-4 h-4" /> Kirim Pesan</button>
                </form>
            </div>

            {{-- peta + WhatsApp --}}
            <div class="lg:col-span-2 space-y-6" data-reveal="right">
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="group lift block rounded-2xl p-6 text-white bg-gradient-to-br from-[#128C7E] to-[#25D366] relative overflow-hidden">
                    <span class="shape w-32 h-32 -top-8 -right-8" style="border-radius:50%"></span>
                    <span class="relative flex items-center gap-4">
                        <span class="icon-pop w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center shrink-0"><x-wa-icon class="w-8 h-8" /></span>
                        <span><span class="block font-head font-extrabold text-lg">Lebih cepat via WhatsApp</span><span class="block text-sm text-green-50">Klik untuk langsung chat dengan tim kami</span></span>
                    </span>
                </a>
                <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-lg">
                    <iframe class="w-full h-80" loading="lazy" title="Peta lokasi" src="https://www.google.com/maps?q={{ urlencode($c['address']) }}&output=embed"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    var f = document.getElementById('contact-form');
    if (f) f.addEventListener('submit', function () {
        var b = document.getElementById('k-send');
        setTimeout(function () { b.disabled = true; b.classList.add('opacity-70'); b.innerHTML = 'Mengirim...'; }, 0);
    });
</script>
</x-layouts.app>
