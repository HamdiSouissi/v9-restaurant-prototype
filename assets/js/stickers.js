/*!
 * BOMBA — stickers.js
 * Chute physique des stickers du footer (Matter.js) : 12 à 16 stickers tombent,
 * rebondissent et s'empilent sur le sol. On peut les attraper et les lancer.
 *
 *  · souris  → Matter.MouseConstraint (prise + lancer avec inertie)
 *  · doigt   → GSAP Draggable + InertiaPlugin (le scroll de page reste libre)
 *  · reduced-motion / Matter absent → stickers posés statiquement (CSS)
 *  · moteur en pause dès que le footer sort de l'écran
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    function init() {
        var arena = B.$('[data-sticker-arena]');
        var stage = B.$('[data-sticker-stage]');
        if (!arena || !stage) return;

        var els = B.$$('.ftr__stk', stage);
        if (!els.length) return;

        if (B.reduced || !window.Matter || !window.Matter.Engine) {
            // Statiques : le CSS les pose déjà en désordre.
            return;
        }

        var Engine = window.Matter.Engine,
            World = window.Matter.World,
            Bodies = window.Matter.Bodies,
            Body = window.Matter.Body,
            Composite = window.Matter.Composite,
            Mouse = window.Matter.Mouse,
            MouseConstraint = window.Matter.MouseConstraint,
            Runner = null;

        var engine = null, mouseConstraint = null, mouse = null;
        var bodies = [];
        var statics = [];
        var started = false, live = false, rafId = 0, lastT = 0;
        var W = 0, H = 0;

        function size(el) {
            var r = el.getBoundingClientRect();
            return { w: Math.max(24, r.width), h: Math.max(24, r.height) };
        }

        function buildStatics() {
            statics.forEach(function (s) { Composite.remove(engine.world, s); });
            statics = [
                Bodies.rectangle(W / 2, H + 40, W * 3, 80, { isStatic: true, friction: 0.9, restitution: 0.16, label: 'floor' }),
                Bodies.rectangle(-40, H / 2, 80, H * 3, { isStatic: true, friction: 0, restitution: 0.5, label: 'wallL' }),
                Bodies.rectangle(W + 40, H / 2, 80, H * 3, { isStatic: true, friction: 0, restitution: 0.5, label: 'wallR' })
            ];
            World.add(engine.world, statics);
        }

        function build() {
            W = arena.clientWidth;
            H = arena.clientHeight;
            if (!W || !H) return false;

            if (!engine) {
                engine = Engine.create({ enableSleeping: false });
                engine.gravity.y = 1.05;
            }
            Composite.clear(engine.world, false);
            bodies = [];
            buildStatics();

            els.forEach(function (el, i) {
                var s = size(el);
                var x = B.rnd(s.w, Math.max(s.w + 10, W - s.w));
                var y = -s.h - i * B.rnd(34, 74);
                var body = Bodies.rectangle(x, y, s.w * 0.86, s.h * 0.86, {
                    restitution: B.rnd(0.3, 0.52),
                    friction: 0.055,
                    frictionAir: 0.011,
                    density: 0.0016,
                    angle: B.rnd(-0.6, 0.6),
                    label: 'sticker-' + i
                });
                body.plugin = body.plugin || {};
                body.plugin.el = el;
                body.plugin.w = s.w;
                body.plugin.h = s.h;
                bodies.push(body);

                // Le moteur prend la main sur le placement : on retire le
                // positionnement CSS statique et la rotation « collée ».
                el.style.left = '0px';
                el.style.top = '0px';
                el.style.rotate = '0deg';
                el.style.margin = '0';
            });

            World.add(engine.world, bodies);
            return true;
        }

        function render() {
            for (var i = 0; i < bodies.length; i++) {
                var b = bodies[i];
                var el = b.plugin.el;
                var hw = b.plugin.w / 2, hh = b.plugin.h / 2;
                el.style.transform = 'translate3d(' + (b.position.x - hw).toFixed(1) + 'px,' +
                    (b.position.y - hh).toFixed(1) + 'px,0) rotate(' + b.angle.toFixed(3) + 'rad)';
            }
        }

        function loop(now) {
            rafId = 0;
            if (!live) return;
            var dt = Math.min(32, now - lastT || 16.7);
            lastT = now;
            Engine.update(engine, dt);
            render();
            rafId = requestAnimationFrame(loop);
        }

        function play() {
            if (!started || live) return;
            live = true;
            lastT = performance.now();
            if (!rafId) rafId = requestAnimationFrame(loop);
        }
        function pause() {
            live = false;
            if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
        }

        /* --- Interaction souris : attraper et lancer --------------------- */
        function attachMouse() {
            if (B.coarse || !Mouse || !MouseConstraint) return;
            try {
                mouse = Mouse.create(arena);
                mouse.pixelRatio = 1;
                mouseConstraint = MouseConstraint.create(engine, {
                    mouse: mouse,
                    constraint: {
                        stiffness: 0.19,
                        damping: 0.12,
                        render: { visible: false }
                    }
                });
                World.add(engine.world, mouseConstraint);

                arena.addEventListener('mousedown', function (e) {
                    if (e.target && e.target.classList && e.target.classList.contains('ftr__stk')) {
                        e.preventDefault();
                    }
                });
            } catch (err) {
                console.warn('[bomba] MouseConstraint indisponible', err);
            }
        }

        /* --- Interaction tactile : Draggable + inertie ------------------- */
        function attachTouch() {
            if (!B.coarse || !B.gsap || !window.Draggable) return;
            pause();
            // On fige la simulation et on rend la main à Draggable.
            els.forEach(function (el, i) {
                var b = bodies[i];
                if (!b) return;
                var hw = b.plugin.w / 2, hh = b.plugin.h / 2;
                B.gsap.set(el, {
                    x: b.position.x - hw,
                    y: b.position.y - hh,
                    rotation: b.angle * 57.2958,
                    transformOrigin: '50% 50%'
                });
                el.style.transform = '';
                window.Draggable.create(el, {
                    type: 'x,y',
                    inertia: !!(window.InertiaPlugin),
                    bounds: arena,
                    edgeResistance: 0.72,
                    throwResistance: 2200,
                    dragResistance: 0.02,
                    onPress: function () { el.classList.add('is-dragging'); },
                    onRelease: function () { el.classList.remove('is-dragging'); },
                    onDragStart: function () { el.classList.add('is-dragging'); },
                    onDragEnd: function () { el.classList.remove('is-dragging'); }
                });
            });
            bodies = [];
            started = false;
        }

        /* --- Démarrage quand le footer entre ----------------------------- */
        function startOnce() {
            if (started) return;
            if (!build()) return;
            started = true;
            arena.classList.add('is-live');
            attachMouse();
            play();
            if (B.coarse) setTimeout(attachTouch, 2600);
        }

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                var e = entries[0];
                if (e.isIntersecting) {
                    startOnce();
                    if (started && !B.coarse) play();
                } else if (started) {
                    pause();
                }
            }, { threshold: 0.06 }).observe(arena);
        } else {
            startOnce();
        }

        document.addEventListener('visibilitychange', function () {
            if (!started) return;
            if (document.hidden || B.coarse) pause(); else play();
        });

        // Redimensionnement : on reconstruit le sol et les murs.
        var rebuild = B.debounce(function () {
            if (!started || B.coarse) return;
            var wasLive = live;
            pause();
            W = arena.clientWidth;
            H = arena.clientHeight;
            if (!W || !H) return;
            buildStatics();
            bodies.forEach(function (b) {
                if (b.position.y > H) Body.setPosition(b, { x: B.clamp(b.position.x, 20, W - 20), y: H - 40 });
                if (b.position.x < 0 || b.position.x > W) Body.setPosition(b, { x: B.clamp(b.position.x, 20, W - 20), y: b.position.y });
            });
            if (wasLive) play();
        }, 220);
        window.addEventListener('resize', rebuild);
    }

    B.stickers = { init: init };
})();
