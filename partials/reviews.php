<?php

/**
 * RECENSIONI — stickers et bulles collées en désordre, fond rosa shocking.
 */

declare(strict_types=1);

/** @var array $CFG */
$rev   = $CFG['reviews'];
$items = $rev['items'];

// Position « collée à la main » : 2 colonnes irrégulières + jitter.
$slots = [
    ['x' => 3,  'y' => 6],
    ['x' => 40, 'y' => 2],
    ['x' => 66, 'y' => 14],
    ['x' => 14, 'y' => 47],
    ['x' => 48, 'y' => 44],
];
?>
<section class="rev section section--rosa" id="recensioni" aria-labelledby="rev-title"
         data-sweep="senape" style="--sweep:var(--senape)">

    <div class="rev__head">
        <h2 class="h2 h2--split" id="rev-title">
            <span class="h2__a sr-only">Cosa dicono di noi</span>
            <span class="rev__score" aria-hidden="true"><?php echo e((string) $rev['rating']); ?></span>
            <span class="rev__max" aria-hidden="true">/ <?php echo e((string) $rev['rating_max']); ?></span>
        </h2>

        <p class="rev__stars" role="img" aria-label="Valutazione <?php echo e((string) $rev['rating']); ?> su <?php echo e((string) $rev['rating_max']); ?>">
            <?php for ($s = 0; $s < 5; $s++) : ?>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="<?php echo $s === 4 ? 'star star--cut' : 'star'; ?>"><use href="#ico-star"></use></svg>
            <?php endfor; ?>
            <span class="rev__count"><?php echo e((string) $rev['count']); ?></span>
        </p>
    </div>

    <div class="rev__wall">
        <?php foreach ($items as $i => $r) :
            $pos = $slots[$i % count($slots)];
            $bg  = (string) ($r['bg'] ?? 'carta');
            $rot = (float) ($r['rot'] ?? bomba_rot('rev-' . $i, 6));
            $jx  = bomba_rot('revx-' . $i, 4);
            $jy  = bomba_rot('revy-' . $i, 5);
        ?>
        <blockquote class="bub" data-bub="<?php echo (int) $i; ?>"
                    style="--bg:var(--<?php echo e($bg); ?>);--rot:<?php echo e((string) $rot); ?>deg;--x:<?php echo e((string) round($pos['x'] + $jx, 2)); ?>%;--y:<?php echo e((string) round($pos['y'] + $jy, 2)); ?>%">
            <span class="bub__tail" aria-hidden="true"></span>
            <p class="bub__txt">« <?php echo e((string) $r['text']); ?> »</p>
            <footer class="bub__who">
                <span class="bub__stars" aria-hidden="true">
                    <?php for ($s = 0; $s < 5; $s++) : ?><svg viewBox="0 0 24 24"><use href="#ico-star"></use></svg><?php endfor; ?>
                </span>
                <cite class="bub__name"><?php echo e((string) $r['author']); ?></cite>
            </footer>
        </blockquote>
        <?php endforeach; ?>

        <div class="rev__stampwrap" aria-hidden="true">
            <svg class="rev__stamp" viewBox="0 0 200 200"><use href="#stk-stella-fritto"></use></svg>
        </div>
    </div>
</section>
