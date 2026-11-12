/*!
 * BOMBA — cart.js
 * Sacchetto kraft : état + persistance localStorage, tiroir, bascule
 * Consegna/Asporto, frais, minimum, suppression annulable 5 s,
 * checkout 3 étapes → /api/order.php → scontrino qui s'imprime → suivi.
 * Tout est simulé, aucun paiement réel.
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});
    var KEY = 'bomba:cart:v2';

    var D = {};          // config livraison
    var state = { mode: 'delivery', items: [] };

    var els = {};
    var releaseTrap = null;
    var lastFocus = null;
    var coStep = 1;
    var coRelease = null;
    var lastOrder = null;

    /* ================================================================= *
     * ÉTAT
     * ================================================================= */
    function load() {
        try {
            var raw = localStorage.getItem(KEY);
            if (!raw) return;
            var parsed = JSON.parse(raw);
            if (parsed && Array.isArray(parsed.items)) {
                state.mode = parsed.mode === 'pickup' ? 'pickup' : 'delivery';
                state.items = parsed.items.filter(function (it) {
                    return it && it.id && Number(it.qty) > 0;
                }).map(function (it) {
                    return {
                        id: String(it.id), name: String(it.name || ''), note: String(it.note || ''),
                        price: Number(it.price) || 0, qty: B.clamp(parseInt(it.qty, 10) || 1, 1, 99),
                        cutout: String(it.cutout || '')
                    };
                });
            }
        } catch (err) { /* stockage indisponible : on repart de zéro */ }
    }

    function save() {
        try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (err) {}
    }

    function resolve(id, qty) {
        // Combo : « combo:main|fried|drink »
        if (String(id).indexOf('combo:') === 0) {
            var ids = String(id).slice(6).split('|');
            if (ids.length !== 3) return null;
            var sum = 0, names = [], cut = '';
            for (var i = 0; i < ids.length; i++) {
                var it = B.item(ids[i]);
                if (!it) return null;
                sum += it.price;
                names.push(it.name);
                if (i === 0) cut = it.cutout;
            }
            var discount = Number(B.cfg.combo && B.cfg.combo.discount) || 0;
            var price = Math.round((sum - Math.min(discount, Math.max(0, sum - 0.5))) * 100) / 100;
            return {
                id: id, name: 'Combo ' + ((B.cfg.brand && B.cfg.brand.name) || 'BOMBA'),
                note: names.join(' + '), price: price, qty: qty || 1, cutout: cut,
                combo: ids, saved: Math.round(Math.min(discount, sum) * 100) / 100
            };
        }
        var item = B.item(id);
        if (!item) return null;
        return {
            id: item.id, name: item.name, note: item.cat_label || '', price: item.price,
            qty: qty || 1, cutout: item.cutout || ''
        };
    }

    function count() {
        return state.items.reduce(function (n, it) { return n + it.qty; }, 0);
    }

    function subtotal() {
        return Math.round(state.items.reduce(function (s, it) { return s + it.price * it.qty; }, 0) * 100) / 100;
    }

    function fee() {
        if (state.mode !== 'delivery') return 0;
        var sub = subtotal();
        if (sub <= 0) return 0;
        return sub >= Number(D.free_from) ? 0 : Number(D.fee);
    }

    function total() { return Math.round((subtotal() + fee()) * 100) / 100; }

    /* ================================================================= *
     * RENDU
     * ================================================================= */
    function cutImg(slug, name) {
        var src = B.cutouts[slug] || '';
        if (src) return '<img src="' + B.escape(src) + '" alt="" width="1200" height="1200" loading="lazy">';
        return '<span class="cutout__fallback-name" aria-hidden="true">' + B.escape(name || '') + '</span>';
    }

    function renderCounts() {
        var n = count();
        B.$$('[data-cart-count]').forEach(function (el) { el.textContent = String(n); });
        var bag = B.$('[data-bag-open]');
        if (bag) bag.setAttribute('aria-label', 'Apri il sacchetto — articoli nel sacchetto: ' + n);
    }

    function bumpBag() {
        if (B.reduced) return;
        var bag = B.$('.bag');
        var badges = B.$$('[data-cart-count]');
        if (bag) { bag.classList.remove('is-bump'); void bag.offsetWidth; bag.classList.add('is-bump'); }
        badges.forEach(function (b) { b.classList.remove('is-pop'); void b.offsetWidth; b.classList.add('is-pop'); });
    }

    function renderList() {
        var list = els.list, empty = els.empty;
        if (!list) return;

        if (!state.items.length) {
            list.innerHTML = '';
            if (empty) empty.hidden = false;
            list.hidden = true;
        } else {
            if (empty) empty.hidden = true;
            list.hidden = false;
            list.innerHTML = state.items.map(function (it, i) {
                return '' +
                '<li class="ci is-in" data-ci="' + i + '">' +
                    '<span class="ci__thumb">' + cutImg(it.cutout, it.name) + '</span>' +
                    '<span class="ci__body">' +
                        '<span class="ci__top">' +
                            '<span class="ci__name">' + B.escape(it.name) + '</span>' +
                            '<span class="ci__price">' + B.money(it.price * it.qty) + '</span>' +
                        '</span>' +
                        (it.note ? '<span class="ci__note">' + B.escape(it.note) + '</span>' : '') +
                        '<span class="ci__ctrl">' +
                            '<span class="ci__qty qty is-active">' +
                                '<button class="qty__btn qty__minus" type="button" data-ci-dec="' + i + '" aria-label="Togli un ' + B.escape(it.name) + '">' +
                                    '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#ico-minus"></use></svg></button>' +
                                '<span class="qty__n">' + it.qty + '</span>' +
                                '<button class="qty__btn qty__plus" type="button" data-ci-inc="' + i + '" aria-label="Aggiungi un ' + B.escape(it.name) + '">' +
                                    '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#ico-plus"></use></svg></button>' +
                            '</span>' +
                            '<button class="ci__rm" type="button" data-ci-rm="' + i + '" aria-label="Elimina ' + B.escape(it.name) + ' dal sacchetto">' +
                                '<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#ico-trash"></use></svg><span>Togli</span></button>' +
                        '</span>' +
                    '</span>' +
                '</li>';
            }).join('');
        }
        renderTotals();
    }

    function renderTotals() {
        var sub = subtotal(), f = fee(), tot = total();
        if (els.subtotal) els.subtotal.textContent = B.money(sub);
        if (els.feeRow) els.feeRow.hidden = state.mode !== 'delivery';
        if (els.fee) els.fee.textContent = state.mode === 'delivery' ? (f === 0 ? 'Gratis' : B.money(f)) : '—';
        if (els.total) els.total.textContent = B.money(tot);

        // Barre « Ancora € X e la consegna è gratis »
        var freeFrom = Number(D.free_from) || 0;
        if (els.free) {
            var show = state.mode === 'delivery' && sub > 0 && sub < freeFrom;
            els.free.hidden = !(state.mode === 'delivery' && sub > 0);
            if (els.freeTxt) {
                els.freeTxt.textContent = show
                    ? 'Ancora ' + B.money(Math.round((freeFrom - sub) * 100) / 100) + " e la consegna è gratis"
                    : 'Consegna gratis. Grande.';
            }
            if (els.freeBar) els.freeBar.style.width = B.clamp((sub / freeFrom) * 100, 0, 100).toFixed(1) + '%';
            els.free.classList.toggle('is-done', !show);
        }

        // Minimum en livraison
        var min = Number(D.min_order) || 0;
        var belowMin = state.mode === 'delivery' && sub > 0 && sub < min;
        if (els.min) {
            els.min.hidden = !belowMin;
            if (belowMin) {
                els.min.textContent = 'Consegna da ' + B.money(min) + ' in su. Aggiungi ancora ' +
                    B.money(Math.round((min - sub) * 100) / 100) + '.';
            }
        }

        if (els.checkoutBtn) {
            els.checkoutBtn.disabled = state.items.length === 0 || belowMin;
        }
    }

    function render() { renderCounts(); renderList(); }

    /* ================================================================= *
     * TIROIR
     * ================================================================= */
    function open() {
        if (!els.drawer) return;
        lastFocus = document.activeElement;
        els.drawer.hidden = false;
        els.checkoutRoot.hidden = true;
        requestAnimationFrame(function () { els.drawer.classList.add('is-open'); });
        B.lockScroll(true);
        releaseTrap = B.trapFocus(els.panel);
        render();
        setTimeout(function () {
            var f = els.panel.querySelector('.seg__btn.is-on') || els.panel.querySelector('button');
            if (f) f.focus();
        }, 300);
    }

    function close() {
        if (!els.drawer || els.drawer.hidden) return;
        els.drawer.classList.remove('is-open');
        B.lockScroll(false);
        if (releaseTrap) releaseTrap();
        releaseTrap = null;
        setTimeout(function () { els.drawer.hidden = true; }, 500);
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    /* ================================================================= *
     * AJOUTS / QUANTITÉS
     * ================================================================= */
    function add(id, qty, opts) {
        opts = opts || {};
        qty = B.clamp(parseInt(qty, 10) || 1, 1, 99);
        var resolved = resolve(id, qty);
        if (!resolved) {
            B.toast('Ops, questo piatto non c\'è più.', { variant: 'err' });
            return false;
        }
        var found = null;
        state.items.forEach(function (it) { if (it.id === resolved.id) found = it; });
        if (found) {
            found.qty = B.clamp(found.qty + resolved.qty, 1, 99);
        } else {
            state.items.push(resolved);
        }
        save();
        render();
        bumpBag();
        syncQtyWidgets();

        if (!opts.silent) {
            var label = resolved.id.indexOf('combo:') === 0 ? 'Combo nel sacchetto!' : resolved.name + ' nel sacchetto!';
            B.toast(label, { variant: 'ok' });
        }
        B.emit('cart:change', { count: count(), total: total() });
        return true;
    }

    function setQty(index, qty) {
        var it = state.items[index];
        if (!it) return;
        it.qty = B.clamp(qty, 0, 99);
        if (it.qty === 0) removeAt(index);
        else { save(); render(); syncQtyWidgets(); B.emit('cart:change', { count: count(), total: total() }); }
    }

    function removeAt(index) {
        var it = state.items[index];
        if (!it) return;
        var snapshot = { item: it, index: index };
        state.items.splice(index, 1);
        save(); render(); syncQtyWidgets();
        B.emit('cart:change', { count: count(), total: total() });

        B.toast('Tolto dal sacchetto', {
            action: 'Annulla',
            duration: 5000,
            onAction: function () {
                var at = B.clamp(snapshot.index, 0, state.items.length);
                state.items.splice(at, 0, snapshot.item);
                save(); render(); syncQtyWidgets(); bumpBag();
                B.emit('cart:change', { count: count(), total: total() });
            }
        });
    }

    /** Remet les compteurs « – n + » du menu en phase avec le panier. */
    function syncQtyWidgets() {
        var perItem = {};
        state.items.forEach(function (it) {
            if (it.id.indexOf('combo:') === 0) return;
            perItem[it.id] = (perItem[it.id] || 0) + it.qty;
        });
        B.$$('[data-qty]').forEach(function (box) {
            var id = box.getAttribute('data-qty');
            var n = perItem[id] || 0;
            var label = B.$('[data-qty-n]', box);
            if (label) label.textContent = String(n);
            box.classList.toggle('is-active', n > 0);
            var minus = B.$('[data-dec]', box);
            if (minus) minus.tabIndex = n > 0 ? 0 : -1;
        });
    }

    /* ================================================================= *
     * CHECKOUT — 3 étapes
     * ================================================================= */
    /* --- Horaires d'ouverture : jamais de retrait frittura éteinte. --- */
    function toMin(hhmm) {
        var p = String(hhmm || '').trim().split(':');
        return (parseInt(p[0], 10) || 0) * 60 + (parseInt(p[1], 10) || 0);
    }
    function isoDow(d) { var n = d.getDay(); return n === 0 ? 7 : n; }
    function rangesFor(dow) {
        var day = null;
        (B.cfg.hours || []).forEach(function (h) { if (Number(h.dow) === Number(dow)) day = h; });
        if (!day || day.closed || !day.open) return [];
        return day.open.map(function (range) {
            var p = String(range).split(/[\u2013\u2014-]/);
            var a = toMin(p[0]), b = toMin(p[1]);
            if (b <= a) b += 1440;          // 18:30\u201302:00 déborde sur le lendemain
            return [a, b];
        });
    }
    function isOpenAt(d) {
        var mins = d.getHours() * 60 + d.getMinutes();
        var hit = rangesFor(isoDow(d)).some(function (r) { return mins >= r[0] && mins < r[1]; });
        if (hit) return true;
        var veille = new Date(d.getTime() - 86400000);
        var tard = mins + 1440;
        return rangesFor(isoDow(veille)).some(function (r) { return tard >= r[0] && tard < r[1]; });
    }
    function nextOpen(d) {
        for (var i = 5; i <= 1440 * 8; i += 5) {
            var t = new Date(d.getTime() + i * 60000);
            if (isOpenAt(t)) return t;
        }
        return null;
    }
    function hhmm(d) { return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }

    function bindPickupChips(host) {
        B.$$('[data-pickup]', host).forEach(function (btn) {
            btn.addEventListener('click', function () {
                B.$$('[data-pickup]', host).forEach(function (b) {
                    b.classList.toggle('is-on', b === btn);
                    b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                });
                if (els.pickupValue) els.pickupValue.value = btn.getAttribute('data-pickup');
            });
        });
    }

    function buildPickupSlots() {
        var host = els.pickupSlots;
        if (!host) return;
        var step = Math.max(5, Number(D.pickup_step_minutes) || 10);
        var now = new Date();
        var lead = new Date(now.getTime() + Math.max(10, step) * 60000);
        lead.setMinutes(Math.ceil(lead.getMinutes() / step) * step, 0, 0);

        var slots = [], t = new Date(lead.getTime());
        for (var i = 0; i < 60 && slots.length < 6; i++) {
            if (isOpenAt(t)) slots.push(new Date(t.getTime()));
            t = new Date(t.getTime() + step * 60000);
        }

        var openNow = isOpenAt(now);
        var html = '<button class="chip chip--solid is-on" type="button" data-pickup="asap" aria-pressed="true">' +
            (openNow ? 'Prima possibile' : 'Primo turno utile') + '</button>';
        slots.forEach(function (d) {
            html += '<button class="chip chip--solid" type="button" data-pickup="' + hhmm(d) +
                '" aria-pressed="false">' + hhmm(d) + '</button>';
        });
        if (!openNow) {
            var nx = nextOpen(now);
            html += '<span class="field__hint">' + (nx
                ? 'Siamo chiusi adesso. Si ricomincia alle ' + hhmm(nx) + '.'
                : 'Siamo chiusi adesso: gli orari sono in fondo alla pagina.') + '</span>';
        }
        host.innerHTML = html;
        if (els.pickupValue) els.pickupValue.value = 'asap';
        bindPickupChips(host);
    }

    function setMode(mode) {
        state.mode = mode === 'pickup' ? 'pickup' : 'delivery';
        save();
        if (els.checkoutRoot) els.checkoutRoot.setAttribute('data-mode', state.mode);
        B.$$('[data-cart-mode] .seg__btn').forEach(function (b) {
            var on = b.getAttribute('data-mode') === state.mode;
            b.classList.toggle('is-on', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        renderTotals();
    }

    function openCheckout() {
        if (!state.items.length) { B.toast('Il sacchetto è vuoto. Rimedia subito.'); return; }
        close();
        var root = els.checkoutRoot;
        if (!root) return;
        root.hidden = false;
        root.setAttribute('data-mode', state.mode);
        root.setAttribute('data-screen', 'form');
        if (els.panelForm) els.panelForm.hidden = false;
        if (els.panelDone) els.panelDone.hidden = true;
        coStep = 1;
        paintStep();
        buildPickupSlots();
        lastFocus = document.activeElement;
        requestAnimationFrame(function () { root.classList.add('is-open'); });
        B.lockScroll(true);
        if (coRelease) coRelease();
        coRelease = B.trapFocus(root);
        setTimeout(function () {
            var f = root.querySelector('#co-name');
            if (f) f.focus();
        }, 320);
    }

    function closeCheckout() {
        var root = els.checkoutRoot;
        if (!root || root.hidden) return;
        root.classList.remove('is-open');
        B.lockScroll(false);
        if (coRelease) coRelease();
        coRelease = null;
        setTimeout(function () { root.hidden = true; }, 420);
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    function paintStep() {
        B.$$('[data-co-panel]').forEach(function (p) {
            p.hidden = p.getAttribute('data-co-panel') !== String(coStep);
        });
        B.$$('[data-co-step-dot]').forEach(function (d) {
            var n = parseInt(d.getAttribute('data-co-step-dot'), 10);
            d.classList.toggle('is-on', n === coStep);
            d.classList.toggle('is-done', n < coStep);
        });
        if (els.coBack) els.coBack.hidden = coStep === 1;
        if (els.coNext) {
            var lbl = B.$('span', els.coNext);
            if (lbl) lbl.textContent = coStep === 3 ? 'Conferma l\'ordine' : 'Continua';
        }
        if (coStep === 3) paintRecap();
    }

    function paintRecap() {
        var wrap = els.coRecap;
        if (!wrap) return;
        if (els.coRecapMode) els.coRecapMode.textContent = state.mode === 'delivery' ? 'Consegna' : 'Asporto';
        if (els.coRecapItems) {
            els.coRecapItems.innerHTML = state.items.map(function (it) {
                return '<li><span class="q">' + it.qty + '×</span><span class="n">' + B.escape(it.name) +
                    '</span><span class="d"></span><span class="p">' + B.money(it.price * it.qty) + '</span></li>';
            }).join('');
        }
        if (els.coRecapSub) els.coRecapSub.textContent = B.money(subtotal());
        if (els.coRecapFeeRow) els.coRecapFeeRow.hidden = state.mode !== 'delivery';
        if (els.coRecapFee) els.coRecapFee.textContent = fee() === 0 ? 'Gratis' : B.money(fee());
        if (els.coRecapTotal) els.coRecapTotal.textContent = B.money(total());

        var who = [];
        var g = function (id) { var n = B.$(id); return n ? n.value.trim() : ''; };
        if (g('#co-name')) who.push('<li>Nome — <strong>' + B.escape(g('#co-name')) + '</strong></li>');
        if (g('#co-phone')) who.push('<li>Telefono — <strong>' + B.escape(g('#co-phone')) + '</strong></li>');
        if (state.mode === 'delivery' && g('#co-address')) who.push('<li>Indirizzo — <strong>' + B.escape(g('#co-address')) + '</strong></li>');
        if (state.mode === 'delivery' && g('#co-note')) who.push('<li>Note rider — <strong>' + B.escape(g('#co-note')) + '</strong></li>');
        if (state.mode === 'pickup') {
            var pk = els.pickupValue ? els.pickupValue.value : 'asap';
            who.push('<li>Ritiro — <strong>' + B.escape(pk === 'asap' ? 'Prima possibile' : pk) + '</strong></li>');
        }
        var pay = B.$('input[name="payment"]:checked');
        if (pay) {
            var lbl = pay.closest('.pay__opt').querySelector('.pay__box span:last-child');
            who.push('<li>Pagamento — <strong>' + B.escape(lbl ? lbl.textContent : pay.value) + '</strong></li>');
        }
        if (els.coRecapWho) els.coRecapWho.innerHTML = who.join('');
    }

    /* --- validation --------------------------------------------------- */
    var RULES = {
        name: function (v) { return v.length >= 2 && /^[A-Za-zÀ-ÿ0-9'\-\.\s]+$/.test(v) ? '' : 'Ci serve un nome vero, dai.'; },
        phone: function (v) { return (v.replace(/\D+/g, '').length >= 9) ? '' : 'Ci serve un numero valido per chiamarti.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v) ? '' : 'Email strana: ricontrolla.'; },
        address: function (v) { return v.length >= 6 ? '' : 'Senza indirizzo il rider gira a vuoto.'; },
        check: function (v, input) { return input.checked ? '' : 'Serve il tuo ok per continuare.'; }
    };

    function validateField(input) {
        var rule = input.getAttribute('data-validate');
        if (!rule || !RULES[rule]) return true;
        if (input.type !== 'checkbox' && input.getAttribute('data-validate') === 'address' && state.mode !== 'delivery') return true;
        var field = input.closest('.field');
        var errBox = field ? (B.$('[data-err-for="' + input.id + '"]') || B.$('.field__err', field)) : null;
        var msg = RULES[rule]((input.value || '').trim(), input);
        if (field) {
            field.classList.toggle('is-bad', !!msg);
            field.classList.toggle('is-ok', !msg && (input.type === 'checkbox' ? input.checked : (input.value || '').trim() !== ''));
        }
        if (errBox) { errBox.hidden = !msg; errBox.textContent = msg; }
        input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        return !msg;
    }

    function validateStep(n) {
        var panel = B.$('[data-co-panel="' + n + '"]');
        if (!panel) return true;
        var inputs = B.$$('input[data-validate]', panel).filter(function (i) {
            if (i.getAttribute('data-validate') === 'address' && state.mode !== 'delivery') return false;
            return true;
        });
        var ok = true, firstBad = null;
        inputs.forEach(function (i) {
            var good = validateField(i);
            if (!good && !firstBad) firstBad = i;
            ok = ok && good;
        });
        if (!ok && firstBad) firstBad.focus();
        return ok;
    }

    /* --- envoi -------------------------------------------------------- */
    function submitOrder() {
        var g = function (id) { var n = B.$(id); return n ? n.value.trim() : ''; };
        var pay = B.$('input[name="payment"]:checked');
        var pickup = els.pickupValue ? els.pickupValue.value : 'asap';

        var payload = {
            name: g('#co-name'),
            phone: g('#co-phone'),
            email: g('#co-email'),
            mode: state.mode,
            address: state.mode === 'delivery' ? g('#co-address') : '',
            rider_note: state.mode === 'delivery' ? g('#co-note') : '',
            pickup_time: state.mode === 'pickup' ? pickup : '',
            payment: pay ? pay.value : 'carta',
            items: state.items.map(function (it) { return { id: it.id, qty: it.qty }; })
        };

        if (els.coNext) { els.coNext.disabled = true; var s = B.$('span', els.coNext); if (s) s.textContent = 'In cucina…'; }

        fetch('api/order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Risposta illeggibile.' }; }); })
        .then(function (data) {
            if (!data || !data.ok) {
                var msg = (data && data.error) || 'Ops, qualcosa si è bruciato. Riprova.';
                showCoError(msg);
                if (data && data.field) {
                    var map = { name: '#co-name', phone: '#co-phone', email: '#co-email', address: '#co-address', payment: null };
                    if (data.field === 'items') coStep = 1;
                    if (map[data.field]) { var f = B.$(map[data.field]); if (f) f.focus(); }
                    paintStep();
                }
                return;
            }
            lastOrder = data;
            showDone(data);
        })
        .catch(function () {
            showCoError('Ops, qualcosa si è bruciato. Riprova.');
        })
        .then(function () {
            if (els.coNext) { els.coNext.disabled = false; var s2 = B.$('span', els.coNext); if (s2) s2.textContent = 'Conferma l\'ordine'; }
        });
    }

    function showCoError(msg) {
        if (!els.coErr) return;
        els.coErr.hidden = false;
        els.coErr.textContent = msg;
        setTimeout(function () { els.coErr.hidden = true; }, 6000);
    }

    /* ================================================================= *
     * SCONTRINO + SUIVI
     * ================================================================= */
    function showDone(data) {
        var root = els.checkoutRoot;
        root.setAttribute('data-screen', 'done');
        if (els.panelForm) els.panelForm.hidden = true;
        if (els.panelDone) els.panelDone.hidden = false;

        if (els.orderCode) els.orderCode.textContent = data.code;
        var when = new Date();
        if (els.orderWhen) {
            els.orderWhen.textContent = when.toLocaleDateString('it-IT', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' })
                .toUpperCase() + ' · ' + when.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        }
        if (els.orderBc) els.orderBc.textContent = '* ' + data.code.replace(/-/g, ' ') + ' *';

        if (els.scRows) {
            els.scRows.innerHTML = (data.lines || []).map(function (l) {
                return '<li><span class="q">' + l.qty + '×</span><span class="n">' + B.escape(l.name) +
                    '</span><span class="d"></span><span class="p">' + B.money(l.total) + '</span></li>';
            }).join('');
        }
        if (els.scSub) els.scSub.textContent = B.money(data.subtotal);
        if (els.scFeeRow) els.scFeeRow.hidden = data.mode !== 'delivery';
        if (els.scFee) els.scFee.textContent = Number(data.fee) === 0 ? 'Gratis' : B.money(data.fee);
        if (els.scTotal) els.scTotal.textContent = B.money(data.total);
        var payLabel = { carta: 'Carta', satispay: 'Satispay', contanti: 'Contanti' };
        if (els.scPay) els.scPay.textContent = (payLabel[data.payment] || data.payment) + ' (simulato)';

        // Le ticket s'imprime du haut de l'écran
        if (els.scontrino) {
            els.scontrino.classList.remove('is-printing');
            void els.scontrino.offsetWidth;
            if (!B.reduced) els.scontrino.classList.add('is-printing');
        }
        // Le tampon PAGATO s'écrase en rotation
        if (els.stamp) {
            els.stamp.classList.remove('is-stamped');
            setTimeout(function () { els.stamp.classList.add('is-stamped'); }, B.reduced ? 60 : 1500);
        }

        startTracking(data);

        // Le panier est consommé
        state.items = [];
        save();
        render();
        syncQtyWidgets();
        B.emit('cart:change', { count: 0, total: 0 });

        setTimeout(function () {
            var f = els.panelDone ? els.panelDone.querySelector('[data-checkout-close]') : null;
            if (f) f.focus();
        }, B.reduced ? 100 : 1700);
    }

    function startTracking(data) {
        var list = B.$$('[data-track-step]');
        if (!list.length) return;
        var mode = data.mode || state.mode;
        var steps = list.filter(function (li) {
            var m = li.getAttribute('data-track-mode');
            li.hidden = !(m === 'any' || m === mode);
            li.classList.remove('is-on', 'is-now');
            return !li.hidden;
        });

        var scooter = B.$('[data-track-scooter]');
        var eta = B.$('[data-track-eta]');
        if (eta) {
            eta.innerHTML = (mode === 'delivery' ? 'Arrivo stimato: ' : 'Pronto tra: ') +
                '<strong>' + B.escape(data.eta || (D.eta || '25–35 min')) + '</strong>';
        }
        if (scooter) {
            scooter.hidden = mode !== 'delivery';
            scooter.style.transform = 'translateX(0)';
        }

        if (B.reduced) {
            steps.forEach(function (li) { li.classList.add('is-on'); });
            return;
        }

        // Démo accélérée : chaque étape tient ~1,5 s.
        var i = 0;
        (function next() {
            if (i >= steps.length) return;
            steps.forEach(function (li, k) {
                li.classList.toggle('is-on', k <= i);
                li.classList.toggle('is-now', k === i);
            });
            if (scooter && mode === 'delivery') {
                var road = scooter.parentElement;
                var max = road ? Math.max(0, road.clientWidth - 40) : 0;
                scooter.style.transform = 'translateX(' + Math.round(max * (i / Math.max(1, steps.length - 1))) + 'px)';
            }
            i++;
            setTimeout(next, 1500);
        })();
    }

    /* ================================================================= *
     * INIT
     * ================================================================= */
    function init() {
        D = B.cfg.delivery || {};

        els.drawer      = B.$('[data-cart-drawer]');
        els.panel       = B.$('[data-cart-panel]');
        els.list        = B.$('[data-cart-list]');
        els.empty       = B.$('[data-cart-empty]');
        els.subtotal    = B.$('[data-cart-subtotal]');
        els.fee         = B.$('[data-cart-fee]');
        els.feeRow      = B.$('[data-cart-feerow]');
        els.total       = B.$('[data-cart-total]');
        els.free        = B.$('[data-cart-free]');
        els.freeTxt     = B.$('[data-cart-free-txt]');
        els.freeBar     = B.$('[data-cart-free-bar]');
        els.min         = B.$('[data-cart-min]');
        els.checkoutBtn = B.$('[data-cart-checkout]');
        els.checkoutRoot= B.$('[data-checkout]');
        els.panelForm   = B.$('[data-screen-panel="form"]');
        els.panelDone   = B.$('[data-screen-panel="done"]');
        els.coBack      = B.$('[data-co-back]');
        els.coNext      = B.$('[data-co-next]');
        els.coErr       = B.$('[data-checkout-err]');
        els.coRecap     = B.$('[data-co-recap]');
        els.coRecapMode = B.$('[data-co-recap-mode]');
        els.coRecapItems= B.$('[data-co-recap-items]');
        els.coRecapSub  = B.$('[data-co-recap-sub]');
        els.coRecapFee  = B.$('[data-co-recap-fee]');
        els.coRecapFeeRow = B.$('[data-co-recap-feerow]');
        els.coRecapTotal= B.$('[data-co-recap-total]');
        els.coRecapWho  = B.$('[data-co-recap-who]');
        els.pickupSlots = B.$('[data-pickup-slots]');
        els.pickupValue = B.$('[data-pickup-value]');
        els.scontrino   = B.$('[data-scontrino]');
        els.orderCode   = B.$('[data-order-code]');
        els.orderWhen   = B.$('[data-order-when]');
        els.orderBc     = B.$('[data-order-bc]');
        els.scRows      = B.$('[data-scontrino-rows]');
        els.scSub       = B.$('[data-scontrino-sub]');
        els.scFee       = B.$('[data-scontrino-fee]');
        els.scFeeRow    = B.$('[data-scontrino-feerow]');
        els.scTotal     = B.$('[data-scontrino-total]');
        els.scPay       = B.$('[data-scontrino-pay]');
        els.stamp       = B.$('[data-paid-stamp]');

        load();
        render();
        syncQtyWidgets();
        setMode(state.mode);

        /* --- ouverture / fermeture du tiroir --- */
        B.$$('[data-bag-open]').forEach(function (b) { b.addEventListener('click', open); });
        B.$$('[data-cart-close]').forEach(function (b) { b.addEventListener('click', close); });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (els.checkoutRoot && !els.checkoutRoot.hidden) { closeCheckout(); return; }
            if (els.drawer && !els.drawer.hidden) close();
        });
        var goMenu = B.$('[data-cart-gomenu]');
        if (goMenu) goMenu.addEventListener('click', function () { close(); B.scrollToId('#menu'); });
        if (els.checkoutBtn) els.checkoutBtn.addEventListener('click', openCheckout);

        /* --- bascule Consegna / Asporto --- */
        B.$$('[data-cart-mode] .seg__btn').forEach(function (b) {
            b.addEventListener('click', function () {
                setMode(b.getAttribute('data-mode'));
                if (!B.reduced && B.gsap) {
                    B.gsap.fromTo(b, { scaleX: 1.1, scaleY: 0.9 }, { scaleX: 1, scaleY: 1, duration: 0.5, ease: 'elastic.out(1, 0.45)' });
                }
            });
        });

        /* --- liste du panier (délégation) --- */
        if (els.list) {
            els.list.addEventListener('click', function (e) {
                var t = e.target.closest ? e.target.closest('[data-ci-inc],[data-ci-dec],[data-ci-rm]') : null;
                if (!t) return;
                var i;
                if (t.hasAttribute('data-ci-inc')) { i = parseInt(t.getAttribute('data-ci-inc'), 10); setQty(i, state.items[i].qty + 1); }
                else if (t.hasAttribute('data-ci-dec')) { i = parseInt(t.getAttribute('data-ci-dec'), 10); setQty(i, state.items[i].qty - 1); }
                else if (t.hasAttribute('data-ci-rm')) { i = parseInt(t.getAttribute('data-ci-rm'), 10); removeAt(i); }
            });
        }

        /* --- boutons + / – du menu --- */
        document.addEventListener('click', function (e) {
            var t = e.target.closest ? e.target.closest('[data-add],[data-inc],[data-dec]') : null;
            if (!t) return;
            if (t.hasAttribute('data-add')) {
                var id = t.getAttribute('data-add');
                if (!id) return;
                add(id, 1);
                if (B.gsap && !B.reduced) {
                    B.gsap.fromTo(t, { scaleX: 1.12, scaleY: 0.88 }, { scaleX: 1, scaleY: 1, duration: 0.55, ease: 'elastic.out(1, 0.42)' });
                }
                var q = B.$('[data-qty="' + id + '"]');
                if (q && !B.reduced) { q.classList.remove('is-bump'); void q.offsetWidth; q.classList.add('is-bump'); }
                if (!B.reduced && typeof B.crumbs === 'function') {
                    var cx = e.clientX || 0, cy = e.clientY || 0;
                    if (!cx && !cy) {
                        var box = t.getBoundingClientRect();
                        cx = box.left + box.width / 2;
                        cy = box.top + box.height / 2;
                    }
                    B.crumbs(cx, cy);
                }
                return;
            }
            var inc = t.hasAttribute('data-inc'), dec = t.hasAttribute('data-dec');
            var pid = t.getAttribute(inc ? 'data-inc' : 'data-dec');
            var box = B.$('[data-qty="' + pid + '"]');
            var current = 0;
            state.items.forEach(function (it) { if (it.id === pid) current += it.qty; });
            if (inc) { add(pid, 1); }
            else {
                if (current <= 1) {
                    var idx = -1;
                    state.items.forEach(function (it, k) { if (it.id === pid) idx = k; });
                    if (idx >= 0) removeAt(idx);
                } else { setQty(state.items.findIndex(function (it) { return it.id === pid; }), current - 1); }
            }
            if (box && !B.reduced) { box.classList.remove('is-bump'); void box.offsetWidth; box.classList.add('is-bump'); }
        });

        /* --- checkout : navigation par étapes --- */
        if (els.coNext) els.coNext.addEventListener('click', function () {
            if (!validateStep(coStep)) return;
            if (coStep < 3) { coStep++; paintStep(); return; }
            submitOrder();
        });
        if (els.coBack) els.coBack.addEventListener('click', function () {
            if (coStep > 1) { coStep--; paintStep(); }
        });
        B.$$('[data-checkout-close]').forEach(function (b) { b.addEventListener('click', closeCheckout); });

        /* --- validation au blur + Entrée --- */
        document.addEventListener('focusout', function (e) {
            var input = e.target;
            if (!input || !input.getAttribute || !input.getAttribute('data-validate')) return;
            if (!els.checkoutRoot || !input.closest('[data-checkout]')) return;
            validateField(input);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            var input = e.target;
            if (!input || !input.closest || !input.closest('[data-checkout-form]')) return;
            if (input.tagName === 'TEXTAREA') return;
            e.preventDefault();
            if (coStep < 3) { if (validateStep(coStep)) { coStep++; paintStep(); } }
            else submitOrder();
        });
        if (els.checkoutRoot) {
            els.checkoutRoot.addEventListener('change', function (e) {
                if (e.target && e.target.name === 'payment') paintRecap();
            });
        }

        B.openCart = open;
        B.closeCart = close;
        B.openCheckout = openCheckout;
    }

    B.cart = {
        init: init,
        add: add,
        open: function () { open(); },
        close: function () { close(); },
        render: render,
        clear: function () { state.items = []; save(); render(); syncQtyWidgets(); },
        get mode() { return state.mode; },
        get items() { return state.items.slice(); },
        get count() { return count(); },
        get subtotal() { return subtotal(); },
        get total() { return total(); },
        syncQty: syncQtyWidgets
    };
})();
