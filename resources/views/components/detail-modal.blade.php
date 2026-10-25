{{-- Pop-up detail: kartu Pengalaman / Layanan dibuka di sini. Data dibaca dari atribut data-detail pada kartu. --}}
<div id="dt-modal" class="fixed inset-0 z-[90] flex items-center justify-center p-3 sm:p-6 bg-slate-900/70 backdrop-blur-sm transition duration-300 invisible opacity-0" role="dialog" aria-modal="true" aria-labelledby="dt-title">
    <div data-panel class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto transition duration-300 scale-95 translate-y-4">
        <div class="relative h-56 sm:h-80 bg-gradient-to-br from-[#0B3D91] to-[#4A90D9] overflow-hidden rounded-t-3xl">
            <img id="dt-img" alt="" class="hidden w-full h-full object-cover">
            <span id="dt-ph" class="absolute inset-0 flex items-center justify-center text-white/30"><x-icon name="building" class="w-20 h-20" /></span>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent"></div>
            <button type="button" id="dt-close" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/90 text-slate-700 hover:bg-white flex items-center justify-center shadow" aria-label="Tutup"><x-icon name="x" class="w-5 h-5" /></button>
            <button type="button" id="dt-prev" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 text-[#0B3D91] hover:bg-white hover:scale-110 transition flex items-center justify-center shadow disabled:opacity-30 disabled:hover:scale-100" aria-label="Sebelumnya"><x-icon name="chevron" class="w-5 h-5 rotate-90" /></button>
            <button type="button" id="dt-next" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 text-[#0B3D91] hover:bg-white hover:scale-110 transition flex items-center justify-center shadow disabled:opacity-30 disabled:hover:scale-100" aria-label="Berikutnya"><x-icon name="chevron" class="w-5 h-5 -rotate-90" /></button>
            <span id="dt-count" class="absolute bottom-3 left-4 text-xs font-bold text-white/90 bg-slate-900/40 backdrop-blur rounded-full px-3 py-1"></span>
        </div>
        <div class="p-6 sm:p-8">
            <div id="dt-chips" class="flex flex-wrap gap-2 mb-3"></div>
            <h3 id="dt-title" class="font-head text-xl sm:text-2xl font-extrabold text-[#0B3D91] leading-snug"></h3>
            <ul id="dt-meta" class="mt-4 grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm text-slate-600"></ul>
            <div id="dt-descbox" class="mt-6 pt-6 border-t border-gray-100">
                <div id="dt-desclabel" class="font-head text-xs font-bold text-[#4A90D9] uppercase tracking-wider mb-2"></div>
                <p id="dt-desc" class="text-slate-600 leading-relaxed whitespace-pre-line"></p>
            </div>
            <div class="mt-7 flex flex-wrap gap-3">
                <button type="button" id="dt-dl" class="hidden btn-shine font-head inline-flex items-center gap-2 bg-[#0B3D91] hover:bg-[#082d6e] text-white font-bold rounded-xl px-6 py-3 text-sm transition active:scale-95"><x-icon name="download" class="w-4 h-4" /> Download dokumen</button>
                <a id="dt-wa" href="#" target="_blank" rel="noopener" class="font-head inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebe5a] text-white font-bold rounded-xl px-6 py-3 text-sm transition active:scale-95"><x-wa-icon class="w-4 h-4" /> Tanya via WhatsApp</a>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var M = document.getElementById('dt-modal'); if (!M) return;
    var P = M.querySelector('[data-panel]'), list = [], idx = -1, lastFocus = null;
    function $(id) { return document.getElementById(id); }
    var ICON = {
        briefcase: '<path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>',
        pin: '<path d="M12 21s-7-6.2-7-11a7 7 0 1114 0c0 4.8-7 11-7 11z"/><path d="M12 7.5a2.5 2.5 0 100 5 2.5 2.5 0 000-5z"/>',
        layers: '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>'
    };
    function data(el) { try { return JSON.parse(el.getAttribute('data-detail')); } catch (e) { return null; } }
    function cards() { return [].slice.call(document.querySelectorAll('[data-detail]')).filter(function (e) { return !e.closest('.hidden'); }); }

    function render() {
        var d = data(list[idx]); if (!d) return;
        var img = $('dt-img'), ph = $('dt-ph');
        if (d.img) { img.src = d.img; img.alt = d.t; img.classList.remove('hidden'); ph.classList.add('hidden'); }
        else { img.classList.add('hidden'); img.removeAttribute('src'); ph.classList.remove('hidden'); }
        $('dt-title').textContent = d.t;

        var chips = $('dt-chips'); chips.innerHTML = '';
        (d.chips || []).forEach(function (c, i) {
            var s = document.createElement('span');
            s.className = 'rounded-full px-3 py-1 text-xs font-bold ' + (i === 0 ? 'bg-[#0B3D91] text-white' : 'bg-[#0B3D91]/10 text-[#0B3D91]');
            s.textContent = c; chips.appendChild(s);
        });

        var meta = $('dt-meta'); meta.innerHTML = '';
        (d.meta || []).forEach(function (m) {
            var li = document.createElement('li'); li.className = 'flex items-start gap-3';
            li.innerHTML = '<svg class="w-5 h-5 text-[#4A90D9] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' + (ICON[m[0]] || '') + '</svg><span><span class="block text-[11px] uppercase tracking-wider text-gray-400"></span><span class="block font-semibold text-slate-700"></span></span>';
            var sp = li.querySelectorAll('span span'); sp[0].textContent = m[1]; sp[1].textContent = m[2];
            meta.appendChild(li);
        });

        $('dt-descbox').classList.toggle('hidden', !d.desc);
        $('dt-desclabel').textContent = d.label || 'Deskripsi';
        $('dt-desc').textContent = d.desc || '';
        $('dt-dl').classList.toggle('hidden', !d.dl);

        var wa = document.getElementById('wa-float'), a = $('dt-wa');
        if (wa) { a.href = wa.href.split('?')[0] + '?text=' + encodeURIComponent('Halo, saya ingin bertanya tentang "' + d.t + '".'); a.classList.remove('hidden'); }
        else { a.classList.add('hidden'); }

        $('dt-count').textContent = (idx + 1) + ' / ' + list.length;
        $('dt-prev').disabled = idx <= 0; $('dt-next').disabled = idx >= list.length - 1;
        P.scrollTop = 0;
    }
    function open(el) {
        list = cards(); idx = list.indexOf(el); if (idx < 0) return;
        lastFocus = el; render();
        M.classList.remove('invisible', 'opacity-0'); P.classList.remove('scale-95', 'translate-y-4'); document.body.style.overflow = 'hidden';
        setTimeout(function () { $('dt-close').focus(); }, 120);
    }
    function close() {
        M.classList.add('invisible', 'opacity-0'); P.classList.add('scale-95', 'translate-y-4'); document.body.style.overflow = '';
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    function go(n) { if (idx + n >= 0 && idx + n < list.length) { idx += n; render(); } }
    function isOpen() { return !M.classList.contains('invisible'); }

    document.addEventListener('click', function (e) {
        if (isOpen() || !e.target.closest) return;
        var card = e.target.closest('[data-detail]'); if (!card) return;
        var inner = e.target.closest('button, a');
        if (inner && card.contains(inner)) return;            // tombol/tautan di dalam kartu (mis. Download dokumen) bekerja sendiri
        open(card);
    });
    document.addEventListener('keydown', function (e) {
        if (isOpen()) {
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowLeft') go(-1);
            else if (e.key === 'ArrowRight') go(1);
            return;
        }
        if ((e.key === 'Enter' || e.key === ' ') && e.target.matches && e.target.matches('[data-detail]')) { e.preventDefault(); open(e.target); }
    });
    $('dt-close').addEventListener('click', close);
    $('dt-prev').addEventListener('click', function () { go(-1); });
    $('dt-next').addEventListener('click', function () { go(1); });
    M.addEventListener('click', function (e) { if (e.target === M) close(); });
    var tx = null;
    P.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    P.addEventListener('touchend', function (e) { if (tx === null) return; var dx = e.changedTouches[0].clientX - tx; tx = null; if (Math.abs(dx) > 70) go(dx < 0 ? 1 : -1); }, { passive: true });
    $('dt-dl').addEventListener('click', function () {
        var d = data(list[idx]); if (!d || !d.dl) return;
        close(); if (window.PWLDownload) window.PWLDownload.open({ fileId: d.dl, title: d.t });
    });
})();
</script>