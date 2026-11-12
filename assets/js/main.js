/*!
 * BOMBA — main.js
 * Noyau : namespace, Lenis, curseur, reveals, balayages de couleur, header,
 * menu mobile, hero (parallaxe + collage cliquable), rubalise vélocité,
 * best-sellers épinglés, menu « scontrino », compteurs, toasts, barre mobile.
 *
 * Amélioration progressive : sans GSAP/Lenis le site reste lisible et
 * fonctionnel (le CSS porte alors tout l'état initial).
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    /* ===================================================================== *
     * 0. CONTEXTE
     * ===================================================================== */
    B.cfg     = window.BOMBA || {};
    B.menu    = window.MENU || { items: {}, categories: [] };
    B.cutouts = (B.cfg && B.cfg.cutouts) || {};
    B.gsap    = window.gsap || null;
    B.ST      = window.ScrollTrigger || null;
    B.lenis   = null;
    B.velocity = 0;

    var mqReduce = window.matchMedia('(prefers-reduced-motion: reduce)');
    var mqCoarse = window.matchMedia('(hover: none), (pointer: coarse)');
    B.reduced = mqReduce.matches;
    B.coarse  = mqCoarse.matches;

    /* ===================================================================== *
     * 1. UTILITAIRES
     * ===================================================================== */
    B.$  = function (sel, root) { return (root || document).querySelector(sel); };
    B.$$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    B.clamp = function (v, a, b) { return Math.min(b, Math.max(a, v)); };
    B.lerp  = function (a, b, t) { return a + (b - a) * t; };
    B.rnd   = function (a, b) { return a + Math.random() * (b - a); };
    B.pick  = function (arr) { return arr[Math.floor(Math.random() * arr.length)]; };

    /** Même règle que bomba_money() côté PHP : € 9 / € 3,50 / € 1,90 */
    B.money = function (v) {
        v = Number(v) || 0;
        var d = Math.abs(v - Math.round(v)) < 0.0001 ? 0 : 2;
        try {
            return '€ ' + v.toLocaleString('it-IT', { minimumFractionDigits: d, maximumFractionDigits: d });
        } catch (err) {
            return '€ ' + v.toFixed(d).replace('.', ',');
        }
    };

    B.escape = function (str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    B.debounce = function (fn, ms) {
        var t;
        return function () {
            var args = arguments, self = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(self, args); }, ms || 120);
        };
    };

    B.item = function (id) { return B.menu.items[id] || null; };

    B._e = {};
    B.on = function (name, fn) { (B._e[name] = B._e[name] || []).push(fn); };
    B.emit = function (name, data) {
        (B._e[name] || []).forEach(function (fn) {
            try { fn(data); } catch (err) { console.error('[bomba]', name, err); }
        });
    };

    /* --- focus trap + verrou de scroll ---------------------------------- */
    B.lockScroll = function (on) {
        var de = document.documentElement;
        if (on) {
            B._scrollY = window.scrollY;
            de.style.overflow = 'hidden';
            if (B.lenis && B.lenis.stop) B.lenis.stop();
        } else {
            de.style.overflow = '';
            if (B.lenis && B.lenis.start) B.lenis.start();
        }
    };

    B.trapFocus = function (container) {
        var SEL = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
        function onKey(e) {
            if (e.key !== 'Tab') return;
            var nodes = B.$$(SEL, container).filter(function (n) {
                return n.offsetParent !== null || n === document.activeElement;
            });
            if (!nodes.length) return;
            var first = nodes[0], last = nodes[nodes.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
        container.addEventListener('keydown', onKey);
        return function () { container.removeEventListener('keydown', onKey); };
    };

    B.scrollToId = function (id, offset) {
        var el = typeof id === 'string' ? B.$(id) : id;
        if (!el) return;
        var off = offset == null ? -(parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-h'), 10) || 64) - 8 : offset;
        if (B.lenis && B.lenis.scrollTo) { B.lenis.scrollTo(el, { offset: off, duration: B.reduced ? 0.01 : 1.1 }); return; }
        var y = el.getBoundingClientRect().top + window.scrollY + off;
        window.scrollTo({ top: y, behavior: B.reduced ? 'auto' : 'smooth' });
    };

    /* ===================================================================== *
     * 2. TOASTS (stickers empilables, aria-live, 4 s, action possible)
     * ===================================================================== */
    B.toast = function (message, opts) {
        opts = opts || {};
        var host = B.$('[data-toasts]');
        var tpl  = B.$('[data-toast-tpl]');
        if (!host || !tpl) return null;

        var node = tpl.content.firstElementChild.cloneNode(true);
        B.$('[data-toast-txt]', node).textContent = message;
        if (opts.variant) node.classList.add('toast--' + opts.variant);

        var life = opts.duration == null ? 4000 : opts.duration;
        var killed = false;
        function close() {
            if (killed) return;
            killed = true;
            clearTimeout(timer);
            node.classList.add('is-out');
            setTimeout(function () { node.remove(); }, 320);
        }
        var timer = life > 0 ? setTimeout(close, life) : null;

        var act = B.$('[data-toast-act]', node);
        if (opts.action && act) {
            act.hidden = false;
            act.textContent = opts.action;
            act.addEventListener('click', function () { if (opts.onAction) opts.onAction(); close(); });
        } else if (act) { act.remove(); }

        B.$('[data-toast-x]', node).addEventListener('click', close);
        host.appendChild(node);

        while (host.children.length > 4) host.firstElementChild.remove();
        return close;
    };

    /* ===================================================================== *
     * 3. LENIS + GSAP
     * ===================================================================== */
    B.initCore = function () {
        if (B.gsap && B.ST) B.gsap.registerPlugin(B.ST);
        if (window.Draggable && B.gsap) B.gsap.registerPlugin(window.Draggable);
        if (window.InertiaPlugin && B.gsap) { try { B.gsap.registerPlugin(window.InertiaPlugin); } catch (e) {} }

        if (B.reduced || !window.Lenis) {
            document.documentElement.classList.add('no-lenis');
            return;
        }

        try {
            B.lenis = new window.Lenis({
                duration: 1.05,
                easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },
                smoothWheel: true,
                touchMultiplier: 1.6,
                wheelMultiplier: 1,
                syncTouch: false
            });
        } catch (err) {
            console.warn('[bomba] Lenis indisponible', err);
            B.lenis = null;
            return;
        }

        var lastV = 0;
        B.lenis.on('scroll', function (e) {
            var v = (e && typeof e.velocity === 'number') ? e.velocity : 0;
            B.velocity = v;
            if (B.ST) B.ST.update();
            B.emit('scroll', { velocity: v, scroll: e && e.scroll });
            lastV = v;
        });

        /* Lenis doit être piloté par le ticker GSAP pour rester synchrone avec
           ScrollTrigger ; sans ticker on retombe sur requestAnimationFrame. */
        var ticker = (B.gsap && B.gsap.ticker && typeof B.gsap.ticker.add === 'function') ? B.gsap.ticker : null;
        if (ticker) {
            ticker.add(function (time) { if (B.lenis && B.lenis.raf) B.lenis.raf(time * 1000); });
            if (ticker.lagSmoothing) ticker.lagSmoothing(0);
        } else {
            (function loop(t) {
                if (B.lenis && B.lenis.raf) B.lenis.raf(t);
                requestAnimationFrame(loop);
            })(performance.now());
        }
    };

    /* ===================================================================== *
     * 4. CURSEUR PERSONNALISÉ (desktop uniquement)
     * ===================================================================== */
    B.initCursor = function () {
        if (B.coarse || B.reduced || !B.gsap) return;

        var el = document.createElement('div');
        el.className = 'cursor';
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = '<span class="cursor__label"></span>';
        document.body.appendChild(el);
        var label = el.firstElementChild;

        // quickTo existe depuis GSAP 3.10 ; sinon on retombe sur un tween simple.
        var xTo, yTo;
        if (typeof B.gsap.quickTo === 'function') {
            xTo = B.gsap.quickTo(el, 'x', { duration: 0.28, ease: 'power3' });
            yTo = B.gsap.quickTo(el, 'y', { duration: 0.28, ease: 'power3' });
        } else {
            xTo = function (v) { B.gsap.to(el, { x: v, duration: 0.28, ease: 'power3', overwrite: 'auto' }); };
            yTo = function (v) { B.gsap.to(el, { y: v, duration: 0.28, ease: 'power3', overwrite: 'auto' }); };
        }
        var visible = false;

        window.addEventListener('pointermove', function (e) {
            if (e.pointerType === 'touch') return;
            if (!visible) { visible = true; el.classList.add('is-on'); B.gsap.set(el, { x: e.clientX, y: e.clientY }); }
            xTo(e.clientX); yTo(e.clientY);
        }, { passive: true });

        document.addEventListener('pointerleave', function () { el.classList.remove('is-on'); visible = false; });

        var MODES = [
            { sel: '[data-cursor="dish"]',    cls: 'cursor--dish',    txt: '+ Aggiungi' },
            { sel: '[data-add]',              cls: 'cursor--dish',    txt: '+ Aggiungi' },
            { sel: '[data-cursor="gallery"]', cls: 'cursor--gallery', txt: 'Guarda' },
            { sel: '.pola',                   cls: 'cursor--gallery', txt: 'Guarda' },
            { sel: '[data-cursor="grab"]',    cls: 'cursor--grab',    txt: 'Prendimi' },
            { sel: '.ftr__stk',               cls: 'cursor--grab',    txt: 'Prendimi' },
            { sel: 'a[href],button,input,select,textarea,label,.opt,.guest,.slot,.pill,.chip', cls: 'cursor--link', txt: '' }
        ];

        document.addEventListener('pointerover', function (e) {
            if (e.pointerType === 'touch') return;
            var t = e.target;
            if (!t || !t.closest) return;
            var hit = null;
            for (var i = 0; i < MODES.length; i++) {
                if (t.closest(MODES[i].sel)) { hit = MODES[i]; break; }
            }
            MODES.forEach(function (m) { if (m.cls) el.classList.remove(m.cls); });
            if (hit) {
                el.classList.add(hit.cls);
                label.textContent = hit.txt || '';
                if (hit.cls === 'cursor--link' && !hit.txt) label.textContent = '↗';
            } else {
                label.textContent = '';
            }
        }, { passive: true });

        document.addEventListener('pointerdown', function () {
            B.gsap.to(el, { scale: 0.82, duration: 0.12, ease: 'power2.out' });
        });
        document.addEventListener('pointerup', function () {
            B.gsap.to(el, { scale: 1, duration: 0.4, ease: 'elastic.out(1, 0.4)' });
        });
    };

    /* ===================================================================== *
     * 5. HEADER : masque au scroll bas, réapparaît au scroll haut
     * ===================================================================== */
    B.initHeader = function () {
        var hdr = B.$('[data-header]');
        if (!hdr) return;
        var last = window.scrollY, delta = 0;

        function onScroll() {
            var y = window.scrollY || 0;
            delta = y - last;
            if (y < 90) { hdr.classList.remove('is-hidden'); }
            else if (delta > 6 && y > 260) { hdr.classList.add('is-hidden'); }
            else if (delta < -6) { hdr.classList.remove('is-hidden'); }
            if (Math.abs(delta) > 2) last = y;
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        B.on('scroll', onScroll);

        // Lien de navigation actif
        var links = B.$$('.hdr__link');
        if (links.length && B.ST) {
            links.forEach(function (a) {
                var sec = B.$(a.getAttribute('href'));
                if (!sec) return;
                B.ST.create({
                    trigger: sec,
                    start: 'top 45%',
                    end: 'bottom 45%',
                    onToggle: function (self) { a.classList.toggle('is-active', self.isActive); }
                });
            });
        }
    };

    /* ===================================================================== *
     * 6. MENU MOBILE PLEIN ÉCRAN
     * ===================================================================== */
    B.initMobileNav = function () {
        var root = B.$('[data-mnav]');
        if (!root) return;
        var openers = B.$$('[data-nav-open]');
        var closers = B.$$('[data-nav-close]', root);
        var release = null, lastFocus = null;

        function open() {
            lastFocus = document.activeElement;
            root.hidden = false;
            requestAnimationFrame(function () { root.classList.add('is-open'); });
            B.lockScroll(true);
            release = B.trapFocus(root);
            openers.forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
            var first = B.$('.mnav__link', root);
            setTimeout(function () { if (first) first.focus(); }, 260);
        }
        function close() {
            root.classList.remove('is-open');
            openers.forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
            B.lockScroll(false);
            if (release) release();
            release = null;
            setTimeout(function () { root.hidden = true; }, 480);
            if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        openers.forEach(function (b) { b.addEventListener('click', open); });
        closers.forEach(function (b) { b.addEventListener('click', close); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !root.hidden) close(); });
        B.on('navigate', close);
        B.closeNav = close;
    };

    /* ===================================================================== *
     * 7. ANCRES INTERNES
     * ===================================================================== */
    B.initAnchors = function () {
        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href^="#"]') : null;
            if (!a) return;
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.length < 2) return;
            var target = B.$(href);
            if (!target) return;
            e.preventDefault();
            B.emit('navigate', href);
            if (B.closeNav) B.closeNav();
            B.scrollToId(href);
            if (history.replaceState) history.replaceState(null, '', href);
        });
    };

    /* ===================================================================== *
     * 8. BALAYAGE DE COULEUR ENTRE SECTIONS (clip-path diagonal, scrubé)
     * ===================================================================== */
    B.initSweep = function () {
        if (B.reduced) return;
        var panel = B.$('[data-sweep-panel]');
        var fill  = B.$('[data-sweep-fill]');
        if (!panel || !fill) return;

        var sections = B.$$('main .section[data-sweep]');
        if (!sections.length) return;

        var ticking = false;
        function update() {
            ticking = false;
            var vh = window.innerHeight;
            var best = null;
            for (var i = 0; i < sections.length; i++) {
                var r = sections[i].getBoundingClientRect();
                // La transition se joue pendant que le haut de la section
                // traverse la bande 92% → 26% de l'écran.
                var p = (vh * 0.92 - r.top) / (vh * 0.66);
                if (p > -0.05 && p < 1.05) {
                    if (!best || Math.abs(p - 0.5) < Math.abs(best.p - 0.5)) best = { p: p, sec: sections[i] };
                }
            }
            if (!best) { panel.style.clipPath = 'polygon(0 0, 0 0, 0 100%, 0 100%)'; return; }
            var p = B.clamp(best.p, 0, 1);
            var color = getComputedStyle(best.sec).backgroundColor;
            if (color && fill.style.background !== color) fill.style.background = color;
            var lead  = -34 + p * 168;
            var trail = lead - 52;
            panel.style.clipPath = 'polygon(' + trail + '% -20%, ' + lead + '% -20%, ' +
                (lead - 16) + '% 120%, ' + (trail - 16) + '% 120%)';
        }
        function request() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }

        window.addEventListener('scroll', request, { passive: true });
        window.addEventListener('resize', request);
        B.on('scroll', request);
        update();
    };

    /* ===================================================================== *
     * 9. REVEALS
     * ===================================================================== */
    B.initReveals = function () {
        var sections = B.$$('main section, .ftr');

        // « is-in » sur chaque section → déclenche les tracés dessinés à la main.
        sections.forEach(function (sec) {
            if (!B.ST) { sec.classList.add('is-in'); return; }
            B.ST.create({
                trigger: sec, start: 'top 82%', once: true,
                onEnter: function () { sec.classList.add('is-in'); }
            });
        });

        /* Sans GSAP (ou en reduced-motion) rien n'est masqué : le contenu
           reste tel que le PHP l'a écrit, on ne touche à aucun style. */
        if (!B.gsap || !B.ST || B.reduced) { return; }

        var groups = [
            { sel: '.label',      y: 46, rot: true },
            { sel: '.fld',        y: 34 },
            { sel: '.stat',       y: 40, stagger: 0.08 },
            { sel: '.bub',        y: 0,  pop: true },
            { sel: '.combo__side', y: 40 },
            { sel: '.step',       y: 34 }
        ];

        groups.forEach(function (g) {
            var els = B.$$(g.sel);
            if (!els.length) return;
            B.gsap.set(els, { opacity: 0, y: g.y || 0 });
            B.ST.batch(els, {
                start: 'top 88%',
                once: true,
                onEnter: function (batch) {
                    B.gsap.to(batch, {
                        opacity: 1, y: 0,
                        duration: 0.55,
                        ease: 'expo.out',
                        stagger: g.stagger || 0.05,
                        overwrite: true
                    });
                }
            });
        });

        // Titres : chaque bloc monte derrière un masque (créé à la volée),
        // puis le masque repasse en overflow visible pour ne rien rogner
        // (cercles dessinés à la main, débordements volontaires).
        B.$$('.h2').forEach(function (h) {
            var parts = B.$$('.h2__a, .h2__b', h);
            if (!parts.length) return;
            var masks = parts.map(function (p) {
                var mask = document.createElement('span');
                mask.className = 'h2-mask';
                p.parentNode.insertBefore(mask, p);
                mask.appendChild(p);
                return mask;
            });
            B.gsap.set(parts, { yPercent: 112 });
            B.ST.create({
                trigger: h, start: 'top 88%', once: true,
                onEnter: function () {
                    B.gsap.to(parts, {
                        yPercent: 0, duration: 0.78, ease: 'expo.out', stagger: 0.06,
                        onComplete: function () { masks.forEach(function (m) { m.style.overflow = 'visible'; }); }
                    });
                }
            });
        });
    };

    /* ===================================================================== *
     * 10. PRELOADER (≤ 1,8 s, une fois par session)
     * ===================================================================== */
    B._preloadCbs = [];
    B._preloaded = false;
    B.afterPreload = function (fn) {
        if (B._preloaded) { fn(); return; }
        B._preloadCbs.push(fn);
    };

    B.initPreloader = function () {
        var el = B.$('#preloader');
        var pct = B.$('#preloaderPct');
        var html = document.documentElement;

        function finish() {
            if (B._preloaded) return;
            B._preloaded = true;
            html.classList.add('preloader-done');
            try { sessionStorage.setItem('bomba:seen', '1'); } catch (err) {}
            if (el && el.parentNode) el.parentNode.removeChild(el);
            B.lockScroll(false);
            B._preloadCbs.splice(0).forEach(function (fn) {
                try { fn(); } catch (err) { console.error('[bomba] preload', err); }
            });
            if (B.ST) setTimeout(function () { B.ST.refresh(); }, 60);
        }

        if (!el || html.classList.contains('preloader-done')) { finish(); return; }

        B.lockScroll(true);

        if (B.reduced) {
            if (pct) pct.textContent = '87%';
            setTimeout(finish, 260);
            return;
        }

        var start = performance.now(), DURATION = 1000;
        (function tick(now) {
            var p = B.clamp((now - start) / DURATION, 0, 1);
            if (pct) pct.textContent = Math.round(p * 87) + '%';
            if (p < 1) requestAnimationFrame(tick);
        })(start);

        setTimeout(function () { el.classList.add('is-boom'); }, 940);
        setTimeout(function () { el.classList.add('is-out'); }, 1220);
        setTimeout(finish, 1780);
    };

    /* ===================================================================== *
     * 11. RUBALISE — la vitesse suit la vélocité Lenis, ralentit au survol
     * ===================================================================== */
    B.initTicker = function () {
        var bands = B.$$('[data-marquee]');
        if (!bands.length) return;

        var items = bands.map(function (band) {
            var track = B.$('[data-marquee-track]', band);
            var run = track && track.firstElementChild;
            return {
                track: track,
                dir: band.getAttribute('data-marquee') === 'a' ? -1 : 1,
                base: band.getAttribute('data-marquee') === 'a' ? 92 : 74,
                hover: false,
                offset: 0,
                w: 0,
                run: run
            };
        }).filter(function (i) { return i.track && i.run; });

        function measure() {
            items.forEach(function (i) { i.w = i.run.getBoundingClientRect().width || 1; });
        }
        measure();
        window.addEventListener('resize', B.debounce(measure, 200));

        items.forEach(function (i) {
            i.track.addEventListener('pointerenter', function () { i.hover = true; });
            i.track.addEventListener('pointerleave', function () { i.hover = false; });
        });

        if (B.reduced) { measure(); return; }

        var last = performance.now(), visible = true;
        var box = B.$('.ticker');
        if (box && 'IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; },
                { threshold: 0 }).observe(box);
        }

        (function loop(now) {
            var dt = Math.min(0.05, (now - last) / 1000);
            last = now;
            if (visible) {
                var boost = Math.min(3.4, Math.abs(B.velocity) / 26);
                items.forEach(function (i) {
                    var speed = (i.base + i.base * boost * 1.5) * (i.hover ? 0.32 : 1);
                    i.offset = (i.offset + speed * dt) % i.w;
                    var x = i.dir < 0 ? -i.offset : -(i.w - i.offset);
                    i.track.style.transform = 'translate3d(' + x.toFixed(2) + 'px,0,0)';
                });
            }
            requestAnimationFrame(loop);
        })(performance.now());
    };

    /* ===================================================================== *
     * 12. HERO — titre cinétique, collage parallaxe, clic = ajout au panier
     * ===================================================================== */
    B.initHero = function () {
        var hero = B.$('.hero');
        if (!hero) return;

        var chs   = B.$$('.hero__ch', hero);
        var pieces = B.$$('.hero__piece', hero);
        var strip = B.$$('.hero__strip span', hero);

        /* --- Titre : chute lettre par lettre avec rebond --- */
        if (B.gsap && !B.reduced && chs.length) {
            B.gsap.set(chs, { yPercent: -128, opacity: 0 });
            B.afterPreload(function () {
                B.gsap.to(chs, {
                    yPercent: 0, opacity: 1,
                    duration: 0.86, ease: 'back.out(1.7)', stagger: 0.04, delay: 0.05
                });
            });
        } else if (chs.length) {
            chs.forEach(function (c) { c.style.opacity = 1; c.style.transform = 'none'; });
        }

        /* --- Largeur qui respire en continu (wdth 103 ↔ 121) --- */
        var heroVisible = true;
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (en) { heroVisible = en[0].isIntersecting; }, { threshold: 0 })
                .observe(hero);
        }
        if (!B.reduced && chs.length) {
            var t0 = performance.now();
            (function breathe(now) {
                if (heroVisible) {
                    var t = (now - t0) / 1000;
                    for (var i = 0; i < chs.length; i++) {
                        var w = 112 + Math.sin(t * 0.85 + i * 0.42) * 9;
                        chs[i].style.setProperty('--wdth', w.toFixed(2));
                    }
                }
                requestAnimationFrame(breathe);
            })(t0);
        }

        /* --- Bandeau du bas : défilement lent --- */
        if (B.gsap && strip.length > 1 && !B.reduced) {
            var w = strip[0].getBoundingClientRect().width;
            B.gsap.to(strip, { x: -w, duration: 22, ease: 'none', repeat: -1 });
        }

        /* --- Collage : parallaxe souris sur l'image, éparpillement au scroll --- */
        if (!B.gsap) return;

        pieces.forEach(function (p) {
            var rot = parseFloat(getComputedStyle(p).getPropertyValue('--rot')) || 0;
            B.gsap.set(p, { xPercent: -50, yPercent: -50, rotation: rot });
        });

        if (!B.reduced && B.ST) {
            B.ST.create({
                trigger: hero,
                start: 'top top',
                end: 'bottom top',
                scrub: 0.6,
                onUpdate: function (self) {
                    var p = self.progress;
                    pieces.forEach(function (piece) {
                        var depth = parseFloat(piece.getAttribute('data-parallax')) || 1;
                        var rectLeft = parseFloat(piece.style.left) || 50;
                        var rectTop  = parseFloat(piece.style.top) || 50;
                        var dx = (rectLeft - 50) * 3.1 * depth * p;
                        var dy = (rectTop - 50) * 2.4 * depth * p - 60 * p * depth;
                        B.gsap.set(piece, {
                            x: dx, y: dy,
                            rotation: (parseFloat(piece.style.getPropertyValue('--rot')) || 0) + p * 26 * (depth > 1.8 ? 1 : -1) * depth * 0.5,
                            scale: 1 - p * 0.14
                        });
                    });
                }
            });
        }

        if (!B.reduced && !B.coarse) {
            var imgs = pieces.map(function (p) { return B.$('.hero__piece-img', p) || p; });
            var qx = imgs.map(function (n) { return B.gsap.quickTo(n, 'x', { duration: 0.7, ease: 'power3' }); });
            var qy = imgs.map(function (n) { return B.gsap.quickTo(n, 'y', { duration: 0.7, ease: 'power3' }); });
            var qr = imgs.map(function (n) { return B.gsap.quickTo(n, 'rotation', { duration: 0.9, ease: 'power3' }); });

            hero.addEventListener('pointermove', function (e) {
                if (e.pointerType === 'touch') return;
                var nx = (e.clientX / window.innerWidth - 0.5);
                var ny = (e.clientY / window.innerHeight - 0.5);
                imgs.forEach(function (img, i) {
                    var d = parseFloat(pieces[i].getAttribute('data-parallax')) || 1;
                    qx[i](-nx * 42 * d);
                    qy[i](-ny * 32 * d);
                    qr[i](nx * 5 * d);
                });
            }, { passive: true });
        }

        /* L'ajout au sacchetto, le squash et les miettes sont pilotés par
           cart.js (handler délégué unique) : ici on ne fait rien de plus,
           sinon chaque clic compterait double. */
    };

    /** Petit nuage de miettes « croccanti » (CSS pur, 14 particules). */
    B.crumbs = function (x, y) {
        if (!B.gsap) return;
        var n = 14;
        for (var i = 0; i < n; i++) {
            var c = document.createElement('span');
            c.className = 'crumb';
            c.style.position = 'fixed';
            c.style.left = x + 'px';
            c.style.top = y + 'px';
            c.style.zIndex = '95';
            document.body.appendChild(c);
            var ang = B.rnd(0, Math.PI * 2), dist = B.rnd(40, 150);
            B.gsap.to(c, {
                x: Math.cos(ang) * dist,
                y: Math.sin(ang) * dist + B.rnd(60, 190),
                rotation: B.rnd(-320, 320),
                opacity: 0,
                scale: B.rnd(0.5, 1.3),
                duration: B.rnd(0.6, 1.05),
                ease: 'power2.out',
                onComplete: function (el) { return function () { el.remove(); }; }(c)
            });
        }
    };

    /* ===================================================================== *
     * 13. BEST-SELLERS — défilement horizontal épinglé
     * ===================================================================== */
    B.initBest = function () {
        var section = B.$('.best');
        var track = B.$('[data-best-track]');
        var bar = B.$('[data-best-bar]');
        // Sans GSAP, ou en reduced-motion, on laisse le scroll natif faire le travail.
        if (!section || !track || !B.gsap || !B.ST || B.reduced) return;

        var mm = B.gsap.matchMedia ? B.gsap.matchMedia() : null;

        function distance() {
            return Math.max(0, track.scrollWidth - window.innerWidth + 2);
        }

        function build() {
            var dist = distance();
            if (dist < 24) return null;
            var tween = B.gsap.to(track, {
                x: function () { return -distance(); },
                ease: 'none',
                scrollTrigger: {
                    trigger: section,
                    start: 'top top',
                    end: function () { return '+=' + distance(); },
                    pin: true,
                    pinSpacing: true,
                    scrub: 0.85,
                    anticipatePin: 1,
                    invalidateOnRefresh: true,
                    onUpdate: function (self) {
                        if (bar) bar.style.width = B.clamp(self.progress * 100, 4, 100).toFixed(1) + '%';
                    }
                }
            });
            return tween;
        }

        if (mm) {
            mm.add('(min-width: 900px)', function () { build(); return function () { if (B.ST) B.ST.refresh(); }; });
        } else if (window.matchMedia('(min-width: 900px)').matches) {
            build();
        }

        window.addEventListener('resize', B.debounce(function () {
            if (bar && window.innerWidth < 900) {
                var d = track.scrollWidth - track.clientWidth;
                bar.style.width = (d > 0 ? B.clamp(track.scrollLeft / d, 0, 1) * 100 : 100).toFixed(1) + '%';
            }
        }, 160));

        track.addEventListener('scroll', function () {
            if (window.innerWidth >= 900 || !bar) return;
            var d = track.scrollWidth - track.clientWidth;
            bar.style.width = (d > 0 ? B.clamp(track.scrollLeft / d, 0, 1) * 100 : 100).toFixed(1) + '%';
        }, { passive: true });
    };

    /* ===================================================================== *
     * 14. MENU « SCONTRINO » — onglets, filtres, impression, aperçu
     * ===================================================================== */
    B.initMenu = function () {
        var section = B.$('.menu');
        if (!section) return;

        var tabs   = B.$$('.pill[data-tab]', section);
        var thumb  = B.$('[data-pills-thumb]', section);
        var panels = B.$$('.panel', section);
        var chips  = B.$$('.chip[data-filter]', section);
        var peek   = B.$('[data-menu-peek]', section);
        var activeFilter = 'tutti';

        function moveThumb(tab) {
            if (!thumb || !tab) return;
            thumb.style.width = tab.offsetWidth + 'px';
            thumb.style.transform = 'translateX(' + tab.offsetLeft + 'px)';
        }

        function applyFilter(panel) {
            if (!panel) return;
            var rows = B.$$('.rrow', panel);
            var shown = 0;
            rows.forEach(function (row) {
                var tags = (row.getAttribute('data-tags') || '').split(' ');
                var ok = activeFilter === 'tutti' || tags.indexOf(activeFilter) !== -1;
                row.classList.toggle('is-hidden', !ok);
                if (ok) shown++;
            });
            var empty = B.$('[data-empty]', panel);
            if (empty) empty.hidden = shown > 0;
        }

        function selectTab(key, focusIt) {
            var tab = tabs.filter(function (t) { return t.getAttribute('data-tab') === key; })[0];
            if (!tab) return;
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
            });
            panels.forEach(function (p) {
                var on = p.getAttribute('data-panel') === key;
                if (on) { p.hidden = false; } else { p.hidden = true; }
                p.classList.remove('is-printing');
            });
            var panel = B.$('.panel[data-panel="' + key + '"]', section);
            applyFilter(panel);
            moveThumb(tab);
            if (focusIt) tab.focus();

            if (!B.reduced && panel) {
                void panel.offsetWidth;
                panel.classList.add('is-printing');
                setTimeout(function () { panel.classList.remove('is-printing'); }, 1400);
            }
            if (B.ST) setTimeout(function () { B.ST.refresh(); }, 60);
            B.emit('menu:tab', key);
        }

        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () { selectTab(tab.getAttribute('data-tab')); });
            tab.addEventListener('keydown', function (e) {
                var k = e.key, n = tabs.length, next = null;
                if (k === 'ArrowRight' || k === 'ArrowDown') next = (i + 1) % n;
                else if (k === 'ArrowLeft' || k === 'ArrowUp') next = (i - 1 + n) % n;
                else if (k === 'Home') next = 0;
                else if (k === 'End') next = n - 1;
                if (next === null) return;
                e.preventDefault();
                selectTab(tabs[next].getAttribute('data-tab'), true);
            });
            tab.addEventListener('mouseenter', function () { moveThumb(tab); });
        });

        var pillWrap = B.$('[data-tabs]', section);
        if (pillWrap) {
            pillWrap.addEventListener('mouseleave', function () {
                var cur = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0];
                moveThumb(cur);
            });
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                activeFilter = chip.getAttribute('data-filter');
                chips.forEach(function (c) {
                    c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
                });
                panels.forEach(function (p) { if (!p.hidden) applyFilter(p); });
            });
        });

        // État initial
        var firstTab = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0];
        if (firstTab) {
            moveThumb(firstTab);
            applyFilter(B.$('.panel[data-panel="' + firstTab.getAttribute('data-tab') + '"]', section));
        }
        window.addEventListener('load', function () { moveThumb(firstTab); });
        window.addEventListener('resize', B.debounce(function () {
            var cur = tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0];
            moveThumb(cur);
        }, 150));

        /* --- Aperçu du plat qui suit le curseur (desktop) --- */
        if (peek && !B.coarse && !B.reduced) {
            var px = 0, py = 0, tx = 0, ty = 0, vx = 0, shown = false, rafId = 0;
            function loop() {
                vx = B.lerp(vx, (tx - px) * 0.6, 0.12);
                px = B.lerp(px, tx, 0.16);
                py = B.lerp(py, ty, 0.16);
                peek.style.transform = 'translate3d(' + (px - 90).toFixed(1) + 'px,' + (py - 150).toFixed(1) +
                    'px,0) scale(' + (shown ? 1 : 0.8) + ') rotate(' + B.clamp(vx, -22, 22).toFixed(2) + 'deg)';
                rafId = requestAnimationFrame(loop);
            }

            section.addEventListener('pointermove', function (e) {
                if (e.pointerType === 'touch') return;
                tx = e.clientX; ty = e.clientY;
                if (!rafId) loop();
            }, { passive: true });

            B.$$('.rrow', section).forEach(function (row) {
                row.addEventListener('pointerenter', function (e) {
                    if (e.pointerType === 'touch') return;
                    var src = row.getAttribute('data-cutsrc');
                    if (!src) { shown = false; peek.classList.remove('is-on'); return; }
                    if (peek.getAttribute('data-src') !== src) {
                        peek.setAttribute('data-src', src);
                        peek.innerHTML = '<img src="' + B.escape(src) + '" alt="" width="1200" height="1200">';
                    }
                    shown = true;
                    peek.classList.add('is-on');
                });
                row.addEventListener('pointerleave', function () {
                    shown = false;
                    peek.classList.remove('is-on');
                });
            });
        }
    };

    /* ===================================================================== *
     * 15. COMPTEURS (Chi siamo)
     * ===================================================================== */
    B.initCounters = function () {
        var nodes = B.$$('[data-count]');
        if (!nodes.length) return;

        function write(node, v, sep) {
            var s = String(Math.round(v));
            if (sep) s = s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            node.textContent = s;
        }

        nodes.forEach(function (node) {
            var target = parseFloat(node.getAttribute('data-count')) || 0;
            var sep = node.getAttribute('data-sep') === '1';
            write(node, 0, sep);

            if (!B.gsap || !B.ST || B.reduced) { write(node, target, sep); return; }

            var obj = { v: 0 };
            B.ST.create({
                trigger: node, start: 'top 88%', once: true,
                onEnter: function () {
                    B.gsap.to(obj, {
                        v: target, duration: 1.5, ease: 'power2.out',
                        onUpdate: function () { write(node, obj.v, sep); },
                        onComplete: function () { write(node, target, sep); }
                    });
                }
            });
        });
    };

    /* ===================================================================== *
     * 16. BARRE MOBILE + NEWSLETTER
     * ===================================================================== */
    B.initMbar = function () {
        var mbar = B.$('[data-mbar]');
        var hero = B.$('.hero');
        if (!mbar || !hero) return;
        if (!('IntersectionObserver' in window)) { mbar.classList.add('is-on'); return; }
        new IntersectionObserver(function (entries) {
            mbar.classList.toggle('is-on', !entries[0].isIntersecting);
        }, { threshold: 0.06 }).observe(hero);
    };

    B.initNewsletter = function () {
        var form = B.$('[data-nl-form]');
        if (!form) return;
        var input = B.$('#nl-email', form);
        var err = B.$('[data-nl-err]', form);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var v = (input.value || '').trim();
            if (!/^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v)) {
                err.hidden = false;
                err.textContent = 'Email strana: ricontrolla.';
                input.focus();
                return;
            }
            err.hidden = true;
            input.value = '';
            B.toast('Sei dei nostri. Niente spam, promesso.', { variant: 'ok' });
        });
    };

    /* ===================================================================== *
     * 17. BOOT
     * ===================================================================== */
    /* Chaque brique démarre dans son coin : si une librairie CDN manque ou
       plante, le reste du site continue de fonctionner (et de vendre). */
    function safe(name, fn) {
        try { fn(); } catch (err) { console.error('[bomba] ' + name, err); }
    }

    function boot() {
        safe('core', B.initCore);
        safe('cursor', B.initCursor);
        safe('header', B.initHeader);
        safe('nav', B.initMobileNav);
        safe('anchors', B.initAnchors);
        safe('sweep', B.initSweep);
        safe('reveals', B.initReveals);
        safe('ticker', B.initTicker);
        safe('menu', B.initMenu);
        safe('counters', B.initCounters);
        safe('mbar', B.initMbar);
        safe('newsletter', B.initNewsletter);
        safe('best', B.initBest);

        // Modules compagnons (kinetic, spray, stickers, cart, combo, booking, gallery)
        ['cart', 'kinetic', 'spray', 'stickers', 'combo', 'booking', 'gallery'].forEach(function (key) {
            var mod = B[key];
            if (mod && typeof mod.init === 'function') {
                try { mod.init(); } catch (err) { console.error('[bomba] ' + key, err); }
            }
        });

        safe('hero', B.initHero);
        safe('preloader', B.initPreloader);

        if (B.ST) {
            window.addEventListener('load', function () { B.ST.refresh(); });
        }
        document.documentElement.classList.add('bomba-ready');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Si reduced-motion change en cours de session
    try {
        mqReduce.addEventListener('change', function () { window.location.reload(); });
    } catch (err) { /* anciens navigateurs */ }
})();
