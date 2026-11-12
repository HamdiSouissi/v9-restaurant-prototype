<?php

/**
 * Header « ticket » fixe + menu mobile plein écran.
 */

declare(strict_types=1);

/** @var array $CFG */
/** @var array $OPEN */
$brand = $CFG['brand'];

$nav = [
    ['#menu',    'Menu'],
    ['#combo',   'Combo'],
    ['#story',   'Chi siamo'],
    ['#prenota', 'Prenota'],
    ['#info',    'Dove siamo'],
];

$statusOpen  = (bool) ($OPEN['open'] ?? false);
$statusLabel = (string) ($OPEN['label'] ?? '');
$scatter     = $CFG['stickers']['scatter'] ?? [];
?>
<header class="hdr" id="hdr" data-header>
    <div class="hdr__in">

        <a class="hdr__logo" href="#hero" aria-label="<?php echo e($brand['name'] . ' — ' . $brand['subtitle']); ?>">
            <span class="hdr__logo-text" data-kinetic="62,125"><?php echo e($brand['name']); ?></span>
            <svg class="hdr__badge" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
                <defs>
                    <path id="hdr-badge-track" d="M50,50 m-33,0 a33,33 0 1,1 66,0 a33,33 0 1,1 -66,0"/>
                </defs>
                <circle class="badge-disc" cx="50" cy="50" r="46"/>
                <text class="badge-text"><textPath href="#hdr-badge-track" startOffset="0">★ PARMA ★ PARMA </textPath></text>
                <path class="badge-star" d="M50 38l3.6 8 8.4 1-6.2 5.8 1.7 8.4L50 57l-7.5 4.2 1.7-8.4L38 47l8.4-1z"/>
            </svg>
        </a>

        <nav class="hdr__nav" aria-label="Navigazione principale">
            <?php foreach ($nav as [$href, $label]) : ?>
                <a class="hdr__link" href="<?php echo e($href); ?>"><?php echo e($label); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="hdr__right">

            <p class="status<?php echo $statusOpen ? ' status--open' : ' status--closed'; ?>" data-status>
                <span class="status__dot" aria-hidden="true"></span>
                <span class="status__txt"><?php echo e($statusLabel); ?></span>
            </p>

            <a class="btn btn--red hdr__ordina" href="#menu" data-scroll-to="#menu">
                <span>Ordina</span>
            </a>

            <button class="bag" type="button" data-bag-open
                    aria-label="Apri il sacchetto — articoli nel sacchetto: 0"
                    aria-haspopup="dialog">
                <svg class="bag__ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-bag"></use></svg>
                <span class="bag__count" data-cart-count aria-hidden="true">0</span>
            </button>

            <button class="burger" type="button" data-nav-open aria-expanded="false" aria-controls="mnav">
                <span class="burger__txt">Menu</span>
                <svg class="burger__ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-burger"></use></svg>
            </button>
        </div>
    </div>
</header>

<div class="mnav" id="mnav" data-mnav hidden>
    <div class="mnav__scrim" data-nav-close></div>
    <div class="mnav__panel" role="dialog" aria-modal="true" aria-label="Menu di navigazione">
        <button class="mnav__close" type="button" data-nav-close aria-label="Chiudi il menu">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-close"></use></svg>
        </button>

        <nav class="mnav__links" aria-label="Navigazione mobile">
            <?php foreach ($nav as $i => [$href, $label]) : ?>
                <a class="mnav__link" href="<?php echo e($href); ?>" data-nav-close data-mnav-link="<?php echo (int) $i; ?>">
                    <span class="mnav__link-n">0<?php echo $i + 1; ?></span>
                    <span class="mnav__link-t" data-kinetic="62,125"><?php echo e($label); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="mnav__foot">
            <p class="mnav__status">
                <span class="status__dot" aria-hidden="true"></span>
                <?php echo e($statusLabel); ?>
            </p>
            <a class="mnav__tel" href="tel:<?php echo e($CFG['contact']['phone']); ?>"><?php echo e($CFG['contact']['phone_ui']); ?></a>
        </div>

        <div class="mnav__stickers" aria-hidden="true">
            <?php foreach ($scatter as $i => $sid) :
                $left = 6 + (($i * 37) % 78);
                $top  = 8 + (($i * 53) % 74);
                $rot  = bomba_rot('mnav-' . $i, 18); ?>
                <svg class="mnav__sticker" viewBox="0 0 200 200" style="left:<?php echo $left; ?>%;top:<?php echo $top; ?>%;--rot:<?php echo $rot; ?>deg">
                    <use href="#stk-<?php echo e($sid); ?>"></use>
                </svg>
            <?php endforeach; ?>
        </div>
    </div>
</div>
