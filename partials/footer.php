<?php

/**
 * FOOTER — pile de stickers (chute physique Matter.js), BOMBA pleine largeur,
 * newsletter, liens, mentions, retour en haut.
 */

declare(strict_types=1);

/** @var array $CFG */
$brand    = $CFG['brand'];
$stickers = $CFG['stickers']['footer'] ?? [];
$letters  = preg_split('//u', (string) $brand['name'], -1, PREG_SPLIT_NO_EMPTY) ?: [];
?>
<footer class="ftr section section--asfalto" id="footer">

    <div class="ftr__arena" data-sticker-arena aria-hidden="true">
        <div class="ftr__arena-in" data-sticker-stage>
            <?php foreach ($stickers as $i => $sid) :
                $x   = round(4 + (($i * 61) % 90), 2);
                $y   = round(6 + (($i * 37) % 62), 2);
                $rot = bomba_rot('stk-' . $i, 26);
                $sz  = 54 + (($i * 13) % 46); ?>
                <svg class="ftr__stk" viewBox="0 0 200 200" data-stk="<?php echo e((string) $sid); ?>"
                     style="--x:<?php echo $x; ?>%;--y:<?php echo $y; ?>%;--rot:<?php echo $rot; ?>deg;--s:<?php echo $sz; ?>px">
                    <use href="#stk-<?php echo e((string) $sid); ?>"></use>
                </svg>
            <?php endforeach; ?>
        </div>
        <div class="ftr__floor" data-sticker-floor></div>
    </div>

    <p class="ftr__word" aria-hidden="true" data-ftr-word>
        <?php foreach ($letters as $li => $ch) : ?>
            <span class="ftr__letter" data-ftr-letter="<?php echo (int) $li; ?>"><?php echo e($ch); ?></span>
        <?php endforeach; ?>
    </p>

    <div class="ftr__grid">

        <form class="nl" data-nl-form novalidate>
            <p class="nl__title" data-kinetic="62,125">Entra nella banda</p>
            <p class="nl__sub">Sconti, novità e qualche stupidaggine. Una mail al mese, giurato.</p>
            <div class="nl__row">
                <label class="sr-only" for="nl-email">La tua email</label>
                <input class="nl__in" id="nl-email" name="email" type="email" placeholder="La tua email"
                       autocomplete="email" required>
                <button class="btn btn--lime btn--ink" type="submit"><span>Ci sto</span></button>
            </div>
            <p class="nl__err" data-nl-err role="alert" hidden></p>
        </form>

        <nav class="ftr__nav" aria-label="Social">
            <?php foreach ($CFG['socials'] as $s) : ?>
                <a class="ftr__social" href="<?php echo e((string) $s['url']); ?>" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-<?php echo e((string) $s['icon']); ?>"></use></svg>
                    <span><?php echo e((string) $s['label']); ?></span>
                    <svg class="ftr__social-go" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-ur"></use></svg>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="ftr__legal">
            <p class="ftr__claim"><?php echo e((string) $brand['tagline']); ?></p>
            <p class="ftr__copy">© <?php echo e((string) $brand['year']); ?> <?php echo e((string) $brand['name']); ?> <?php echo e((string) $brand['subtitle']); ?> — P.IVA <?php echo e((string) $brand['piva']); ?></p>
            <ul class="ftr__links">
                <li><a href="#info" data-scroll-to="#info">Privacy</a></li>
                <li><a href="#info" data-scroll-to="#info">Cookie</a></li>
                <li><a href="#menu" data-scroll-to="#menu">Allergeni</a></li>
            </ul>
            <p class="ftr__demo">Sito dimostrativo — ordini e prenotazioni sono simulati.</p>
        </div>

        <a class="totop" href="#hero" data-scroll-to="#hero" aria-label="Torna su">
            <svg class="totop__ring" viewBox="0 0 200 200" aria-hidden="true" focusable="false">
                <defs><path id="totop-track" d="M100,100 m-70,0 a70,70 0 1,1 140,0 a70,70 0 1,1 -140,0"/></defs>
                <circle class="totop__disc" cx="100" cy="100" r="92"/>
                <text class="totop__txt"><textPath href="#totop-track" startOffset="0">TORNA SU · TORNA SU · </textPath></text>
            </svg>
            <svg class="totop__arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-up"></use></svg>
        </a>
    </div>
</footer>
