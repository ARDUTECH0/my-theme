/**
 * ECM Theme — UX Polish
 *  • شريط تقدّم القراءة (Scroll progress)
 *  • زر العودة للأعلى (Back to top)
 *  • زر نسخ على بلوكات الأكواد (Copy)
 *
 * بدون أي اعتماديات (vanilla JS).
 */
(function () {
    'use strict';

    if (typeof document === 'undefined') return;

    function init() {
        var body = document.body;
        if (!body) return;

        /* ── شريط التقدّم ── */
        var bar = document.createElement('div');
        bar.className = 'ecm-scroll-progress';
        bar.setAttribute('aria-hidden', 'true');
        body.appendChild(bar);

        /* ── زر العودة للأعلى ── */
        var toTop = document.createElement('button');
        toTop.type = 'button';
        toTop.className = 'ecm-back-to-top';
        toTop.setAttribute('aria-label', 'العودة للأعلى');
        toTop.innerHTML = '↑';
        body.appendChild(toTop);
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        function onScroll() {
            var doc = document.documentElement;
            var st = window.pageYOffset || doc.scrollTop;
            var max = doc.scrollHeight - doc.clientHeight;
            bar.style.width = (max > 0 ? (st / max) * 100 : 0) + '%';
            toTop.classList.toggle('is-visible', st > 400);
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        onScroll();

        /* ── زر نسخ على الأكواد ── */
        var pres = document.querySelectorAll('pre');
        Array.prototype.forEach.call(pres, function (pre) {
            if (pre.getAttribute('data-ecm-copy')) return;
            pre.setAttribute('data-ecm-copy', '1');

            var codeText = (pre.querySelector('code') || pre).innerText;

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ecm-code-copy';
            btn.textContent = 'نسخ';

            if (getComputedStyle(pre).position === 'static') {
                pre.style.position = 'relative';
            }
            pre.appendChild(btn);

            btn.addEventListener('click', function () {
                var done = function () {
                    btn.textContent = 'تم النسخ ✓';
                    setTimeout(function () { btn.textContent = 'نسخ'; }, 2000);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(codeText).then(done).catch(function () {});
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = codeText;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(ta);
                }
            });
        });
    }

    /* ── ملء الشاشة لموديلات الـ 3D (delegation — بدون inline JS) ── */
    document.addEventListener('click', function (e) {
        var t = e.target;
        if (!t || !t.closest) return;
        var btn = t.closest('.ecm-3d-fs-btn');
        if (!btn) return;
        var holder = btn.closest('.ecm-3d-holder') || btn.parentNode;
        var mv = holder ? holder.querySelector('model-viewer') : null;
        if (mv) {
            var fn = mv.requestFullscreen || mv.webkitRequestFullscreen;
            if (fn) { fn.call(mv); }
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
