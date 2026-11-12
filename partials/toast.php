<?php

/**
 * Composants flottants : toasts, barre mobile fixe, pastille Demo.
 */

declare(strict_types=1);

/** @var array $CFG */
?>
<div class="toasts" data-toasts role="region" aria-live="polite" aria-label="Avvisi"></div>

<template data-toast-tpl>
    <div class="toast" role="status">
        <svg class="toast__ico" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><use href="#stk-stella-fritto"></use></svg>
        <p class="toast__txt" data-toast-txt></p>
        <button class="toast__act" type="button" data-toast-act hidden></button>
        <button class="toast__x" type="button" data-toast-x aria-label="Chiudi l'avviso">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-close"></use></svg>
        </button>
    </div>
</template>

<nav class="mbar" data-mbar aria-label="Azioni rapide">
    <a class="mbar__btn mbar__btn--ghost" href="#prenota" data-scroll-to="#prenota">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-ticket"></use></svg>
        <span>Prenota</span>
    </a>
    <button class="mbar__btn mbar__btn--red" type="button" data-bag-open>
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-bag"></use></svg>
        <span>Ordina · <b data-cart-count>0</b></span>
    </button>
</nav>

<?php if (!empty($CFG['demo_badge'])) : ?>
    <p class="demo-badge" data-demo-badge>
        <span class="demo-badge__dot" aria-hidden="true"></span>
        Demo — niente ordini veri
    </p>
<?php endif; ?>

<div class="sweep" data-sweep-panel aria-hidden="true"><span data-sweep-fill></span></div>
