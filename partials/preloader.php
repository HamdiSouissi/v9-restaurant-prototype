<?php

/**
 * Preloader — ≤ 1,8 s, une seule fois par session.
 * Fond rouge pomodoro, « BOMBA » qui respire en largeur, étoile qui éclate
 * et révèle la page par clip-path: circle().
 */

declare(strict_types=1);

/** @var array $CFG */
$brand = $CFG['brand'];
?>
<div class="preloader" id="preloader" aria-hidden="true">
    <div class="preloader__bg"></div>

    <div class="preloader__word">
        <span class="preloader__letter" data-pl>B</span><span class="preloader__letter" data-pl>O</span><span class="preloader__letter" data-pl>M</span><span class="preloader__letter" data-pl>B</span><span class="preloader__letter" data-pl>A</span>
    </div>

    <svg class="preloader__burst" viewBox="0 0 240 240" aria-hidden="true" focusable="false">
        <path class="burst-star" d="M120 6l14 44 46-16-24 42 44 16-44 16 24 42-46-16-14 44-14-44-46 16 24-42-44-16 44-16-24-42 46 16z"/>
    </svg>

    <p class="preloader__counter">
        <span class="preloader__label">CARICAMENTO DEL FRITTO…</span>
        <span class="preloader__pct" id="preloaderPct">0%</span>
    </p>
</div>
