<x-layouts.app title="Tinjau Permintaan Download">
@php $hasil = session('hasil'); $st = $req['status']; @endphp
<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="max-w-xl mx-auto px-4">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8" data-reveal="zoom">
            <div class="w-12 h-12 rounded-2xl bg-[#0B3D91]/10 text-[#0B3D91] flex items-center justify-center mb-4"><x-icon name="shield" class="w-6 h-6" /></div>
            <h1 class="font-head text-2xl font-extrabold text-[#0B3D91] mb-1">Tinjau permintaan download</h1>
            <p class="text-sm text-gray-500 mb-6">Halaman ini hanya untuk tim {{ $c['name'] ?? 'perusahaan' }}.</p>

            @if ($hasil)
                @if ($hasil['ok'] && $hasil['act'] === 'setujui')
                    <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 p-4 text-green-800">
                        <div class="font-bold">Disetujui</div>
                        <div class="text-sm mt-1">Kode otomatis muncul di layar pemohon (selama halaman mereka masih terbuka).</div>
                        <div class="mt-3 text-xs text-green-700">Kode untuk pemohon (berlaku 1 jam, hanya di browser pemohon):</div>
                        <div class="font-mono text-3xl font-extrabold tracking-[.3em] mt-1">{{ $hasil['code'] }}</div>
                    </div>
                @elseif ($hasil['ok'])
                    <div class="mb-6 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-slate-700 font-semibold">Permintaan ditolak.</div>
                @else
                    <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700 text-sm">{{ $hasil['error'] ?? 'Gagal memproses.' }}</div>
                @endif
            @endif

            <dl class="text-sm divide-y divide-gray-100 border border-gray-100 rounded-2xl overflow-hidden">
                <div class="flex gap-4 p-3"><dt class="w-32 text-gray-400 shrink-0">File</dt><dd class="font-semibold text-slate-800">{{ collect($files)->pluck('title')->join(', ') ?: '(file sudah dihapus)' }}</dd></div>
                <div class="flex gap-4 p-3"><dt class="w-32 text-gray-400 shrink-0">Nama pemohon</dt><dd class="text-slate-800">{{ $req['requester_name'] }}</dd></div>
                <div class="flex gap-4 p-3"><dt class="w-32 text-gray-400 shrink-0">Kebutuhan</dt><dd class="text-slate-800 whitespace-pre-line">{{ $req['purpose'] }}</dd></div>
                <div class="flex gap-4 p-3"><dt class="w-32 text-gray-400 shrink-0">Waktu</dt><dd class="text-slate-800">{{ \Illuminate\Support\Carbon::parse($req['created_at'])->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</dd></div>
                <div class="flex gap-4 p-3"><dt class="w-32 text-gray-400 shrink-0">Status</dt>
                    <dd><span class="inline-block rounded-full px-3 py-0.5 text-xs font-bold {{ ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800', 'expired' => 'bg-slate-100 text-slate-600'][$st] ?? 'bg-slate-100' }}">{{ ['pending' => 'Menunggu keputusan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa'][$st] ?? $st }}</span></dd></div>
            </dl>

            @if ($st === 'pending' && $files)
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <form method="POST" action="{{ $urls['tolak'] }}">@csrf
                        <button class="w-full border-2 border-red-200 text-red-600 hover:bg-red-50 font-bold rounded-xl py-3 text-sm transition active:scale-95">Tolak</button></form>
                    <form method="POST" action="{{ $urls['setujui'] }}">@csrf
                        <button class="btn-shine w-full bg-[#0B3D91] hover:bg-[#082d6e] text-white font-bold rounded-xl py-3 text-sm transition active:scale-95">Setujui</button></form>
                </div>
                <p class="text-xs text-gray-400 mt-4 text-center">Jika tidak mengenal pemohon, pilih Tolak.</p>
            @endif
        </div>
    </div>
</section>
</x-layouts.app>
