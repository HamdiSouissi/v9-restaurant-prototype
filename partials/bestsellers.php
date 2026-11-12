<?php

/**
 * I PIÙ BOMBATI — best-sellers en défilement horizontal piloté par le scroll
 * vertical (section épinglée par ScrollTrigger).
 */

declare(strict_types=1);

/** @var array $CFG */
$best = $CFG['bestsellers'];
?>
<section class="best section section--carta" id="best" aria-labelledby="best-title"
         data-sweep="rosso" style="--sweep:var(--rosso)">

    <div class="best__head">
        <h2 class="h2 h2--split" id="best-title">
            <span class="h2__a" data-kinetic="62,125">I PIÙ</span>
            <span class="h2__b circled">
                <span data-kinetic="62,125">BOMBATI</span>
                <svg class="circled__ring" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><path class="hand-draw" d="M104 22c40 4 68 34 66 74-2 42-38 72-84 74-44 2-74-26-74-62 0-38 30-70 74-78 22-4 44-2 62 6 16 8 24 18 26 30"/></svg>
            </span>
        </h2>
        <p class="best__hint marker">scorri →</p>
    </div>

    <div class="best__pin" data-best-pin>
        <div class="best__track" data-best-track>

            <?php foreach ($best as $i => $card) :
                $bg   = (string) ($card['bg'] ?? 'rosso');
                $fg   = (string) ($card['fg'] ?? 'carta');
                $rot  = bomba_rot('best-' . $i, 2.6);
                $id   = (string) $card['id'];
                $num  = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
            ?>
            <article class="poster" data-poster="<?php echo (int) $i; ?>"
                     style="--card-bg:var(--<?php echo e($bg); ?>);--card-fg:var(--<?php echo e($fg); ?>);--rot:<?php echo e((string) $rot); ?>deg">

                <span class="poster__num" aria-hidden="true">N° <?php echo e($num); ?></span>

                <div class="poster__art">
                    <?php echo bomba_cutout((string) $card['cutout'], $card['name'], 'poster__cut'); ?>
                </div>

                <?php if (!empty($card['sticker'])) : ?>
                    <span class="poster__sticker marker" aria-hidden="true"><?php echo e((string) $card['sticker']); ?></span>
                <?php endif; ?>

                <div class="poster__body">
                    <h3 class="poster__name" data-kinetic="70,125"><?php echo e($card['name']); ?></h3>
                    <p class="poster__desc"><?php echo e($card['desc']); ?></p>

                    <div class="poster__foot">
                        <span class="tag-price"><?php echo e(bomba_money((float) $card['price'])); ?></span>
                        <button class="btn btn--ink btn--sm" type="button" data-add="<?php echo e($id); ?>"
                                aria-label="Aggiungi <?php echo e($card['name']); ?> al sacchetto">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-plus"></use></svg>
                            <span>Aggiungi</span>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>

            <div class="poster poster--end" aria-hidden="true">
                <p class="poster__end-a" data-kinetic="62,125">E POI?</p>
                <p class="poster__end-b marker">il menu completo ti aspetta</p>
                <a class="btn btn--red btn--lg" href="#menu" data-scroll-to="#menu"><span>Vai al menu</span></a>
            </div>
        </div>
    </div>

    <div class="best__progress" aria-hidden="true"><span data-best-bar></span></div>
</section>
