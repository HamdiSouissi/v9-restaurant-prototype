<?php

/**
 * Ticker — bandes de rubalise croisées.
 * La vitesse est pilotée par la vélocité Lenis (voir main.js).
 */

declare(strict_types=1);

/** @var array $CFG */

$build = static function (array $words, string $sep): string {
    $out = [];
    foreach ($words as $w) {
        $out[] = '<span class="ticker__w">' . e((string) (is_array($w) ? $w['text'] : $w)) . '</span>'
               . '<span class="ticker__sep" aria-hidden="true">' . e($sep) . '</span>';
    }
    return implode('', $out);
};

$t1 = $build($CFG['ticker'], '✶');
$t2 = $build($CFG['ticker_2'], '✶');
?>
<section class="ticker" id="ticker" aria-hidden="true">
    <div class="ticker__band ticker__band--lime ticker__band--a" data-marquee="a">
        <div class="ticker__track" data-marquee-track>
            <div class="ticker__run"><?php echo $t1; ?></div>
            <div class="ticker__run"><?php echo $t1; ?></div>
        </div>
    </div>
    <div class="ticker__band ticker__band--rosso ticker__band--b" data-marquee="b">
        <div class="ticker__track" data-marquee-track>
            <div class="ticker__run"><?php echo $t2; ?></div>
            <div class="ticker__run"><?php echo $t2; ?></div>
        </div>
    </div>
</section>
