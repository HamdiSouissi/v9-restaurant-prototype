/*!
 * BOMBA — booking.js
 * Réservation simulée : calendrier maison, créneaux servis par /api/slots.php,
 * validation au blur, récap façon billet, confirmation en billet détachable
 * avec code, faux QR SVG et vrai export .ics.
 */
(function () {
    'use strict';

    var B = (window.Bomba = window.Bomba || {});

    var CFG = null;
    var els = {};
    var view = { y: 0, m: 0 };          // mois affiché (m : 1-12)
    var today = null;
    var selected = { date: '', time: '', guests: 2 };
    var lastResult = null;

    var DAYS_IT  = ['DOM', 'LUN', 'MAR', 'MER', 'GIO', 'VEN', 'SAB'];
    var MON_SHORT= ['GEN', 'FEB', 'MAR', 'APR', 'MAG', 'GIU', 'LUG', 'AGO', 'SET', 'OTT', 'NOV', 'DIC'];
    var MON_LONG = ['GENNAIO', 'FEBBRAIO', 'MARZO', 'APRILE', 'MAGGIO', 'GIUGNO',
                    'LUGLIO', 'AGOSTO', 'SETTEMBRE', 'OTTOBRE', 'NOVEMBRE', 'DICEMBRE'];
    var DOW_CAL  = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

    /* ================================================================= *
     * UTILITAIRES DATE
     * ================================================================= */
    function iso(d) {
        return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
    }
    function parseIso(s) {
        var p = String(s || '').split('-');
        return new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
    }
    /** ISO 8601 : lundi = 1 … dimanche = 7 (comme dans config.php). */
    function dowIso(d) { var n = d.getDay(); return n === 0 ? 7 : n; }
    function prettyDate(s) {
        var d = parseIso(s);
        if (isNaN(d.getTime())) return '—';
        return DAYS_IT[d.getDay()] + ' ' + ('0' + d.getDate()).slice(-2) + ' ' + MON_SHORT[d.getMonth()];
    }

    /* ================================================================= *
     * CALENDRIER
     * ================================================================= */
    function renderMonth() {
        var grid = els.grid, label = els.month;
        if (!grid || !label) return;

        label.textContent = MON_LONG[view.m - 1] + ' ' + view.y;

        var first = new Date(view.y, view.m - 1, 1);
        var days = new Date(view.y, view.m, 0).getDate();
        var lead = dowIso(first) - 1;
        var closedDow = Number(CFG.closed_dow) || 1;

        var html = '';
        for (var i = 0; i < lead; i++) html += '<span class="cal__blank" aria-hidden="true"></span>';

        for (var d = 1; d <= days; d++) {
            var date = new Date(view.y, view.m - 1, d);
            var key = iso(date);
            var dw = dowIso(date);
            var past = key < iso(today);
            var closed = dw === closedDow;
            var off = past || closed;
            var isSel = key === selected.date;

            html += '<button class="cal__day' + (off ? ' cal__day--off' : '') + '" type="button"' +
                ' data-date="' + key + '"' + (off ? ' disabled' : '') +
                ' aria-pressed="' + (isSel ? 'true' : 'false') + '"' +
                ' aria-label="' + B.escape(DOW_CAL[dw - 1] + ' ' + d + (closed && !past ? ', chiuso' : (past ? ', passato' : ''))) + '">' +
                '<span class="cal__d">' + d + '</span>' +
                '<span class="cal__dow">' + B.escape(closed && !past ? CFG.closed_label || 'chiuso' : DOW_CAL[dw - 1]) + '</span>' +
                '</button>';
        }
        grid.innerHTML = html;

        if (els.prev) {
            els.prev.disabled = (view.y === today.getFullYear() && view.m === today.getMonth() + 1);
        }
    }

    function shiftMonth(delta) {
        view.m += delta;
        while (view.m < 1) { view.m += 12; view.y--; }
        while (view.m > 12) { view.m -= 12; view.y++; }
        renderMonth();
    }

    function selectDate(key) {
        selected.date = key;
        selected.time = '';
        if (els.dateInput) els.dateInput.value = key;
        B.$$('.cal__day', els.grid).forEach(function (b) {
            b.setAttribute('aria-pressed', b.getAttribute('data-date') === key ? 'true' : 'false');
        });
        loadSlots();
        paintRecap();
    }

    /* ================================================================= *
     * CRÉNEAUX (/api/slots.php)
     * ================================================================= */
    function skeleton(n) {
        var s = '';
        for (var i = 0; i < n; i++) s += '<span class="skel skel--slot"></span>';
        return s;
    }

    function loadSlots() {
        if (!els.slots || !els.groups || (!els.groups.lunch && !els.groups.dinner)) return;
        if (!selected.date) {
            if (els.msg) { els.msg.hidden = false; els.msg.textContent = 'Scegli prima il giorno.'; }
            return;
        }
        var lunch = els.groups.lunch, dinner = els.groups.dinner;
        if (lunch) lunch.innerHTML = skeleton(6);
        if (dinner) dinner.innerHTML = skeleton(8);
        if (els.msg) { els.msg.hidden = false; els.msg.textContent = 'Cerchiamo i tavoli liberi…'; }

        var url = 'api/slots.php?date=' + encodeURIComponent(selected.date) + '&guests=' + encodeURIComponent(selected.guests);

        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    if (lunch) lunch.innerHTML = '';
                    if (dinner) dinner.innerHTML = '';
                    if (els.msg) {
                        els.msg.hidden = false;
                        els.msg.textContent = (data && data.error) || 'Ops, qualcosa si è bruciato. Riprova.';
                    }
                    return;
                }
                paintSlots('lunch', data.lunch && data.lunch.slots, lunch);
                paintSlots('dinner', data.dinner && data.dinner.slots, dinner);
                var free = countFree(data);
                if (els.msg) {
                    els.msg.hidden = false;
                    els.msg.textContent = free === 0
                        ? 'Tutto pieno quel giorno. Prova un altro, dai.'
                        : free + ' tavoli liberi — meglio bloccare adesso.';
                }
            })
            .catch(function () {
                if (lunch) lunch.innerHTML = '';
                if (dinner) dinner.innerHTML = '';
                if (els.msg) { els.msg.hidden = false; els.msg.textContent = 'Ops, qualcosa si è bruciato. Riprova.'; }
            });
    }

    function countFree(data) {
        var n = 0;
        ['lunch', 'dinner'].forEach(function (k) {
            var list = (data[k] && data[k].slots) || [];
            list.forEach(function (s) { if (s.status !== 'full') n++; });
        });
        return n;
    }

    function paintSlots(groupKey, slots, host) {
        if (!host) return;
        if (!slots || !slots.length) { host.innerHTML = '<span class="slots__msg">Nessun turno.</span>'; return; }
        host.innerHTML = slots.map(function (s) {
            var full = s.status === 'full';
            var last = s.status === 'last';
            var on = selected.time === s.time;
            return '<button class="slot' + (full ? ' slot--full' : '') + (on ? ' is-on' : '') + '" type="button"' +
                ' data-time="' + B.escape(s.time) + '"' + (full ? ' disabled' : '') +
                ' aria-pressed="' + (on ? 'true' : 'false') + '"' +
                ' aria-label="' + B.escape(s.time + (full ? ' — ' + (CFG.full_label || 'Pieno') : (last ? ' — ' + (CFG.last_label || 'Ultimi posti') : ''))) + '">' +
                B.escape(s.time) +
                (last ? '<span class="slot__flag">' + B.escape(CFG.last_label || 'Ultimi posti') + '</span>' : '') +
                '</button>';
        }).join('');

        B.$$('.slot', host).forEach(function (btn) {
            btn.addEventListener('click', function () {
                selected.time = btn.getAttribute('data-time');
                if (els.timeInput) els.timeInput.value = selected.time;
                B.$$('.slot', els.slots).forEach(function (b) {
                    var on = b === btn;
                    b.classList.toggle('is-on', on);
                    b.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
                paintRecap();
            });
        });
    }

    /* ================================================================= *
     * RÉCAP
     * ================================================================= */
    function paintRecap() {
        var dayTxt = selected.date ? prettyDate(selected.date) : '—';
        var timeTxt = selected.time || '—';
        var g = Number(selected.guests) > Number(CFG.guests_max) ? '8+' : String(selected.guests);

        if (els.recapLine) {
            els.recapLine.textContent = (selected.date && selected.time)
                ? dayTxt + ' · ' + timeTxt + ' · ' + g + (Number(selected.guests) === 1 ? ' PERSONA' : ' PERSONE')
                : 'SCEGLI GIORNO E ORA';
        }
        if (els.recapDay) els.recapDay.textContent = dayTxt;
        if (els.recapTime) els.recapTime.textContent = timeTxt;
        if (els.recapGuests) els.recapGuests.textContent = g;
        if (els.recapOcc && els.occasion) els.recapOcc.textContent = els.occasion.value;
    }

    /* ================================================================= *
     * VALIDATION
     * ================================================================= */
    var RULES = {
        name: function (v) { return v.length >= 2 && /^[A-Za-zÀ-ÿ0-9'\-\.\s]+$/.test(v) ? '' : 'Ci serve un nome vero, dai.'; },
        phone: function (v) { return v.replace(/\D+/g, '').length >= 9 ? '' : 'Ci serve un numero valido per chiamarti.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v) ? '' : 'Email strana: ricontrolla.'; },
        check: function (v, i) { return i.checked ? '' : 'Serve il tuo ok sulla privacy.'; }
    };

    function validateField(input) {
        var rule = input.getAttribute('data-validate');
        if (!rule || !RULES[rule]) return true;
        var field = input.closest('.field') || input.closest('.fld');
        var errBox = field ? (B.$('[data-err-for="' + input.id + '"]', field) || B.$('.field__err', field)) : null;
        var msg = RULES[rule]((input.value || '').trim(), input);
        if (field) {
            field.classList.toggle('is-bad', !!msg);
            field.classList.toggle('is-ok', !msg && (input.type === 'checkbox' ? input.checked : (input.value || '').trim() !== ''));
        }
        if (errBox) { errBox.hidden = !msg; errBox.textContent = msg; }
        input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        return !msg;
    }

    function validateAll() {
        var ok = true, firstBad = null;
        B.$$('input[data-validate]', els.form).forEach(function (i) {
            var good = validateField(i);
            if (!good && !firstBad) firstBad = i;
            ok = ok && good;
        });
        if (!selected.date) { ok = false; showFormError('Scegli un giorno dal calendario.'); }
        else if (!selected.time) { ok = false; showFormError('Scegli anche l\'orario, che teniamo il tavolo.'); }
        if (!ok && !els.formErr.textContent) showFormError('Manca ancora qualcosa: ricontrolla i campi in rosso.');
        if (firstBad) firstBad.focus();
        return ok;
    }

    function showFormError(msg) {
        if (!els.formErr) return;
        els.formErr.hidden = !msg;
        els.formErr.textContent = msg || '';
    }

    /* ================================================================= *
     * ENVOI + CONFIRMATION
     * ================================================================= */
    function submit() {
        showFormError('');
        if (!validateAll()) return;

        var g = function (id) { var n = B.$(id, els.form); return n ? n.value.trim() : ''; };
        var payload = {
            name: g('#bk-name'),
            phone: g('#bk-phone'),
            email: g('#bk-email'),
            date: selected.date,
            time: selected.time,
            guests: Number(selected.guests),
            occasion: g('#bk-occasion'),
            notes: g('#bk-notes'),
            privacy: els.privacy && els.privacy.checked ? '1' : ''
        };

        if (els.submit) { els.submit.disabled = true; var s = B.$('span', els.submit); if (s) s.textContent = 'Blocchiamo…'; }

        fetch('api/booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
            if (!data || !data.ok) {
                var msg = (data && data.error) || 'Ops, qualcosa si è bruciato. Riprova.';
                showFormError(msg);
                if (data && data.field) {
                    var map = { name: '#bk-name', phone: '#bk-phone', email: '#bk-email', privacy: '#bk-privacy' };
                    if (map[data.field]) { var f = B.$(map[data.field], els.form); if (f) { f.focus(); validateField(f); } }
                }
                return;
            }
            lastResult = data;
            confirmBooking(data);
        })
        .catch(function () { showFormError('Ops, qualcosa si è bruciato. Riprova.'); })
        .then(function () {
            if (els.submit) { els.submit.disabled = false; var s2 = B.$('span', els.submit); if (s2) s.textContent = 'Blocca il tavolo'; }
        });
    }

    function confirmBooking(data) {
        if (!els.confirm) return;
        els.grid2 && (els.grid2.hidden = true);
        els.confirm.hidden = false;

        set('[data-confirm-when]', (data.date_ui || prettyDate(data.date)) + ' · ' + data.time);
        set('[data-confirm-name]', data.name);
        set('[data-confirm-guests]', data.guests + (Number(data.guests) === 1 ? ' persona' : ' persone'));
        set('[data-confirm-phone]', data.phone);
        set('[data-confirm-occ]', data.occasion || 'Nessuna');
        set('[data-confirm-code]', data.code);

        drawQr(B.$('[data-confirm-qr]', els.confirm), data.code);

        var ticket = B.$('[data-confirm-ticket]', els.confirm);
        if (ticket && !B.reduced) {
            ticket.classList.remove('is-tearing');
            els.confirm.classList.remove('is-tearing');
            void ticket.offsetWidth;
            els.confirm.classList.add('is-tearing');
        }

        B.scrollToId(els.confirm, -90);
        B.toast('Tavolo bloccato! Codice ' + data.code, { variant: 'ok', duration: 5200 });

        setTimeout(function () {
            var f = B.$('[data-ics]', els.confirm);
            if (f) f.focus();
        }, 500);
    }

    function set(sel, value) {
        var n = B.$(sel, els.confirm || document);
        if (n) n.textContent = value == null ? '—' : String(value);
    }

    /* --- faux QR code SVG, déterministe à partir du code --------------- */
    function drawQr(svg, seed) {
        if (!svg) return;
        var N = 29, cell = 1, out = '';
        var h = 2166136261 >>> 0;
        var s = String(seed || 'BOMBA');

        function hash(x, y) {
            var v = h;
            v ^= s.charCodeAt((x * 7 + y * 13 + x * y) % s.length) + x * 31 + y * 17;
            v = Math.imul(v, 16777619) >>> 0;
            return v;
        }
        function isFinder(x, y) {
            return (x < 8 && y < 8) || (x >= N - 8 && y < 8) || (x < 8 && y >= N - 8);
        }

        for (var y = 0; y < N; y++) {
            for (var x = 0; x < N; x++) {
                if (isFinder(x, y)) continue;
                if ((hash(x, y) & 3) === 0) out += '<rect x="' + x + '" y="' + y + '" width="1" height="1"/>';
            }
        }
        // Trois yeux de repérage
        [[0, 0], [N - 7, 0], [0, N - 7]].forEach(function (p) {
            out += '<rect x="' + p[0] + '" y="' + p[1] + '" width="7" height="7"/>' +
                   '<rect x="' + (p[0] + 1) + '" y="' + (p[1] + 1) + '" width="5" height="5" fill="#F5F1E8"/>' +
                   '<rect x="' + (p[0] + 2) + '" y="' + (p[1] + 2) + '" width="3" height="3"/>';
        });

        svg.innerHTML = '<rect width="' + N + '" height="' + N + '" fill="#F5F1E8"/>' +
            '<g fill="#151515" shape-rendering="crispEdges">' + out + '</g>';
    }

    /* --- export .ics réel ------------------------------------------------ */
    function pad2(n) { return ('0' + n).slice(-2); }
    function icsStamp(d) {
        return d.getUTCFullYear() + pad2(d.getUTCMonth() + 1) + pad2(d.getUTCDate()) + 'T' +
            pad2(d.getUTCHours()) + pad2(d.getUTCMinutes()) + pad2(d.getUTCSeconds()) + 'Z';
    }
    function icsLocal(d) {
        return d.getFullYear() + pad2(d.getMonth() + 1) + pad2(d.getDate()) + 'T' +
            pad2(d.getHours()) + pad2(d.getMinutes()) + '00';
    }
    function downloadIcs() {
        if (!lastResult) { B.toast('Prima prenota, poi lo metti in agenda.'); return; }
        var r = lastResult;
        var parts = String(r.date + 'T' + r.time).split(/[-:T]/);
        var start = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]), Number(parts[3]), Number(parts[4]), 0);
        var end = new Date(start.getTime() + 2 * 3600 * 1000);
        var now = new Date();
        var brand = (B.cfg.brand && B.cfg.brand.name) || 'BOMBA';

        function esc(t) { return String(t).replace(/([,;\\])/g, '\\$1').replace(/\n/g, '\\n'); }

        var lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' + brand + '//Prenotazione tavolo//IT',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' + r.code.toLowerCase() + '-' + Date.now() + '@' + brand.toLowerCase().replace(/\s+/g, '') + '.local',
            'DTSTAMP:' + icsStamp(now),
            'DTSTART:' + icsLocal(start),
            'DTEND:' + icsLocal(end),
            'SUMMARY:' + esc((r.ics && r.ics.summary) || (brand + ' — tavolo per ' + r.guests)),
            'LOCATION:' + esc((r.ics && r.ics.location) || r.address || ''),
            'DESCRIPTION:' + esc((r.ics && r.ics.description) || ('Codice ' + r.code)),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR'
        ];

        var blob = new Blob([lines.join('\r\n') + '\r\n'], { type: 'text/calendar;charset=utf-8' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'prenotazione-' + String(r.code).toLowerCase() + '.ics';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 1500);
        B.toast('File .ics scaricato. Ci vediamo lì.', { variant: 'ok' });
    }

    function resetBooking() {
        selected = { date: '', time: '', guests: 2 };
        lastResult = null;
        if (els.confirm) els.confirm.hidden = true;
        if (els.grid2) els.grid2.hidden = false;
        if (els.form) els.form.reset();
        if (els.dateInput) els.dateInput.value = '';
        if (els.timeInput) els.timeInput.value = '';
        view = { y: today.getFullYear(), m: today.getMonth() + 1 };
        renderMonth();
        B.$$('.cal__day', els.grid).forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
        if (els.groups.lunch) els.groups.lunch.innerHTML = skeleton(6);
        if (els.groups.dinner) els.groups.dinner.innerHTML = skeleton(8);
        if (els.msg) { els.msg.hidden = false; els.msg.textContent = 'Scegli prima il giorno.'; }
        showFormError('');
        paintRecap();
        B.scrollToId('#prenota', -90);
        var f = B.$('.guest input[value="2"]', els.root);
        if (f) f.focus();
    }

    /* ================================================================= *
     * INIT
     * ================================================================= */
    function init() {
        var root = B.$('.book');
        CFG = B.cfg.booking || {};
        if (!root) return;

        els.root   = root;
        els.form   = B.$('[data-book-form]', root);
        els.grid   = B.$('[data-cal-grid]', root);
        els.month  = B.$('[data-cal-month]', root);
        els.prev   = B.$('[data-cal-prev]', root);
        els.next   = B.$('[data-cal-next]', root);
        els.slots  = B.$('[data-slots]', root);
        els.msg    = B.$('[data-slots-msg]', root);
        els.groups = { lunch: B.$('[data-slot-group="lunch"]', root), dinner: B.$('[data-slot-group="dinner"]', root) };
        els.dateInput = B.$('[data-book-date]', root);
        els.timeInput = B.$('[data-book-time]', root);
        els.grid2  = B.$('.book__grid', root);
        els.confirm= B.$('[data-book-confirm]', root);
        els.submit = B.$('[data-book-submit]', root);
        els.formErr= B.$('[data-book-formerr]', root);
        els.recapLine = B.$('[data-recap-line]', root);
        els.recapDay  = B.$('[data-recap-day]', root);
        els.recapTime = B.$('[data-recap-time]', root);
        els.recapGuests = B.$('[data-recap-guests]', root);
        els.recapOcc  = B.$('[data-recap-occ]', root);
        els.occasion  = B.$('#bk-occasion', root);
        els.privacy   = B.$('#bk-privacy', root);
        els.bigParty  = B.$('[data-big-party]', root);

        today = new Date();
        today.setHours(0, 0, 0, 0);
        view = { y: today.getFullYear(), m: today.getMonth() + 1 };

        renderMonth();
        paintRecap();

        if (els.prev) els.prev.addEventListener('click', function () { shiftMonth(-1); });
        if (els.next) els.next.addEventListener('click', function () { shiftMonth(1); });

        if (els.grid) {
            els.grid.addEventListener('click', function (e) {
                var btn = e.target.closest ? e.target.closest('.cal__day') : null;
                if (!btn || btn.disabled) return;
                selectDate(btn.getAttribute('data-date'));
            });
        }

        // Personnes
        B.$$('input[name="guests"]', root).forEach(function (radio) {
            radio.addEventListener('change', function () {
                selected.guests = parseInt(radio.value, 10) || 2;
                if (els.bigParty) els.bigParty.hidden = selected.guests <= Number(CFG.guests_max || 8);
                paintRecap();
                if (selected.date) loadSlots();
            });
        });

        if (els.occasion) els.occasion.addEventListener('change', paintRecap);

        if (els.form) {
            els.form.addEventListener('submit', function (e) { e.preventDefault(); submit(); });
            els.form.addEventListener('focusout', function (e) {
                var input = e.target;
                if (input && input.getAttribute && input.getAttribute('data-validate')) validateField(input);
            });
        }

        B.$$('[data-ics]').forEach(function (b) { b.addEventListener('click', downloadIcs); });
        B.$$('[data-book-reset]').forEach(function (b) { b.addEventListener('click', resetBooking); });

        // Pré-sélectionne le premier jour réservable (sympa en démo)
        var firstOpen = B.$$('.cal__day:not([disabled])', els.grid)[0];
        if (firstOpen) selectDate(firstOpen.getAttribute('data-date'));
    }

    B.booking = { init: init };
})();
