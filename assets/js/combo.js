/*!
 * BOMBA — combo.js
 * Configurateur « Crea il tuo combo » : 3 étapes, cartes sticker cliquables,
 * le plat détouré vole vers le plateau où les éléments s'empilent en rebond,
 * prix recalculé en direct, ajout au panier en un seul article.
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    var CFG = null;
    var chosen = { main: null, fried: null, drink: null };
    var els = {};

    function init() {
        var root = B.$('.combo');
        CFG = B.cfg.combo;
        if (!root || !CFG) return;

        els.root   = root;
        els.stack  = B.$('[data-tray-stack]', root);
        els.empty  = B.$('[data-tray-empty]', root);
        els.was    = B.$('[data-combo-was]', root);
        els.now    = B.$('[data-combo-now]', root);
        els.save   = B.$('[data-combo-save]', root);
        els.live   = B.$('[data-combo-live]', root);
        els.add    = B.$('[data-combo-add]', root);
        els.missing= B.$('[data-combo-missing]', root);

        var steps = (CFG.steps || []).map(function (s) { return s.key; });

        B.$$('input[type="radio"][data-step-key]', root).forEach(function (radio) {
            radio.addEventListener('change', function () {
                var key = radio.getAttribute('data-step-key');
                var id = radio.value;
                var prev = chosen[key];
                chosen[key] = id;

                // Le plateau est mis à jour tout de suite : l'état ne dépend
                // jamais d'une animation (si GSAP manque, tout reste juste).
                paintTray();
                bounceLast();

                var opt = radio.closest('.opt');
                if (opt && !B.reduced && B.gsap) {
                    var box = B.$('.opt__box', opt);
                    if (box) {
                        B.gsap.fromTo(box, { scaleX: 1.09, scaleY: 0.91 },
                            { scaleX: 1, scaleY: 1, duration: 0.6, ease: 'elastic.out(1, 0.42)' });
                    }
                    var art = B.$('.opt__art', opt);
                    if (art) fly(art, key, prev === id);
                }
                update(steps);
            });
        });

        if (els.add) {
            els.add.addEventListener('click', function () {
                var ids = steps.map(function (k) { return chosen[k]; });
                if (ids.some(function (v) { return !v; })) return;
                var ok = B.cart && B.cart.add ? B.cart.add('combo:' + ids.join('|'), 1) : false;
                if (ok && B.gsap && !B.reduced) {
                    B.gsap.fromTo(els.add, { scaleX: 1.1, scaleY: 0.9 },
                        { scaleX: 1, scaleY: 1, duration: 0.55, ease: 'elastic.out(1, 0.42)' });
                }
            });
        }

        update(steps);
    }

    /* ------------------------------------------------------------------ *
     * Vol du détouré vers le plateau
     * ------------------------------------------------------------------ */
    /** Vol purement décoratif du détouré vers le plateau (aucun état). */
    function fly(artEl, key, same) {
        var src = artEl.getBoundingClientRect();
        var dst = els.stack ? els.stack.getBoundingClientRect() : null;
        var item = B.item(chosen[key]);
        var cutSrc = (item && B.cutouts[item.cutout]) || (item ? B.cutouts[item.id] : null);
        if (!dst || !cutSrc) { return; }

        var ghost = document.createElement('img');
        ghost.src = cutSrc;
        ghost.alt = '';
        ghost.setAttribute('aria-hidden', 'true');
        ghost.style.cssText = 'position:fixed;z-index:95;pointer-events:none;width:' +
            Math.round(src.width) + 'px;left:' + Math.round(src.left) + 'px;top:' + Math.round(src.top) + 'px;' +
            'filter:drop-shadow(6px 8px 0 rgba(0,0,0,.4))';
        document.body.appendChild(ghost);

        var targetW = Math.max(56, dst.width * 0.56);
        var index = ['main', 'fried', 'drink'].indexOf(key);
        var tx = dst.left + dst.width / 2 - targetW / 2;
        var ty = dst.top + dst.height - targetW * 0.78 - index * (targetW * 0.24);

        B.gsap.to(ghost, {
            left: tx, top: ty, width: targetW, rotation: B.rnd(-16, 16),
            duration: 0.62, ease: 'power2.in',
            onComplete: function () { ghost.remove(); }
        });
    }

    function bounceLast() {
        if (!els.stack || B.reduced || !B.gsap) return;
        var items = B.$$('.tray__item', els.stack);
        var last = items[items.length - 1];
        if (!last) return;
        B.gsap.fromTo(last, { scaleX: 1.18, scaleY: 0.82 },
            { scaleX: 1, scaleY: 1, duration: 0.72, ease: 'elastic.out(1, 0.4)' });
    }

    /* ------------------------------------------------------------------ *
     * Plateau : empilement léger
     * ------------------------------------------------------------------ */
    function paintTray() {
        if (!els.stack) return;
        var keys = ['main', 'fried', 'drink'];
        var html = '';
        keys.forEach(function (k, i) {
            var id = chosen[k];
            if (!id) return;
            var item = B.item(id);
            if (!item) return;
            var src = B.cutouts[item.cutout] || B.cutouts[id] || '';
            if (!src) return;
            html += '<img class="tray__item" data-tray-item="' + i + '" src="' + B.escape(src) + '" alt="" ' +
                'width="1200" height="1200" style="--rot:' + (i * 7 - 7).toFixed(1) + 'deg;bottom:' +
                (6 + i * 26) + 'px;transform:translateX(-50%) rotate(' + (i * 7 - 7).toFixed(1) + 'deg)">';
        });
        els.stack.innerHTML = html;
        if (els.empty) els.empty.hidden = html !== '';
    }

    /* ------------------------------------------------------------------ *
     * Prix en direct + état du bouton
     * ------------------------------------------------------------------ */
    function update(steps) {
        var missingKeys = (steps || Object.keys(chosen)).filter(function (k) { return !chosen[k]; });
        var sum = 0;
        (steps || Object.keys(chosen)).forEach(function (k) {
            var it = chosen[k] ? B.item(chosen[k]) : null;
            if (it) sum += it.price;
        });
        sum = Math.round(sum * 100) / 100;

        var complete = missingKeys.length === 0;
        var discount = Number(CFG.discount) || 0;
        var price = complete ? Math.round((sum - Math.min(discount, Math.max(0, sum - 0.5))) * 100) / 100 : 0;
        var saved = complete ? Math.round(Math.min(discount, sum) * 100) / 100 : 0;

        if (els.was) {
            els.was.textContent = complete ? B.money(sum) : '—';
            els.was.style.visibility = complete ? 'visible' : 'hidden';
        }
        if (els.now) els.now.textContent = complete ? B.money(price) : B.money(0);
        if (els.save) {
            els.save.hidden = !complete;
            els.save.textContent = 'RISPARMI ' + B.money(saved);
        }
        if (els.live) {
            els.live.textContent = complete
                ? 'Combo completo: ' + B.money(price) + ' invece di ' + B.money(sum) + '.'
                : 'Combo incompleto.';
        }
        if (els.add) els.add.disabled = !complete;

        if (els.missing) {
            if (complete) {
                els.missing.hidden = true;
            } else {
                els.missing.hidden = false;
                var labels = (CFG.steps || []).filter(function (s) { return missingKeys.indexOf(s.key) !== -1; })
                    .map(function (s) { return s.missing || s.title; });
                els.missing.textContent = 'Manca ancora: ' + labels.join(', ');
            }
        }
    }

    B.combo = { init: init };
})();
