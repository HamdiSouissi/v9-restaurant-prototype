<?php

/**
 * CHI SIAMO — mise en page d'affiche, photo scotchée, compteurs, tampon.
 */

declare(strict_types=1);

/** @var array $CFG */
$story = $CFG['story'];
$photo = bomba_asset((string) $story['photo']);
?>
<section class="story section section--rosso" id="story" aria-labelledby="story-title"
         data-sweep="carta" style="--sweep:var(--carta)">

    <div class="story__grid">

        <figure class="polaroid polaroid--big">
            <span class="tape tape--tl" aria-hidden="true"></span>
            <span class="tape tape--br" aria-hidden="true"></span>
            <?php if ($photo !== null) : ?>
                <img class="polaroid__img polaroid__img--bw" src="<?php echo e($photo); ?>"
                     alt="Il crew di <?php echo e($CFG['brand']['name']); ?> dietro al banco, di notte."
                     width="1200" height="1500" loading="lazy" decoding="async">
            <?php else : ?>
                <span class="polaroid__ph" aria-hidden="true"><?php echo e($CFG['brand']['name']); ?></span>
            <?php endif; ?>
            <figcaption class="polaroid__cap marker">il crew, ore 23:40</figcaption>
        </figure>

        <div class="story__copy">
            <h2 class="h2 h2--split" id="story-title">
                <?php foreach ((array) $story['title'] as $ti => $part) : ?>
                    <span class="h2__<?php echo $ti === 0 ? 'a' : 'b'; ?>" data-kinetic="62,125"><?php echo e($part); ?></span>
                <?php endforeach; ?>
            </h2>

            <p class="story__txt"><?php echo e($story['text']); ?></p>

            <ul class="story__stats">
                <?php foreach ((array) $story['stats'] as $si => $s) :
                    $pre = (string) ($s['prefix'] ?? '');
                    $suf = (string) ($s['suffix'] ?? '');
                    $sep = !empty($s['sep']) ? '1' : '0'; ?>
                    <li class="stat" style="--i:<?php echo (int) $si; ?>">
                        <span class="stat__v">
                            <?php echo e($pre); ?><span data-count="<?php echo (int) $s['value']; ?>" data-sep="<?php echo e($sep); ?>">0</span><?php echo e($suf); ?>
                        </span>
                        <span class="stat__l"><?php echo e($s['label']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="story__stamp" data-stamp>
                <svg viewBox="0 0 200 200" role="img" aria-label="<?php echo e($story['stamp']); ?>">
                    <title><?php echo e($story['stamp']); ?></title>
                    <use href="#stk-nonna"></use>
                </svg>
            </div>
        </div>
    </div>
</section>
