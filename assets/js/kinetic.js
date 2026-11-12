/*!
 * BOMBA — kinetic.js
 * Typographie variable animée (Archivo : wdth 62 → 125, wght 400 → 900).
 *  · largeur/graisse scrubées au scroll sur tout [data-kinetic]
 *  · « gonflement » de 300 ms au survol (via --wdth-h, sans conflit)
 *  · BOMBA du footer : chaque lettre change de largeur selon la distance
 *    au curseur, indépendamment de ses voisines
 *  · prefers-reduced-motion → tout reste fixe en wdth 100
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    function init() {
        if (B.reduced) { fixAll(); return; }

        var nodes = B.$$('[data-kinetic]');
        var letters = B.$$('[data-ftr-letter]');

        initScrollKinetic(nodes);
        initHoverKinetic(nodes);
        initFooterWord(letters);
    }

    /** Fixe tout en wdth 100 quand le mouvement est réduit. */
    function fixAll() {
        B.$$('[data-kinetic]').forEach(function (el) {
            el.style.setProperty('--wdth', '100');
            el.style.removeProperty('--wdth-h');
        });
        B.$$('[data-ftr-letter]').forEach(function (el) {
            el.style.setProperty('--wdth', '100');
        });
        B.$$('.hero__ch').forEach(function (el) { el.style.setProperty('--wdth', '100'); });
    }

    /** Lit "62,125" → [62, 125]. */
    function readRange(el) {
        var raw = (el.getAttribute('data-kinetic') || '62,125').split(',');
        var a = parseFloat(raw[0]), b = parseFloat(raw[1]);
        if (isNaN(a)) a = 62;
        if (isNaN(b)) b = 125;
        return [Math.min(a, b), Math.max(a, b)];
    }

    /* ------------------------------------------------------------------ *
     * Scrub au scroll : la largeur se déplie pendant que l'élément entre
     * dans l'écran (uniquement les titres visibles → coût maîtrisé).
     * ------------------------------------------------------------------ */
    function initScrollKinetic(nodes) {
        if (!B.gsap || !B.ST || !nodes.length) {
            nodes.forEach(function (el) {
                var r = readRange(el);
                el.style.setProperty('--wdth', String(r[1]));
            });
            return;
        }

        nodes.forEach(function (el) {
            var r = readRange(el);
            var isHeroWord = el.classList.contains('hero__ch');
            if (isHeroWord) return;               // piloté par main.js (respiration)

            B.gsap.set(el, { '--wdth': r[0], '--wght': Math.max(400, 900 - (r[1] - r[0]) * 2) });

            B.ST.create({
                trigger: el,
                start: 'top bottom',
                end: 'top 34%',
                scrub: 0.45,
                onUpdate: function (self) {
                    var p = self.progress;
                    el.style.setProperty('--wdth', (r[0] + (r[1] - r[0]) * p).toFixed(2));
                    el.style.setProperty('--wght', Math.round(620 + 280 * p));
                }
            });
        });
    }

    /* ------------------------------------------------------------------ *
     * Survol : « gonflement » 300 ms, sans toucher à la valeur scrubbée
     * (on écrit sur --wdth-h, la CSS choisit le bon étage).
     * ------------------------------------------------------------------ */
    function initHoverKinetic(nodes) {
        if (B.coarse) return;
        nodes.forEach(function (el) {
            var r = readRange(el);
            function inflate() {
                el.style.setProperty('--wdth-h', String(r[1]));
                el.style.setProperty('--wght-h', '900');
            }
            function deflate() {
                el.style.removeProperty('--wdth-h');
                el.style.removeProperty('--wght-h');
            }
            el.addEventListener('pointerenter', function (e) { if (e.pointerType !== 'touch') inflate(); });
            el.addEventListener('pointerleave', deflate);
            el.addEventListener('focusin', inflate);
            el.addEventListener('focusout', deflate);
        });
    }

    /* ------------------------------------------------------------------ *
     * Footer : BOMBA pleine largeur, chaque lettre réagit à la distance
     * du curseur (62 → 125), indépendamment des autres.
     * ------------------------------------------------------------------ */
    function initFooterWord(letters) {
        if (!letters.length) return;

        var word = B.$('[data-ftr-word]');
        if (!word) return;

        var MIN = 66, MAX = 125, RADIUS = 340;
        var cache = letters.map(function (el) { return { el: el, cx: 0, cur: 100 }; });
        var visible = false;

        function measure() {
            cache.forEach(function (c) {
                var r = c.el.getBoundingClientRect();
                c.cx = r.left + r.width / 2;
                c.cy = r.top + r.height / 2;
            });
        }

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (en) {
                visible = en[0].isIntersecting;
                if (visible) measure();
                else cache.forEach(function (c) { c.el.style.setProperty('--wdth', '100'); c.cur = 100; });
            }, { threshold: 0.05 }).observe(word);
        } else {
            visible = true;
        }

        window.addEventListener('resize', B.debounce(measure, 200));

        var targetX = -9999, targetY = -9999, raf = 0;

        function loop() {
            raf = 0;
            if (!visible) return;
            for (var i = 0; i < cache.length; i++) {
                var c = cache[i];
                var d = Math.abs(targetX - c.cx) + Math.abs(targetY - c.cy) * 0.35;
                var t = B.clamp(1 - d / RADIUS, 0, 1);
                var want = MIN + (MAX - MIN) * t * t;
                c.cur = B.lerp(c.cur, want, 0.22);
                c.el.style.setProperty('--wdth', c.cur.toFixed(1));
            }
            if (Math.abs(cache[0].cur - (MIN + (MAX - MIN))) > 0.4 || targetX > -9000) {
                raf = requestAnimationFrame(loop);
            }
        }

        word.addEventListener('pointermove', function (e) {
            if (e.pointerType === 'touch') return;
            targetX = e.clientX;
            targetY = e.clientY;
            if (!raf) raf = requestAnimationFrame(loop);
        }, { passive: true });

        word.addEventListener('pointerleave', function () {
            targetX = -9999; targetY = -9999;
            cache.forEach(function (c) { c.el.style.setProperty('--wdth', '100'); c.cur = 100; });
        });

        // Accessibilité clavier : la lettre survolée/focusée se gonfle aussi.
        letters.forEach(function (el, i) {
            el.addEventListener('focusin', function () { el.style.setProperty('--wdth', String(MAX)); });
            el.addEventListener('focusout', function () { el.style.setProperty('--wdth', '100'); });
        });
    }

    B.kinetic = { init: init };
})();
