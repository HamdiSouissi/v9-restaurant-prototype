<?php

/**
 * TIROIR PANIER — sac en papier kraft (texture beige, haut dentelé).
 * Plein écran sur mobile. Focus piégé, Échap pour fermer.
 */

declare(strict_types=1);

/** @var array $CFG */
$d = $CFG['delivery'];
?>
<div class="bag-drawer" data-cart-drawer hidden>
    <div class="bag-drawer__scrim" data-cart-close tabindex="-1"></div>

    <aside class="bag-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="cart-title" data-cart-panel>
        <div class="bag-drawer__zig" aria-hidden="true"></div>

        <header class="bag-drawer__head">
            <p class="bag-drawer__kicker"><?php echo e($CFG['brand']['name']); ?> · SACCHETTO</p>
            <h2 class="bag-drawer__title" id="cart-title">Il tuo ordine</h2>

            <button class="bag-drawer__close" type="button" data-cart-close aria-label="Chiudi il sacchetto">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-close"></use></svg>
            </button>
        </header>

        <div class="seg" role="group" aria-label="Come lo vuoi?" data-cart-mode>
            <button class="seg__btn is-on" type="button" data-mode="delivery" aria-pressed="true">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-scooter"></use></svg>
                <span>Consegna</span>
            </button>
            <button class="seg__btn" type="button" data-mode="pickup" aria-pressed="false">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-bag"></use></svg>
                <span>Asporto</span>
            </button>
        </div>

        <div class="bag-drawer__body" data-cart-body>

            <!-- Vide -->
            <div class="cart-empty" data-cart-empty>
                <svg class="cart-empty__art" viewBox="0 0 200 200" aria-hidden="true" focusable="false">
                    <path class="bag-line" d="M52 62h96l-12 112c-1 9-8 14-18 14H82c-10 0-17-5-18-14z"/>
                    <path class="bag-line" d="M76 62V48a24 24 0 0 1 48 0v14"/>
                    <path class="bag-line bag-line--dashed" d="M62 96h76"/>
                    <path class="bag-face" d="M84 122c4 6 10 9 16 9s12-3 16-9"/>
                    <circle class="bag-dot" cx="86" cy="112" r="4"/>
                    <circle class="bag-dot" cx="114" cy="112" r="4"/>
                </svg>
                <p class="cart-empty__t">Il sacchetto è vuoto</p>
                <p class="cart-empty__s">Rimedia subito.</p>
                <button class="btn btn--red" type="button" data-cart-gomenu><span>Vai al menu</span></button>
            </div>

            <!-- Articles -->
            <ul class="cart-list" data-cart-list></ul>
        </div>

        <footer class="bag-drawer__foot">
            <div class="cart-free" data-cart-free hidden>
                <p class="cart-free__txt" data-cart-free-txt></p>
                <span class="cart-free__bar"><span data-cart-free-bar></span></span>
            </div>

            <dl class="cart-totals">
                <div class="cart-totals__row">
                    <dt>Subtotale</dt><dd data-cart-subtotal>€ 0</dd>
                </div>
                <div class="cart-totals__row" data-cart-feerow>
                    <dt>Consegna</dt><dd data-cart-fee>€ 0</dd>
                </div>
                <div class="cart-totals__row cart-totals__row--big">
                    <dt>Totale</dt><dd data-cart-total>€ 0</dd>
                </div>
            </dl>

            <p class="cart-min" data-cart-min hidden></p>

            <button class="btn btn--red btn--lg btn--block" type="button" data-cart-checkout disabled>
                <span>Vai alla cassa</span>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-ur"></use></svg>
            </button>
            <p class="cart-note"><?php echo e((string) $d['payment_note']); ?> · <?php echo e((string) $d['zone']); ?></p>
        </footer>
    </aside>
</div>
