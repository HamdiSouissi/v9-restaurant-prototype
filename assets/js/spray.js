/*!
 * BOMBA — spray.js
 * Canvas de spray paint dans le hero : le déplacement de la souris laisse une
 * traînée de particules (lime puis rose en alternance) qui s'estompe en 1,5 s.
 * Sur mobile, le spray suit le doigt pendant 2 s puis se coupe.
 * Désactivé en prefers-reduced-motion et mis en pause hors écran.
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    function init() {
        var canvas = B.$('#sprayCanvas');
        if (!canvas) return;

        if (B.reduced) { canvas.style.display = 'none'; return; }

        var ctx = canvas.getContext('2d');
        if (!ctx) { canvas.style.display = 'none'; return; }

        var hero = canvas.parentElement;
        var dpr = Math.min(2, window.devicePixelRatio || 1);
        var W = 0, H = 0;
        var parts = [];
        var visible = true;
        var rafId = 0;
        var lastT = performance.now();
        var colorFlip = 0;
        var lastSwap = 0;
        var touchWindowUntil = 0;
        var pointerInside = false;

        var COLORS = [
            (B.cfg.colors && B.cfg.colors.lime) || '#C6FF3D',
            (B.cfg.colors && B.cfg.colors.rosa) || '#FF4FA3'
        ];
        var LIFE = 1.5;             // secondes avant disparition complète
        var MAX_PARTS = 1400;

        function resize() {
            if (!hero) return;
            var r = hero.getBoundingClientRect();
            W = Math.max(1, Math.round(r.width));
            H = Math.max(1, Math.round(r.height));
            canvas.width = Math.round(W * dpr);
            canvas.height = Math.round(H * dpr);
            canvas.style.width = W + 'px';
            canvas.style.height = H + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function emit(x, y, power) {
            var n = Math.round(6 + power * 9);
            var color = COLORS[colorFlip % COLORS.length];
            for (var i = 0; i < n; i++) {
                if (parts.length >= MAX_PARTS) break;
                var a = Math.random() * Math.PI * 2;
                var r = Math.pow(Math.random(), 0.55) * (18 + power * 16);
                parts.push({
                    x: x + Math.cos(a) * r,
                    y: y + Math.sin(a) * r,
                    vx: Math.cos(a) * (0.25 + Math.random() * 0.9),
                    vy: Math.sin(a) * (0.25 + Math.random() * 0.9) + 0.22,
                    r: 0.5 + Math.random() * (1.5 + power * 1.5),
                    a: 0.18 + Math.random() * 0.5,
                    c: color
                });
            }
        }

        function frame(now) {
            rafId = 0;
            var dt = Math.min(0.05, (now - lastT) / 1000);
            lastT = now;

            if (visible) {
                // Estompage global : la traînée disparaît en ~1,5 s.
                ctx.globalCompositeOperation = 'destination-out';
                ctx.fillStyle = 'rgba(0,0,0,' + Math.min(1, dt / LIFE).toFixed(4) + ')';
                ctx.fillRect(0, 0, W, H);
                ctx.globalCompositeOperation = 'source-over';

                for (var i = parts.length - 1; i >= 0; i--) {
                    var p = parts[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += 0.02;
                    p.vx *= 0.985;
                    p.a -= dt / LIFE;
                    if (p.a <= 0) { parts.splice(i, 1); continue; }
                    ctx.globalAlpha = Math.max(0, p.a);
                    ctx.fillStyle = p.c;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.r, 0, 6.2832);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;

                if (parts.length || pointerInside) start();
            }
        }

        function start() {
            if (!rafId) { lastT = performance.now(); rafId = requestAnimationFrame(frame); }
        }

        function local(e) {
            var r = canvas.getBoundingClientRect();
            return { x: e.clientX - r.left, y: e.clientY - r.top };
        }

        function onMove(e) {
            if (!visible) return;
            var now = performance.now();
            if (now - lastSwap > 320) { colorFlip++; lastSwap = now; }

            var p = local(e);
            var power = 0;
            if (typeof e.pressure === 'number' && e.pressure > 0) power = e.pressure;

            emit(p.x, p.y, B.clamp(power, 0, 1));
            start();
        }

        if (hero) {
            hero.addEventListener('pointermove', function (e) {
                if (e.pointerType === 'touch') return;
                pointerInside = true;
                onMove(e);
            }, { passive: true });

            hero.addEventListener('pointerdown', function (e) {
                if (e.pointerType === 'touch') return;
                var p = local(e);
                emit(p.x, p.y, 1);
                start();
            }, { passive: true });

            hero.addEventListener('pointerleave', function () { pointerInside = false; });

            // Tactile : le spray suit le doigt pendant 2 s puis se coupe.
            hero.addEventListener('touchstart', function () {
                touchWindowUntil = performance.now() + 2000;
            }, { passive: true });

            hero.addEventListener('touchmove', function (e) {
                if (performance.now() > touchWindowUntil) return;
                var t = e.touches && e.touches[0];
                if (!t) return;
                var now = performance.now();
                if (now - lastSwap > 320) { colorFlip++; lastSwap = now; }
                var r = canvas.getBoundingClientRect();
                emit(t.clientX - r.left, t.clientY - r.top, 0.65);
                start();
            }, { passive: true });

            if ('IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    visible = entries[0].isIntersecting;
                    canvas.style.visibility = visible ? 'visible' : 'hidden';
                    if (visible) start();
                }, { threshold: 0 }).observe(hero);
            }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { visible = false; } else { visible = true; start(); }
        });

        resize();
        window.addEventListener('resize', B.debounce(resize, 180));
        B.on('resize', resize);
    }

    B.spray = { init: init };
})();
