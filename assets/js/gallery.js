/*!
 * BOMBA — gallery.js
 * Muro di polaroid : draggabili avec inertie sur desktop, pile « cards »
 * (Swiper) sur mobile, lightbox plein écran accessible (flèches, Échap,
 * focus piégé, compteur).
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    var wall, pola, shots, lb, lbImg, lbCap, lbCount, galN;
    var index = 0;
    var draggables = [];
    var swiper = null;
    var zTop = 40;
    var mqDesktop = null;
    var releaseTrap = null;
    var lastFocus = null;

    function pad(n) { return ('0' + n).slice(-2); }

    /* ================================================================= *
     * LIGHTBOX
     * ================================================================= */
    function paint() {
        var s = shots[index] || {};
        if (lbImg) {
            if (s.src) {
                lbImg.src = s.src;
                lbImg.alt = s.caption || '';
                lbImg.style.display = '';
            } else {
                lbImg.removeAttribute('src');
                lbImg.alt = s.caption || '';
                lbImg.style.display = 'none';
            }
        }
        if (lbCap) lbCap.textContent = s.caption || '';
        if (lbCount) lbCount.textContent = pad(index + 1) + '/' + pad(shots.length);
        if (galN) galN.textContent = pad(index + 1);
    }

    function open(i) {
        if (!lb) return;
        index = B.clamp(i, 0, shots.length - 1);
        lastFocus = document.activeElement;
        lb.hidden = false;
        paint();
        B.lockScroll(true);
        releaseTrap = B.trapFocus(lb);
        setTimeout(function () {
            var close = B.$('[data-lb-close]', lb);
            if (close) close.focus();
        }, 40);
    }

    function close() {
        if (!lb || lb.hidden) return;
        lb.hidden = true;
        B.lockScroll(false);
        if (releaseTrap) releaseTrap();
        releaseTrap = null;
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    function step(delta) {
        index = (index + delta + shots.length) % shots.length;
        paint();
        if (lbImg && !B.reduced && B.gsap) {
            B.gsap.fromTo(lbImg, { opacity: 0, x: delta * 40 }, { opacity: 1, x: 0, duration: 0.34, ease: 'expo.out' });
        }
    }

    /* ================================================================= *
     * DESKTOP — drag + inertie
     * ================================================================= */
    function initDrag() {
        if (!window.Draggable || !B.gsap || B.reduced) { enableStaticClicks(); return; }
        var bounds = B.$('[data-wall-in]') || wall;

        draggables = window.Draggable.create(pola, {
            type: 'x,y',
            inertia: !!window.InertiaPlugin,
            bounds: bounds,
            edgeResistance: 0.72,
            dragResistance: 0.03,
            throwResistance: 1800,
            zIndexBoost: false,
            cursor: 'grab',
            activeCursor: 'grabbing',
            allowNativeClickScroll: false,
            minimumMovement: 4,
            onPress: function () {
                var el = this.target;
                zTop += 1;
                el.style.zIndex = String(zTop);
                el.classList.add('is-front');
                if (galN) {
                    var i = pola.indexOf(el);
                    galN.textContent = pad(i + 1);
                }
            },
            onDragStart: function () { this.target.classList.add('is-dragging'); },
            onDragEnd: function () { this.target.classList.remove('is-dragging'); },
            onRelease: function () { this.target.classList.remove('is-dragging'); }
        });

        enablePointerClicks();
    }

    function enablePointerClicks() {
        pola.forEach(function (el, i) {
            var btn = B.$('.pola__btn', el);
            if (!btn || btn.dataset.bound === '1') return;
            btn.dataset.bound = '1';
            var down = null;
            el.addEventListener('pointerdown', function (e) { down = { x: e.clientX, y: e.clientY }; }, true);
            btn.addEventListener('click', function (e) {
                if (down && Math.sqrt(Math.pow(e.clientX - down.x, 2) + Math.pow(e.clientY - down.y, 2)) > 9) return;
                e.preventDefault();
                open(i);
            });
        });
    }

    /** Sans Draggable : les photos restent clouées, le clic ouvre quand même. */
    function enableStaticClicks() { enablePointerClicks(); }

    function destroyDrag() {
        draggables.forEach(function (d) { if (d && d.kill) d.kill(); });
        draggables = [];
        pola.forEach(function (el) {
            el.classList.remove('is-dragging');
            if (B.gsap) B.gsap.set(el, { clearProps: 'transform' });
        });
    }

    /* ================================================================= *
     * MOBILE — Swiper en mode « cards »
     * ================================================================= */
    function initSwiper() {
        if (!window.Swiper) { enableStaticClicks(); return; }
        try {
            swiper = new window.Swiper(wall, {
                effect: 'cards',
                grabCursor: true,
                cardsEffect: { slideShadows: false, perSlideRotate: 3.2, perSlideOffset: 9 },
                on: {
                    slideChange: function () {
                        index = this.activeIndex;
                        if (galN) galN.textContent = pad(index + 1);
                    }
                }
            });
        } catch (err) {
            console.warn('[bomba] Swiper indisponible', err);
            swiper = null;
        }
        enablePointerClicks();
    }

    function destroySwiper() {
        if (swiper) { try { swiper.destroy(true, true); } catch (e) {} swiper = null; }
    }

    /* ================================================================= *
     * INIT
     * ================================================================= */
    function init() {
        wall = B.$('[data-wall]');
        if (!wall) return;
        pola = B.$$('.pola', wall);
        if (!pola.length) return;

        shots = pola.map(function (el) {
            return { src: el.getAttribute('data-src') || '', caption: el.getAttribute('data-caption') || '' };
        });

        lb = B.$('[data-lb]');
        lbImg = B.$('[data-lb-img]');
        lbCap = B.$('[data-lb-cap]');
        lbCount = B.$('[data-lb-count]');
        galN = B.$('[data-gal-n]');

        if (lb) {
            B.$$('[data-lb-close]', lb).forEach(function (b) { b.addEventListener('click', close); });
            var prev = B.$('[data-lb-prev]', lb), next = B.$('[data-lb-next]', lb);
            if (prev) prev.addEventListener('click', function () { step(-1); });
            if (next) next.addEventListener('click', function () { step(1); });

            lb.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { e.preventDefault(); close(); }
                else if (e.key === 'ArrowLeft') { e.preventDefault(); step(-1); }
                else if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
            });

            // Clic sur le fond rouge = fermer
            lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

            // Swipe sur la lightbox (tactile)
            var sx = 0;
            lb.addEventListener('touchstart', function (e) { sx = (e.touches[0] || {}).clientX || 0; }, { passive: true });
            lb.addEventListener('touchend', function (e) {
                var ex = (e.changedTouches[0] || {}).clientX || 0;
                var d = ex - sx;
                if (Math.abs(d) > 52) step(d < 0 ? 1 : -1);
            }, { passive: true });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lb && !lb.hidden) close();
        });

        mqDesktop = window.matchMedia('(min-width: 900px)');

        function apply() {
            destroyDrag();
            destroySwiper();
            if (mqDesktop.matches) initDrag();
            else initSwiper();
        }
        apply();

        var onChange = function () { apply(); };
        if (mqDesktop.addEventListener) mqDesktop.addEventListener('change', onChange);
        else if (mqDesktop.addListener) mqDesktop.addListener(onChange);

        window.addEventListener('resize', B.debounce(function () {
            if (swiper) { try { swiper.update(); } catch (e) {} }
        }, 240));

        // Apparition en rebond, une par une
        if (B.gsap && B.ST && !B.reduced) {
            B.gsap.set(pola, { opacity: 0, scale: 0.82 });
            B.ST.create({
                trigger: wall,
                start: 'top 84%',
                once: true,
                onEnter: function () {
                    B.gsap.to(pola, {
                        opacity: 1, scale: 1, duration: 0.62,
                        ease: 'back.out(1.7)', stagger: 0.05,
                        clearProps: 'scale'
                    });
                }
            });
        }
    }

    B.gallery = { init: init, open: function (i) { open(i); } };
})();
