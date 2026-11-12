<?php

/**
 * PRENOTA IL TAVOLO — simulation complète.
 * Calendrier maison, créneaux servis par /api/slots.php, billet détachable,
 * export .ics généré côté client.
 */

declare(strict_types=1);

/** @var array $CFG */
$bk       = $CFG['booking'];
$dayLabels = $bk['day_labels'];
$closedDow = (int) $bk['closed_dow'];
$maxGuests = (int) $bk['guests_max'];

$itMonths = ['GENNAIO', 'FEBBRAIO', 'MARZO', 'APRILE', 'MAGGIO', 'GIUGNO',
             'LUGLIO', 'AGOSTO', 'SETTEMBRE', 'OTTOBRE', 'NOVEMBRE', 'DICEMBRE'];

$now   = new DateTimeImmutable('now', new DateTimeZone('Europe/Rome'));
$year  = (int) $now->format('Y');
$month = (int) $now->format('n');
$today = $now->format('Y-m-d');

/**
 * Grille d'un mois, style ticket. Jours passés désactivés, lundi « Chiuso ».
 */
$renderMonth = static function (int $y, int $m) use ($dayLabels, $closedDow, $today): string {
    $first = new DateTimeImmutable(sprintf('%04d-%02d-01', $y, $m));
    $days  = (int) $first->format('t');
    $lead  = ((int) $first->format('N')) - 1; // lundi en premier

    $html = '';
    for ($i = 0; $i < $lead; $i++) {
        $html .= '<span class="cal__blank" aria-hidden="true"></span>';
    }
    for ($d = 1; $d <= $days; $d++) {
        $date = $first->setDate($y, $m, $d);
        $iso  = $date->format('Y-m-d');
        $dow  = (int) $date->format('N');
        $past = $iso < $today;
        $off  = $past || $dow === $closedDow;
        $dowL = $dayLabels[($dow % 7)] ?? '';

        $html .= '<button class="cal__day' . ($off ? ' cal__day--off' : '') . '"'
            . ' type="button" data-date="' . e($iso) . '"'
            . ($off ? ' disabled' : '')
            . ' aria-pressed="false"'
            . ($off && !$past && $dow === $closedDow ? ' aria-label="' . e($dowL . ' ' . $d . ', chiuso') . '"' : '')
            . ($past ? ' aria-label="' . e($dowL . ' ' . $d . ', passato') . '"' : '')
            . '><span class="cal__d">' . $d . '</span>'
            . '<span class="cal__dow">' . e($off && $dow === $closedDow && !$past ? 'chiuso' : $dowL) . '</span>'
            . '</button>';
    }
    return $html;
};
?>
<section class="book section section--carta" id="prenota" aria-labelledby="book-title"
         data-sweep="asfalto" style="--sweep:var(--asfalto)">

    <div class="book__head">
        <h2 class="h2 h2--split" id="book-title">
            <span class="h2__a" data-kinetic="62,125">PRENOTA IL</span>
            <span class="h2__b" data-kinetic="62,125">TAVOLO</span>
        </h2>
        <p class="book__sub">Pochi tavoli, tanta fame. Meglio bloccare il posto.</p>
    </div>

    <div class="book__grid">

        <form class="book__form" id="bookForm" data-book-form novalidate>

            <!-- Quanti siete? -->
            <fieldset class="fld" data-guests>
                <legend class="fld__legend">Quanti siete?</legend>
                <div class="guests">
                    <?php for ($g = 1; $g <= $maxGuests; $g++) : ?>
                        <label class="guest">
                            <input type="radio" name="guests" value="<?php echo $g; ?>"<?php echo $g === 2 ? ' checked' : ''; ?>>
                            <span class="guest__n"><?php echo $g; ?></span>
                        </label>
                    <?php endfor; ?>
                    <label class="guest guest--more">
                        <input type="radio" name="guests" value="9">
                        <span class="guest__n">8+</span>
                    </label>
                </div>
                <p class="fld__big" data-big-party hidden>
                    <?php echo e((string) $bk['big_party']); ?>
                    <a href="tel:<?php echo e($CFG['contact']['phone']); ?>"><?php echo e($CFG['contact']['phone_ui']); ?></a>
                </p>
            </fieldset>

            <!-- Che giorno? -->
            <fieldset class="fld">
                <legend class="fld__legend">Che giorno?</legend>
                <div class="cal" data-cal>
                    <div class="cal__bar">
                        <button class="cal__nav" type="button" data-cal-prev aria-label="Mese precedente">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-chev-l"></use></svg>
                        </button>
                        <p class="cal__month" data-cal-month>
                            <?php echo e($itMonths[$month - 1] . ' ' . $year); ?>
                        </p>
                        <button class="cal__nav" type="button" data-cal-next aria-label="Mese successivo">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-chev-r"></use></svg>
                        </button>
                    </div>

                    <div class="cal__dows" aria-hidden="true">
                        <?php foreach ($dayLabels as $dl) : ?><span><?php echo e($dl); ?></span><?php endforeach; ?>
                    </div>

                    <div class="cal__grid" data-cal-grid role="group" aria-label="Scegli il giorno">
                        <?php echo $renderMonth($year, $month); ?>
                    </div>

                    <p class="cal__hint">Lun: chiuso — si mangia di strada anche in piedi.</p>
                </div>
                <input type="hidden" name="date" value="" data-book-date>
            </fieldset>

            <!-- A che ora? -->
            <fieldset class="fld">
                <legend class="fld__legend">A che ora?</legend>
                <div class="slots" data-slots>
                    <div class="slots__group">
                        <p class="slots__label"><?php echo e($bk['lunch']['label']); ?></p>
                        <div class="slots__grid" data-slot-group="lunch">
                            <?php for ($s = 0; $s < 6; $s++) : ?><span class="skel skel--slot"></span><?php endfor; ?>
                        </div>
                    </div>
                    <div class="slots__group">
                        <p class="slots__label"><?php echo e($bk['dinner']['label']); ?></p>
                        <div class="slots__grid" data-slot-group="dinner">
                            <?php for ($s = 0; $s < 8; $s++) : ?><span class="skel skel--slot"></span><?php endfor; ?>
                        </div>
                    </div>
                    <p class="slots__msg" data-slots-msg role="status">Scegli prima il giorno.</p>
                </div>
                <input type="hidden" name="time" value="" data-book-time>
            </fieldset>

            <!-- I tuoi dati -->
            <fieldset class="fld">
                <legend class="fld__legend">I tuoi dati</legend>

                <div class="fields">
                    <div class="field">
                        <label class="field__label" for="bk-name">Nome</label>
                        <input class="field__in" id="bk-name" name="name" type="text" autocomplete="name"
                               placeholder="Come ti chiamiamo?" required data-validate="name">
                        <p class="field__err" data-err-for="bk-name" hidden></p>
                    </div>

                    <div class="field">
                        <label class="field__label" for="bk-phone">Telefono</label>
                        <input class="field__in" id="bk-phone" name="phone" type="tel" autocomplete="tel"
                               inputmode="tel" placeholder="+39 333 000 0000" required data-validate="phone">
                        <p class="field__err" data-err-for="bk-phone" hidden></p>
                    </div>

                    <div class="field">
                        <label class="field__label" for="bk-email">Email</label>
                        <input class="field__in" id="bk-email" name="email" type="email" autocomplete="email"
                               placeholder="nome@esempio.it" required data-validate="email">
                        <p class="field__err" data-err-for="bk-email" hidden></p>
                    </div>

                    <div class="field">
                        <label class="field__label" for="bk-occasion">Occasione</label>
                        <select class="field__in field__in--select" id="bk-occasion" name="occasion">
                            <?php foreach ((array) $bk['occasions'] as $occ) : ?>
                                <option value="<?php echo e($occ); ?>"><?php echo e($occ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field field--full">
                        <label class="field__label" for="bk-notes">Note</label>
                        <textarea class="field__in field__in--area" id="bk-notes" name="notes" rows="3"
                                  placeholder="Tavolo vicino alla finestra? Allergie? Scrivici."></textarea>
                    </div>

                    <div class="field field--full">
                        <label class="check" for="bk-privacy">
                            <input class="check__in" id="bk-privacy" name="privacy" type="checkbox" required data-validate="check">
                            <span class="check__box" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><use href="#ico-check"></use></svg>
                            </span>
                            <span class="check__txt"><?php echo e((string) $bk['privacy']); ?></span>
                        </label>
                        <p class="field__err" data-err-for="bk-privacy" hidden></p>
                    </div>
                </div>
            </fieldset>

            <div class="book__cta">
                <button class="btn btn--red btn--lg btn--block" type="submit" data-book-submit>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-ticket"></use></svg>
                    <span>Blocca il tavolo</span>
                </button>
                <p class="book__formerr" data-book-formerr role="alert" hidden></p>
            </div>
        </form>

        <!-- Récap façon billet de concert -->
        <aside class="ticketcard" aria-label="Riepilogo della prenotazione">
            <div class="ticketcard__stub">
                <p class="ticketcard__brand"><?php echo e($CFG['brand']['name']); ?></p>
                <p class="ticketcard__sub"><?php echo e($CFG['brand']['subtitle']); ?></p>
                <svg class="ticketcard__barcode" viewBox="0 0 200 30" aria-hidden="true" focusable="false">
                    <?php for ($b = 0; $b < 50; $b++) : ?>
                        <rect x="<?php echo e((string) round($b * 4, 1)); ?>" y="0"
                              width="<?php echo $b % 3 === 0 ? 2 : 1; ?>" height="30" fill="currentColor"/>
                    <?php endfor; ?>
                </svg>
                <p class="ticketcard__tiny"><?php echo e($CFG['brand']['number']); ?></p>
            </div>

            <div class="ticketcard__body" data-recap>
                <p class="ticketcard__k">IL TUO POSTO</p>
                <p class="ticketcard__big" data-recap-line>SCEGLI GIORNO E ORA</p>
                <ul class="ticketcard__rows">
                    <li><span>Giorno</span><strong data-recap-day>—</strong></li>
                    <li><span>Ora</span><strong data-recap-time>—</strong></li>
                    <li><span>Persone</span><strong data-recap-guests>2</strong></li>
                    <li><span>Occasione</span><strong data-recap-occ>Nessuna</strong></li>
                </ul>
                <p class="ticketcard__addr"><?php echo e($CFG['contact']['address']); ?></p>
            </div>
        </aside>
    </div>

    <!-- Confirmation : billet détachable -->
    <div class="confirm" data-book-confirm hidden>
        <div class="confirm__ticket" data-confirm-ticket>
            <div class="confirm__main">
                <p class="confirm__kicker">BOMBA · PARMA</p>
                <p class="confirm__title" data-kinetic="62,125">Tavolo bloccato!</p>
                <p class="confirm__when" data-confirm-when>—</p>
                <ul class="confirm__rows">
                    <li><span>Nome</span><strong data-confirm-name>—</strong></li>
                    <li><span>Persone</span><strong data-confirm-guests>—</strong></li>
                    <li><span>Telefono</span><strong data-confirm-phone>—</strong></li>
                    <li><span>Occasione</span><strong data-confirm-occ>—</strong></li>
                </ul>
                <p class="confirm__note">Se cambi idea, chiamaci: nessun dramma, solo fame.</p>
            </div>

            <div class="confirm__stub" data-confirm-stub>
                <p class="confirm__stub-k">CODICE</p>
                <p class="confirm__code" data-confirm-code>BMB-T-0000</p>
                <svg class="confirm__qr" viewBox="0 0 29 29" data-confirm-qr role="img" aria-label="Codice di prenotazione in formato QR (decorativo)">
                    <rect width="29" height="29" fill="currentColor" opacity="0"/>
                </svg>
                <button class="btn btn--ink btn--sm btn--block" type="button" data-ics>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-clock"></use></svg>
                    <span>Aggiungi al calendario</span>
                </button>
                <button class="btn btn--ghost btn--sm btn--block" type="button" data-book-reset>
                    <span>Nuova prenotazione</span>
                </button>
            </div>
        </div>
    </div>
</section>
