<?php

/**
 * LA GALLERIA — muro di polaroid draggabili + lightbox + Swiper « cards » mobile.
 */

declare(strict_types=1);

/** @var array $CFG */
$shots = $CFG['gallery'];
$total = count($shots);

// Dispersion « collée à la main » : grille irrégulière + jitter déterministe.
$cols = 4;
$cellW = 100 / $cols;
?>
<section class="gal section section--asfalto gal--concrete" id="galleria" aria-labelledby="gal-title"
         data-sweep="rosa" style="--sweep:var(--rosa)">

    <div class="gal__head">
        <h2 class="h2 h2--split" id="gal-title">
            <span class="h2__a" data-kinetic="62,125">LA</span>
            <span class="h2__b" data-kinetic="62,125">GALLERIA</span>
        </h2>
        <span class="gal__tag marker">#bombaparma</span>
        <p class="gal__hint">Trascina le foto. Clicca per ingrandire.</p>
    </div>

    <div class="wall swiper" data-wall>
        <div class="wall__in swiper-wrapper" data-wall-in>
            <?php foreach ($shots as $i => $shot) :
                $src   = bomba_asset((string) $shot['src']);
                $rot   = (float) ($shot['rot'] ?? bomba_rot('pola-' . $i, 7));
                $row   = intdiv($i, $cols);
                $col   = $i % $cols;
                $jx    = bomba_rot('jx-' . $i, 3.2);
                $jy    = bomba_rot('jy-' . $i, 4.0);
                $left  = round($col * $cellW + 2 + $jx + ($row % 2 === 1 ? $cellW / 2.6 : 0), 2);
                $top   = round($row * 31 + 2 + $jy, 2);
                $size  = (string) ($shot['size'] ?? 'md');
                $pin   = ($i % 3 === 1);
            ?>
            <figure class="pola pola--<?php echo e($size); ?> swiper-slide" data-pola="<?php echo (int) $i; ?>"
                    style="--x:<?php echo e((string) $left); ?>%;--y:<?php echo e((string) $top); ?>%;--rot:<?php echo e((string) $rot); ?>deg"
                    data-src="<?php echo e((string) ($src ?? '')); ?>"
                    data-caption="<?php echo e((string) $shot['caption']); ?>">

                <span class="pola__fix <?php echo $pin ? 'pola__fix--pin' : 'pola__fix--tape'; ?>" aria-hidden="true"></span>

                <button class="pola__btn" type="button" data-pola-open="<?php echo (int) $i; ?>"
                        aria-label="Apri la foto: <?php echo e((string) $shot['caption']); ?>">
                    <?php if ($src !== null) : ?>
                        <img class="pola__img" src="<?php echo e($src); ?>" alt="<?php echo e((string) $shot['caption']); ?>"
                             width="900" height="900" loading="lazy" decoding="async"
                             style="object-position:<?php echo e((string) ($shot['focus'] ?? '50% 50%')); ?>">
                    <?php else : ?>
                        <span class="pola__ph" aria-hidden="true"><?php echo e((string) $shot['caption']); ?></span>
                    <?php endif; ?>
                </button>

                <figcaption class="pola__cap marker"><?php echo e((string) $shot['caption']); ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>

    <p class="gal__count" aria-hidden="true"><span data-gal-n>01</span> / <?php echo str_pad((string) $total, 2, '0', STR_PAD_LEFT); ?></p>
</section>

<div class="lb" data-lb hidden role="dialog" aria-modal="true" aria-label="Foto a schermo intero">
    <button class="lb__close" type="button" data-lb-close aria-label="Chiudi la foto">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-close"></use></svg>
    </button>

    <button class="lb__nav lb__nav--prev" type="button" data-lb-prev aria-label="Foto precedente">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-chev-l"></use></svg>
    </button>

    <figure class="lb__fig">
        <img class="lb__img" alt="" data-lb-img width="1400" height="1400" style="display:none">
        <figcaption class="lb__cap marker" data-lb-cap></figcaption>
    </figure>

    <button class="lb__nav lb__nav--next" type="button" data-lb-next aria-label="Foto successiva">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-chev-r"></use></svg>
    </button>

    <p class="lb__count" data-lb-count aria-live="polite">01/<?php echo str_pad((string) $total, 2, '0', STR_PAD_LEFT); ?></p>
</div>
