<?php

/**
 * CHECKOUT — 3 étapes simulées, puis « scontrino » qui s'imprime et suivi.
 */

declare(strict_types=1);

/** @var array $CFG */
$d  = $CFG['delivery'];
$co = $CFG['contact'];

$pickupStep = (int) $d['pickup_step_minutes'];
?>
<div class="checkout" data-checkout data-screen="form" hidden>
    <div class="checkout__scrim" data-checkout-close tabindex="-1"></div>

    <!-- ============================ FORM ============================ -->
    <div class="checkout__panel" role="dialog" aria-modal="true" aria-labelledby="co-title"
         data-screen-panel="form" data-checkout-panel>

        <header class="checkout__head">
            <p class="checkout__kicker"><?php echo e($CFG['brand']['name']); ?> · CASSA</p>
            <h2 class="checkout__title" id="co-title">Vai alla cassa</h2>
            <button class="checkout__close" type="button" data-checkout-close aria-label="Chiudi la cassa">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-close"></use></svg>
            </button>
        </header>

        <ol class="co-steps" data-co-steps>
            <li class="co-steps__i is-on" data-co-step-dot="1"><span>1</span> Chi sei</li>
            <li class="co-steps__i" data-co-step-dot="2"><span>2</span> Come paghi</li>
            <li class="co-steps__i" data-co-step-dot="3"><span>3</span> Conferma</li>
        </ol>

        <form class="checkout__form" data-checkout-form novalidate>

            <!-- Étape 1 -->
            <fieldset class="co-panel" data-co-panel="1">
                <legend class="sr-only">Chi sei</legend>
                <div class="fields">
                    <div class="field">
                        <label class="field__label" for="co-name">Nome</label>
                        <input class="field__in" id="co-name" name="name" type="text" autocomplete="name"
                               placeholder="Come ti chiamiamo?" required data-validate="name">
                        <p class="field__err" data-err-for="co-name" hidden></p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="co-phone">Telefono</label>
                        <input class="field__in" id="co-phone" name="phone" type="tel" inputmode="tel"
                               autocomplete="tel" placeholder="+39 333 000 0000" required data-validate="phone">
                        <p class="field__err" data-err-for="co-phone" hidden></p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="co-email">Email</label>
                        <input class="field__in" id="co-email" name="email" type="email" autocomplete="email"
                               placeholder="nome@esempio.it" required data-validate="email">
                        <p class="field__err" data-err-for="co-email" hidden></p>
                    </div>

                    <div class="field field--full" data-only="delivery">
                        <label class="field__label" for="co-address">Indirizzo</label>
                        <input class="field__in" id="co-address" name="address" type="text" autocomplete="street-address"
                               placeholder="Via, numero civico, piano" data-validate="address">
                        <p class="field__err" data-err-for="co-address" hidden></p>
                    </div>
                    <div class="field field--full" data-only="delivery">
                        <label class="field__label" for="co-note">Citofono / Note per il rider</label>
                        <input class="field__in" id="co-note" name="rider_note" type="text"
                               placeholder="Citofono Rossi, interno 4. Suona due volte.">
                    </div>

                    <div class="field field--full" data-only="pickup">
                        <p class="field__label" id="co-when-label">Quando passi?</p>
                        <div class="chips chips--wrap" role="group" aria-labelledby="co-when-label" data-pickup-slots>
                            <button class="chip chip--solid is-on" type="button" data-pickup="asap" aria-pressed="true">Prima possibile</button>
                        </div>
                        <input type="hidden" name="pickup_time" value="asap" data-pickup-value>
                    </div>
                </div>
            </fieldset>

            <!-- Étape 2 -->
            <fieldset class="co-panel" data-co-panel="2" hidden>
                <legend class="sr-only">Come paghi</legend>
                <div class="pay" role="radiogroup" aria-label="Metodo di pagamento">
                    <?php foreach ((array) $d['payment_methods'] as $pi => $pm) : ?>
                        <label class="pay__opt">
                            <input type="radio" name="payment" value="<?php echo e((string) $pm['id']); ?>"
                                   <?php echo $pi === 0 ? 'checked' : ''; ?>>
                            <span class="pay__box">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-<?php echo e((string) $pm['icon']); ?>"></use></svg>
                                <span><?php echo e((string) $pm['label']); ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="pay__note">
                    <svg viewBox="0 0 200 200" class="pay__note-ico" aria-hidden="true" focusable="false"><use href="#stk-stella-fritto"></use></svg>
                    <?php echo e((string) $d['payment_note']); ?>
                </p>
            </fieldset>

            <!-- Étape 3 -->
            <fieldset class="co-panel" data-co-panel="3" hidden>
                <legend class="sr-only">Conferma</legend>
                <div class="co-recap" data-co-recap>
                    <p class="co-recap__mode" data-co-recap-mode>Consegna</p>
                    <ul class="co-recap__list" data-co-recap-items></ul>
                    <dl class="co-recap__totals">
                        <div><dt>Subtotale</dt><dd data-co-recap-sub>€ 0</dd></div>
                        <div data-co-recap-feerow><dt>Consegna</dt><dd data-co-recap-fee>€ 0</dd></div>
                        <div class="co-recap__big"><dt>Totale</dt><dd data-co-recap-total>€ 0</dd></div>
                    </dl>
                    <ul class="co-recap__who" data-co-recap-who></ul>
                </div>
            </fieldset>

            <p class="checkout__err" data-checkout-err role="alert" hidden></p>
        </form>

        <footer class="checkout__foot">
            <button class="btn btn--ghost" type="button" data-co-back hidden>
                <span>Indietro</span>
            </button>
            <button class="btn btn--red btn--lg" type="button" data-co-next>
                <span>Continua</span>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-ur"></use></svg>
            </button>
        </footer>
    </div>

    <!-- ========================== SCONTRINO ========================== -->
    <div class="checkout__panel checkout__panel--print" data-screen-panel="done" hidden>
        <div class="scontrino" data-scontrino>
            <div class="scontrino__zig scontrino__zig--top" aria-hidden="true"></div>

            <div class="scontrino__in">
                <p class="scontrino__brand"><?php echo e($CFG['brand']['name']); ?></p>
                <p class="scontrino__sub"><?php echo e($CFG['brand']['subtitle']); ?> · <?php echo e($CFG['brand']['city']); ?></p>
                <p class="scontrino__addr"><?php echo e($co['address']); ?></p>
                <p class="scontrino__addr">P.IVA <?php echo e($CFG['brand']['piva']); ?> — Tel <?php echo e($co['phone_ui']); ?></p>

                <div class="scontrino__rule" aria-hidden="true"></div>

                <p class="scontrino__code">ORDINE N° <strong data-order-code>BMB-0000</strong></p>
                <p class="scontrino__when" data-order-when>—</p>

                <div class="scontrino__rule" aria-hidden="true"></div>

                <ul class="scontrino__rows" data-scontrino-rows></ul>

                <div class="scontrino__rule" aria-hidden="true"></div>

                <dl class="scontrino__totals">
                    <div><dt>Subtotale</dt><dd data-scontrino-sub>€ 0</dd></div>
                    <div data-scontrino-feerow><dt>Consegna</dt><dd data-scontrino-fee>€ 0</dd></div>
                    <div class="scontrino__big"><dt>TOTALE</dt><dd data-scontrino-total>€ 0</dd></div>
                    <div><dt>Pagamento</dt><dd data-scontrino-pay>—</dd></div>
                </dl>

                <div class="scontrino__rule" aria-hidden="true"></div>

                <p class="scontrino__thanks">Grazie. Torna presto, che si fredda.</p>
                <p class="scontrino__tiny">Documento non fiscale — ordine simulato a scopo dimostrativo.</p>

                <svg class="scontrino__barcode" viewBox="0 0 220 44" aria-hidden="true" focusable="false">
                    <?php for ($b = 0; $b < 54; $b++) : ?>
                        <rect x="<?php echo e((string) round($b * 4.05, 2)); ?>" y="0"
                              width="<?php echo $b % 5 === 0 ? 3 : ($b % 3 === 0 ? 2 : 1); ?>" height="44" fill="currentColor"/>
                    <?php endfor; ?>
                </svg>
                <p class="scontrino__bc" data-order-bc>* * * * 0 0 0 0 * * * *</p>

                <div class="scontrino__stamp" data-paid-stamp aria-hidden="true">PAGATO</div>
            </div>

            <div class="scontrino__zig scontrino__zig--bot" aria-hidden="true"></div>
        </div>

        <!-- ============================ SUIVI ============================ -->
        <div class="track" data-track>
            <p class="track__kicker">E ADESSO?</p>

            <ol class="track__list">
                <?php foreach ((array) $d['tracking'] as $ti => $t) : ?>
                    <li class="track__step" data-track-step="<?php echo e((string) $t['key']); ?>"
                        data-track-mode="<?php echo e((string) ($t['mode'] ?? 'any')); ?>">
                        <span class="track__ico">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-<?php echo e((string) $t['icon']); ?>"></use></svg>
                        </span>
                        <span class="track__label"><?php echo e((string) $t['label']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="track__road" aria-hidden="true">
                <span class="track__line"></span>
                <span class="track__scooter" data-track-scooter>
                    <svg viewBox="0 0 24 24"><use href="#ico-scooter"></use></svg>
                </span>
                <span class="track__home"></span>
            </div>

            <p class="track__eta" role="status" data-track-eta>Arrivo stimato: <strong><?php echo e((string) $d['eta']); ?></strong></p>

            <div class="track__actions">
                <button class="btn btn--ghost" type="button" data-checkout-close>
                    <span>Chiudi</span>
                </button>
                <a class="btn btn--red" href="#menu" data-scroll-to="#menu" data-checkout-close>
                    <span>Ordina ancora</span>
                </a>
            </div>

            <p class="track__demo">Demo accelerata: qui i passaggi durano qualche secondo.</p>
        </div>
    </div>
</div>
