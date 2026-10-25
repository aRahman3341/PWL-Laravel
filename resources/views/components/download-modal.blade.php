{{-- Pop-up Download: pilih 1 atau lebih dokumen -> minta persetujuan (atau kode manual) -> kode 6 angka -> unduh -> kembali ke menu awal --}}
@php
    $dlUrls = [
        'list' => route('dl.daftar'), 'request' => route('dl.minta'), 'manual' => route('dl.manual'), 'status' => route('dl.status'),
        'verify' => route('dl.verifikasi'), 'cancel' => route('dl.batal'), 'finish' => route('dl.selesai'),
    ];
    $dlInput = 'w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B3D91] focus:ring-4 focus:ring-[#0B3D91]/10';
    $dlLabel = 'font-head block text-xs font-bold text-[#0B3D91] uppercase tracking-wider mb-1.5';
    $dlBtn = 'btn-shine font-head w-full inline-flex items-center justify-center gap-2 bg-[#0B3D91] hover:bg-[#082d6e] text-white font-bold rounded-xl py-3 text-sm transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed';
@endphp
<div id="dl-modal" data-urls="{{ json_encode($dlUrls) }}"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition duration-300 invisible opacity-0" role="dialog" aria-modal="true" aria-labelledby="dl-title">
    <div data-panel class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto transition duration-300 scale-95 translate-y-4">
        <button type="button" data-dl-close class="absolute top-4 right-4 p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100" aria-label="Tutup"><x-icon name="x" class="w-5 h-5" /></button>
        <div class="p-7">
            <div class="w-12 h-12 rounded-2xl bg-[#0B3D91]/10 text-[#0B3D91] flex items-center justify-center mb-4"><x-icon name="download" class="w-6 h-6" /></div>
            <h3 id="dl-title" class="font-head text-xl font-extrabold text-[#0B3D91]">Download Dokumen</h3>

            {{-- 1. pilih dokumen (boleh lebih dari satu) --}}
            <div data-view="list" class="hidden">
                <p class="text-sm text-gray-500 mt-1">Pilih satu atau beberapa dokumen yang ingin diunduh.</p>
                <ul id="dl-list" class="mt-5 space-y-3"></ul>
                <button type="button" id="dl-next" class="{{ $dlBtn }} mt-5" disabled>Lanjut</button>
            </div>

            {{-- 2. isi nama + kebutuhan (atau kode manual) --}}
            <div data-view="form" class="hidden">
                <p class="text-sm text-gray-500 mt-1">Dokumen yang dipilih:</p>
                <ul id="dl-ftitles" class="mt-2 text-sm font-semibold text-slate-700 space-y-1"></ul>
                <form id="dl-form" class="mt-5 space-y-4" novalidate>
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
                    <div><label for="dl-name" class="{{ $dlLabel }}">Nama</label>
                        <input id="dl-name" name="name" maxlength="100" placeholder="Nama lengkap / instansi" class="{{ $dlInput }}"></div>
                    <div id="dl-purposebox"><label for="dl-purpose" class="{{ $dlLabel }}">Kebutuhan</label>
                        <textarea id="dl-purpose" name="purpose" rows="3" maxlength="500" placeholder="Contoh: untuk referensi pengadaan konsultan di dinas kami" class="{{ $dlInput }} resize-none"></textarea></div>
                    <button type="button" id="dl-manual-toggle" class="text-xs text-[#0B3D91] underline">Punya kode manual dari kami?</button>
                    <div id="dl-manualbox" class="hidden">
                        <label for="dl-manual" class="{{ $dlLabel }}">Kode manual</label>
                        <input id="dl-manual" name="manual" maxlength="30" autocomplete="off" placeholder="XXXX-XXXX-XXXX" class="{{ $dlInput }} font-mono tracking-wider uppercase">
                        <p class="text-[11px] text-gray-400 mt-1">Isi kolom Nama persis seperti nama yang kami daftarkan untuk kode ini.</p>
                    </div>
                    <div id="dl-ferr" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                    <button id="dl-send" class="{{ $dlBtn }}"><x-icon name="send" class="w-4 h-4" /> <span>Kirim permintaan</span></button>
                    <button type="button" id="dl-back" class="w-full text-xs text-gray-400 hover:text-[#0B3D91] underline">Ganti pilihan dokumen</button>
                </form>
            </div>

            {{-- 3. menunggu persetujuan --}}
            <div data-view="wait" class="hidden text-center">
                <div class="mx-auto mt-6 w-14 h-14 rounded-full border-4 border-[#0B3D91]/15 border-t-[#0B3D91] animate-spin"></div>
                <p class="mt-5 font-semibold text-slate-800">Permintaan terkirim</p>
                <p class="text-sm text-gray-500 mt-1">Menunggu persetujuan tim kami untuk:</p>
                <ul data-titles class="mt-1 text-sm font-semibold text-slate-700 space-y-0.5"></ul>
                <p class="text-xs text-gray-400 mt-4">Anda boleh menutup jendela ini. Saat disetujui, kode 6 angka muncul sebagai notifikasi di pojok layar selama 15 detik. Jangan tutup halaman website ini.</p>
                <button type="button" data-dl-cancel class="mt-4 text-xs text-gray-400 hover:text-red-600 underline">Batalkan permintaan</button>
            </div>

            {{-- 4. masukkan kode --}}
            <div data-view="code" class="hidden">
                <p class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl px-3 py-2 mt-3 flex items-start gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 shrink-0" /> <span>Disetujui! Masukkan kode 6 angka untuk membuka file.</span></p>
                <ul data-titles class="mt-3 text-sm font-semibold text-slate-700 space-y-0.5"></ul>
                <form id="dl-codeform" class="mt-4" novalidate>
                    <label for="dl-code" class="{{ $dlLabel }}">Kode 6 angka</label>
                    <input id="dl-code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" placeholder="000000" class="{{ $dlInput }} font-mono text-2xl tracking-[.5em] text-center">
                    <div id="dl-cerr" class="hidden mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                    <button id="dl-unlock" class="{{ $dlBtn }} mt-4"><x-icon name="check" class="w-4 h-4" /> Buka file</button>
                </form>
                <button type="button" id="dl-again" class="mt-3 w-full text-xs text-gray-400 hover:text-[#0B3D91] underline">Tampilkan kode lagi (15 detik)</button>
                <button type="button" data-dl-cancel class="mt-1 w-full text-xs text-gray-400 hover:text-red-600 underline">Batalkan permintaan</button>
                <p class="text-[11px] text-gray-400 mt-2 text-center">Kode berlaku 1 jam dan hanya di browser ini.</p>
            </div>

            {{-- 5. daftar file yang bisa diunduh --}}
            <div data-view="files" class="hidden">
                <p class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl px-3 py-2 mt-3 flex items-start gap-2"><x-icon name="check" class="w-4 h-4 mt-0.5 shrink-0" /> <span>Berhasil. Klik <strong>Unduh</strong> pada tiap file yang Anda perlukan.</span></p>
                <ul id="dl-files" class="mt-4 space-y-3"></ul>
                <button type="button" id="dl-finish" class="mt-4 w-full text-xs text-gray-500 hover:text-[#0B3D91] underline">Selesai, kembali ke menu awal</button>
                <p class="text-[11px] text-gray-400 mt-1 text-center">Setelah semua file terunduh, jendela ini otomatis kembali ke menu awal.</p>
            </div>

            {{-- 6. selesai --}}
            <div data-view="finished" class="hidden text-center">
                <div class="mx-auto mt-6 w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><x-icon name="check" class="w-7 h-7" /></div>
                <p class="mt-4 font-semibold text-slate-800">Unduhan selesai</p>
                <p class="text-sm text-gray-500 mt-1">Periksa folder unduhan browser Anda. Anda bisa mengunduh dokumen lain kapan saja.</p>
                <button type="button" id="dl-home" class="{{ $dlBtn }} mt-5">Kembali ke menu awal</button>
            </div>

            {{-- 7. ditolak / kedaluwarsa --}}
            <div data-view="end" class="hidden text-center">
                <div class="mx-auto mt-6 w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><x-icon name="x" class="w-7 h-7" /></div>
                <p id="dl-endmsg" class="mt-4 font-semibold text-slate-800"></p>
                <button type="button" id="dl-retry" class="{{ $dlBtn }} mt-5">Kembali ke menu awal</button>
            </div>
        </div>
    </div>
</div>

<div id="dl-toasts" class="fixed top-20 right-4 z-[130] w-[min(22rem,calc(100vw-2rem))] space-y-3 pointer-events-none"></div>
<button type="button" id="dl-chip" class="hidden fixed left-4 bottom-4 z-[65] items-center gap-2 bg-white border border-[#0B3D91]/20 shadow-xl rounded-full pl-3 pr-4 py-2 text-xs font-bold text-[#0B3D91] hover:-translate-y-0.5 transition" style="bottom:max(1rem,env(safe-area-inset-bottom))"></button>

<script>
(function () {
    var M = document.getElementById('dl-modal'); if (!M) return;
    var P = M.querySelector('[data-panel]'), views = [].slice.call(M.querySelectorAll('[data-view]'));
    var U = JSON.parse(M.getAttribute('data-urls'));
    var csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
    var chip = document.getElementById('dl-chip'), toasts = document.getElementById('dl-toasts');
    var S = { sel: [], state: 'none', poll: null, code: '', titles: [], verified: false, files: [], expectFinish: false };

    function $(id) { return document.getElementById(id); }
    function cur() { var v = views.filter(function (x) { return !x.classList.contains('hidden'); })[0]; return v ? v.getAttribute('data-view') : ''; }
    function show(n) { views.forEach(function (v) { v.classList.toggle('hidden', v.getAttribute('data-view') !== n); }); }
    function isOpen() { return !M.classList.contains('invisible'); }
    function open() { M.classList.remove('invisible', 'opacity-0'); P.classList.remove('scale-95', 'translate-y-4'); document.body.style.overflow = 'hidden'; }
    function close() { M.classList.add('invisible', 'opacity-0'); P.classList.add('scale-95', 'translate-y-4'); document.body.style.overflow = ''; }
    function showErr(el, msg) { el.textContent = msg; el.classList.toggle('hidden', !msg); }
    function fillTitles(list) {
        M.querySelectorAll('[data-titles]').forEach(function (ul) {
            ul.innerHTML = ''; (list || []).forEach(function (t) { var li = document.createElement('li'); li.textContent = t; ul.appendChild(li); });
        });
    }

    function api(url, body) {
        var h = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf };
        if (body) h['Content-Type'] = 'application/json';
        return fetch(url, { method: body ? 'POST' : 'GET', headers: h, credentials: 'same-origin', body: body ? JSON.stringify(body) : undefined })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, data: j }; }, function () { return { ok: r.ok, status: r.status, data: {} }; }); });
    }
    function errText(res, fb) { var d = res.data || {}; if (d.errors) { return d.errors[Object.keys(d.errors)[0]][0]; } return d.message || fb; }

    /* ---------- notifikasi (tidak menutupi layar) ---------- */
    function fade(t, ms) { setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = 0; setTimeout(function () { t.remove(); }, 320); }, ms); }
    function toastCode(code) {
        if (!code) return;
        var t = document.createElement('div');
        t.className = 'pointer-events-auto relative overflow-hidden rounded-2xl bg-white shadow-2xl border border-emerald-200';
        t.style.animation = 'toastIn .35s ease';
        t.innerHTML = '<div class="p-4"><div class="flex items-start justify-between gap-3"><div>' +
            '<div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Permintaan disetujui</div>' +
            '<div class="text-xs text-gray-500 mt-0.5">Kode verifikasi Anda (berlaku 1 jam):</div></div>' +
            '<button type="button" data-x class="text-gray-300 hover:text-gray-600" aria-label="Tutup">&times;</button></div>' +
            '<div class="mt-2 flex items-center justify-between gap-3"><span class="font-mono text-3xl font-extrabold tracking-[.25em] text-[#0B3D91]"></span>' +
            '<button type="button" data-copy class="text-xs font-bold text-[#0B3D91] border border-[#0B3D91]/30 rounded-lg px-3 py-1.5 hover:bg-[#0B3D91] hover:text-white transition">Salin</button></div></div>' +
            '<div class="h-1 bg-emerald-100"><div data-bar class="h-full bg-emerald-500" style="width:100%;transition:width 15s linear"></div></div>';
        t.querySelector('.font-mono').textContent = code;
        toasts.appendChild(t);
        var bar = t.querySelector('[data-bar]');
        requestAnimationFrame(function () { requestAnimationFrame(function () { bar.style.width = '0%'; }); });
        fade(t, 15000);
        t.querySelector('[data-x]').addEventListener('click', function () { t.remove(); });
        t.querySelector('[data-copy]').addEventListener('click', function (e) { if (navigator.clipboard) navigator.clipboard.writeText(code).then(function () { e.target.textContent = 'Tersalin'; }); });
    }
    function toastInfo(msg, bad) {
        var t = document.createElement('div');
        t.className = 'pointer-events-auto rounded-2xl bg-white shadow-2xl border p-4 text-sm ' + (bad ? 'border-red-200 text-red-700' : 'border-gray-200 text-gray-700');
        t.style.animation = 'toastIn .35s ease'; t.textContent = msg; toasts.appendChild(t); fade(t, 6000);
    }

    /* ---------- penanda pojok ---------- */
    function renderChip() {
        var txt = S.state === 'pending' ? 'Menunggu persetujuan...' : (S.state === 'approved' ? (S.verified ? 'Unduh file Anda' : 'Kode siap - masukkan kode') : '');
        chip.style.display = txt ? 'inline-flex' : 'none';
        chip.classList.toggle('hidden', !txt);
        chip.innerHTML = txt ? '<span class="w-2.5 h-2.5 rounded-full ' + (S.state === 'pending' ? 'bg-amber-400 animate-pulse' : 'bg-emerald-500') + '"></span><span></span>' : '';
        if (txt) chip.lastChild.textContent = txt;
    }

    /* ---------- daftar file siap unduh ---------- */
    function renderFiles() {
        var ul = $('dl-files'); ul.innerHTML = '';
        S.files.forEach(function (f) {
            var li = document.createElement('li'); li.className = 'flex items-center gap-3 p-3 rounded-2xl border border-gray-200';
            li.innerHTML = '<span class="w-10 h-10 rounded-xl bg-blue-50 text-[#0B3D91] flex items-center justify-center shrink-0 text-[10px] font-bold">FILE</span>' +
                '<span class="flex-1 min-w-0"><span data-t class="block text-sm font-semibold text-slate-800 leading-snug"></span><span data-s class="block text-[11px] text-gray-400"></span></span>' +
                '<a data-dlink class="shrink-0 inline-flex items-center gap-1.5 bg-[#0B3D91] hover:bg-[#082d6e] text-white text-xs font-bold rounded-full px-4 py-2 transition hover:scale-105">Unduh</a>';
            li.querySelector('[data-t]').textContent = f.title;
            li.querySelector('[data-s]').textContent = f.size ? (f.size / 1048576).toFixed(1) + ' MB' : '';
            var a = li.querySelector('[data-dlink]'); a.href = f.url; a.setAttribute('data-id', f.id);
            if (f.done) markDone(a);
            ul.appendChild(li);
        });
    }
    function markDone(a) { a.textContent = 'Terunduh'; a.classList.remove('bg-[#0B3D91]', 'hover:bg-[#082d6e]'); a.classList.add('bg-green-600'); a.setAttribute('data-done', '1'); }

    /* ---------- status dari server ---------- */
    function apply(d) {
        var st = d.state || 'none', prev = S.state;
        S.state = st; if (d.titles) S.titles = d.titles; S.verified = !!d.verified;
        if (st === 'approved' && d.code) S.code = d.code;
        if (d.files) S.files = d.files;
        renderChip(); fillTitles(S.titles);

        if (st === 'approved') {
            if (S.verified) { renderFiles(); if (isOpen() && cur() !== 'files') show('files'); }
            else if (prev === 'pending') { toastCode(S.code); if (isOpen()) show('code'); }
            stopPoll();
        } else if (st === 'rejected' || st === 'expired') {
            if (prev === 'pending' || prev === 'approved') {
                toastInfo(st === 'rejected' ? 'Permintaan Anda belum dapat disetujui.' : 'Permintaan sudah kedaluwarsa. Silakan ajukan lagi.', true);
                $('dl-endmsg').textContent = st === 'rejected' ? 'Permintaan belum dapat disetujui' : 'Permintaan kedaluwarsa';
                if (isOpen()) show('end');
            }
            stopPoll();
        } else {
            if (prev === 'approved' && S.expectFinish) { S.expectFinish = false; S.files = []; toastInfo('Semua file sudah terunduh.'); if (isOpen()) show('finished'); }
            stopPoll();
        }
    }
    function check() { return api(U.status).then(function (r) { if (r.ok) apply(r.data); }).catch(function () {}); }
    function startPoll() { stopPoll(); S.poll = setInterval(check, 4000); }
    function stopPoll() { if (S.poll) { clearInterval(S.poll); S.poll = null; } }

    /* ---------- navigasi ---------- */
    function viewForState() {
        if (S.state === 'pending') { fillTitles(S.titles); return 'wait'; }
        if (S.state === 'approved') { fillTitles(S.titles); if (S.verified) { renderFiles(); return 'files'; } return 'code'; }
        return null;
    }
    function toForm() {
        showErr($('dl-ferr'), ''); $('dl-manualbox').classList.add('hidden'); $('dl-manual').value = ''; updSend();
        var ul = $('dl-ftitles'); ul.innerHTML = ''; S.sel.forEach(function (f) { var li = document.createElement('li'); li.textContent = '• ' + f.title; ul.appendChild(li); });
        show('form'); setTimeout(function () { $('dl-name').focus(); }, 200);
    }
    function home() { S.sel = []; S.expectFinish = false; show('list'); loadList(); }
    function openFor(o) {
        o = o || {}; showErr($('dl-cerr'), '');
        var v = viewForState(); if (v) { show(v); open(); return; }
        if (o.fileId) { S.sel = [{ id: o.fileId, title: o.title || 'Dokumen' }]; toForm(); open(); return; }
        home(); open();
    }
    function updNext() {
        var n = S.sel.length, b = $('dl-next'); b.disabled = !n; b.textContent = n ? 'Lanjut (' + n + ' dokumen dipilih)' : 'Pilih dokumen dulu';
    }
    function loadList() {
        var ul = $('dl-list'); ul.innerHTML = '<li class="text-sm text-gray-400 text-center py-4">Memuat daftar...</li>'; updNext();
        api(U.list).then(function (r) {
            ul.innerHTML = '';
            var rows = (r.ok && Array.isArray(r.data)) ? r.data : [];
            if (!rows.length) { ul.innerHTML = '<li class="text-sm text-gray-500 text-center py-4">Belum ada dokumen yang tersedia. Silakan hubungi kami untuk informasi lebih lanjut.</li>'; return; }
            rows.forEach(function (d) {
                var li = document.createElement('li');
                li.innerHTML = '<label class="flex items-center gap-3 p-3 rounded-2xl border border-gray-200 hover:border-[#4A90D9] hover:shadow-md transition cursor-pointer"><input type="checkbox" class="w-5 h-5 accent-[#0B3D91] shrink-0"><span class="flex-1 font-semibold text-sm text-slate-800 leading-snug"></span></label>';
                li.querySelector('span').textContent = d.title;
                li.querySelector('input').addEventListener('change', function (e) {
                    S.sel = S.sel.filter(function (x) { return x.id !== d.id; });
                    if (e.target.checked) S.sel.push({ id: d.id, title: d.title });
                    updNext();
                });
                ul.appendChild(li);
            });
        }).catch(function () { ul.innerHTML = '<li class="text-sm text-red-600 text-center py-4">Gagal memuat daftar. Coba lagi.</li>'; });
    }

    /* ---------- formulir: persetujuan ATAU kode manual ---------- */
    function updSend() {
        var manual = !$('dl-manualbox').classList.contains('hidden') && $('dl-manual').value.trim() !== '';
        $('dl-send').querySelector('span').textContent = manual ? 'Gunakan kode manual' : 'Kirim permintaan';
        $('dl-purposebox').classList.toggle('hidden', manual);
    }
    $('dl-manual-toggle').addEventListener('click', function () { $('dl-manualbox').classList.toggle('hidden'); updSend(); if (!$('dl-manualbox').classList.contains('hidden')) $('dl-manual').focus(); });
    $('dl-manual').addEventListener('input', updSend);
    $('dl-next').addEventListener('click', function () { if (S.sel.length) toForm(); });
    $('dl-back').addEventListener('click', function () { show('list'); loadList(); });

    function sending(on) { var b = $('dl-send'); b.disabled = on; b.classList.toggle('opacity-70', on); }
    $('dl-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var f = e.target, name = f.querySelector('[name=name]').value.trim(), purpose = f.querySelector('[name=purpose]').value.trim(),
            hp = f.querySelector('[name=website]').value, code = f.querySelector('[name=manual]').value.trim(),
            manual = !$('dl-manualbox').classList.contains('hidden') && code !== '', ids = S.sel.map(function (x) { return x.id; });
        if (!name) { showErr($('dl-ferr'), 'Nama wajib diisi.'); return; }
        if (!manual && purpose.length < 5) { showErr($('dl-ferr'), 'Jelaskan kebutuhan Anda (minimal 5 karakter).'); return; }
        showErr($('dl-ferr'), ''); sending(true);
        var req = manual ? api(U.manual, { files: ids, name: name, code: code, purpose: purpose }) : api(U.request, { files: ids, name: name, purpose: purpose, website: hp });
        req.then(function (r) {
            sending(false);
            if (!r.ok) { showErr($('dl-ferr'), errText(r, 'Permintaan gagal dikirim. Coba lagi.')); return; }
            f.reset(); S.titles = S.sel.map(function (x) { return x.title; }); S.expectFinish = false;
            if (manual) { S.state = 'pending'; check().then(function () { if (S.state === 'approved') { toastInfo('Kode manual diterima.'); show('files'); } }); return; }
            S.state = 'pending'; fillTitles(S.titles); renderChip(); show('wait'); startPoll();
        }).catch(function () { sending(false); showErr($('dl-ferr'), 'Koneksi bermasalah. Coba lagi.'); });
    });

    /* ---------- kode 6 angka ---------- */
    $('dl-code').addEventListener('input', function (e) { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6); });
    $('dl-codeform').addEventListener('submit', function (e) {
        e.preventDefault();
        var code = $('dl-code').value.trim(), btn = $('dl-unlock');
        if (code.length !== 6) { showErr($('dl-cerr'), 'Masukkan kode 6 angka.'); return; }
        showErr($('dl-cerr'), ''); btn.disabled = true;
        api(U.verify, { code: code }).then(function (r) {
            btn.disabled = false;
            if (!r.ok) { showErr($('dl-cerr'), errText(r, 'Kode salah.')); if (r.status === 429) { S.state = 'none'; renderChip(); } return; }
            S.verified = true; S.files = r.data.files || []; $('dl-code').value = ''; renderChip(); renderFiles(); show('files');
        }).catch(function () { btn.disabled = false; showErr($('dl-cerr'), 'Koneksi bermasalah. Coba lagi.'); });
    });
    $('dl-again').addEventListener('click', function () { toastCode(S.code); });

    /* ---------- unduh per file: setelah semua terunduh, otomatis kembali ke menu awal ---------- */
    $('dl-files').addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('[data-dlink]') : null; if (!a) return;
        S.expectFinish = true; markDone(a);
        setTimeout(check, 1800); setTimeout(check, 5000);
    });
    function reset(msg) { S.state = 'none'; S.files = []; S.verified = false; S.expectFinish = false; stopPoll(); renderChip(); if (msg) toastInfo(msg); }
    $('dl-finish').addEventListener('click', function () { api(U.finish, {}).then(function () { reset(); show('finished'); }); });
    M.querySelectorAll('[data-dl-cancel]').forEach(function (b) { b.addEventListener('click', function () { api(U.cancel, {}).then(function () { reset('Permintaan dibatalkan.'); home(); }); }); });
    $('dl-home').addEventListener('click', function () { home(); });
    $('dl-retry').addEventListener('click', function () { reset(); home(); });

    /* ---------- pemicu ---------- */
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('[data-dl-file]') : null;
        if (a) { e.preventDefault(); openFor({ fileId: a.getAttribute('data-dl-file'), title: a.getAttribute('data-dl-title') }); return; }
        var b = e.target.closest ? e.target.closest('[data-open-dl]') : null;
        if (b) { e.preventDefault(); openFor(); }
    });
    M.querySelectorAll('[data-dl-close]').forEach(function (b) { b.addEventListener('click', close); });
    M.addEventListener('click', function (e) { if (e.target === M) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    chip.addEventListener('click', function () { var v = viewForState(); if (v) { show(v); open(); } });

    check().then(function () { if (S.state === 'pending') startPoll(); });
    window.PWLDownload = { open: openFor, _state: S };
})();
</script>