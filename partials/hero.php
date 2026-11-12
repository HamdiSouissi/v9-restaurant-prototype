<?php

/**
 * HERO — « affiche explosive »
 * Titre géant qui déborde, collage de détourés en parallaxe, spray canvas,
 * enseigne néon lente, bande ticket en bas.
 */

declare(strict_types=1);

/** @var array $CFG */
$hero  = $CFG['hero'];
$brand = $CFG['brand'];
$photo = bomba_asset($hero['photo']);
?>
<section class="hero section section--asfalto" id="hero" aria-labelledby="hero-title">

    <?php if ($photo !== null) : ?>
        <img class="hero__photo" src="<?php echo e($photo); ?>" alt="" width="1600" height="1067"
             fetchpriority="high" decoding="async">
    <?php endif; ?>

    <canvas class="hero__spray" id="sprayCanvas" aria-hidden="true"></canvas>
    <div class="grain" aria-hidden="true"></div>

    <h1 class="hero__title" id="hero-title">
        <?php foreach ($hero['lines'] as $li => $line) : ?>
            <span class="hero__line" data-hero-line="<?php echo (int) $li; ?>">
                <?php foreach (preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) as $ci => $ch) : ?>
                    <span class="hero__ch" data-hero-ch="<?php echo (int) $li . '-' . (int) $ci; ?>"><?php echo e($ch); ?></span>
                <?php endforeach; ?>
            </span>
        <?php endforeach; ?>
    </h1>

    <div class="hero__collage" aria-hidden="false">
        <?php foreach ($hero['collage'] as $i => $piece) :
            $slug  = (string) $piece['cutout'];
            $item  = !empty($piece['add']) ? bomba_item($CFG, (string) $piece['add']) : null;
            $rot   = (float) ($piece['rot'] ?? 0);
            $depth = (float) ($piece['depth'] ?? 1);
            $style = sprintf(
                'left:%s%%;top:%s%%;width:%s%%;--rot:%sdeg;--depth:%s;z-index:%d',
                (string) $piece['x'],
                (string) $piece['y'],
                (string) $piece['w'],
                (string) $rot,
                (string) $depth,
                (int) ($piece['z'] ?? 3)
            );

            if ($item !== null) : ?>
                <button class="hero__piece hero__piece--btn" type="button" style="<?php echo e($style); ?>"
                        data-parallax="<?php echo e((string) $depth); ?>"
                        data-add="<?php echo e($item['id']); ?>"
                        data-cutout="<?php echo e($slug); ?>"
                        aria-label="Aggiungi <?php echo e($item['name']); ?> al sacchetto">
                    <?php echo bomba_cutout($slug, $item['name'], 'hero__piece-img'); ?>
                    <span class="hero__piece-price"><?php echo e(bomba_money((float) $item['price'])); ?></span>
                </button>
            <?php else : ?>
                <span class="hero__piece" style="<?php echo e($style); ?>" data-parallax="<?php echo e((string) $depth); ?>" aria-hidden="true">
                    <?php echo bomba_cutout($slug, '', 'hero__piece-img'); ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="hero__neon" data-neon aria-hidden="true">
        <span class="hero__neon-txt"><?php echo e($hero['neon']); ?></span>
    </div>

    <div class="hero__copy">
        <p class="hero__lead"><?php echo e($brand['claim']); ?></p>

        <div class="hero__actions">
            <a class="btn btn--red btn--lg" href="#menu" data-scroll-to="#menu">
                <span><?php echo e($hero['cta1']); ?></span>
            </a>
            <a class="btn btn--ghost btn--lg" href="#prenota" data-scroll-to="#prenota">
                <span><?php echo e($hero['cta2']); ?></span>
            </a>

            <span class="hero__tag">
                <svg class="hero__tag-arrow" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><path class="hand-draw" d="M18 152C56 152 92 128 118 92c14-20 24-40 30-58"/><path class="hand-draw hand-draw--2" d="M120 30l34 2-8 34"/></svg>
                <span class="hero__tag-txt"><?php echo e($hero['sticker']); ?></span>
            </span>
        </div>
    </div>

    <p class="hero__strip">
        <span><?php echo e($hero['strip']); ?></span>
        <span class="hero__strip-dup" aria-hidden="true"><?php echo e($hero['strip']); ?></span>
    </p>

    <a class="hero__scroll" href="#ticker" data-scroll-to="#ticker" aria-label="Scorri al menu">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#ico-arrow-up"></use></svg>
    </a>
</section>
