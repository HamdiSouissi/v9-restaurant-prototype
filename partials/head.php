<?php

/**
 * <head> — SEO, Open Graph, JSON-LD Restaurant, fonts, palette CSS.
 * Tout est piloté par config.php.
 */

declare(strict_types=1);

/** @var array $CFG */
$brand  = $CFG['brand'];
$colors = $CFG['colors'];
$cssVars = [];
foreach ($colors as $name => $hex) {
    $cssVars[] = '--' . e($name) . ':' . e($hex) . ';';
}
$rootVars = implode("\n        ", $cssVars);

$menuForSchema = [];
foreach ($CFG['menu'] as $cat) {
    foreach ($cat['items'] as $item) {
        $menuForSchema[] = [
            '@type'    => 'MenuItem',
            'name'     => $item['name'],
            'description' => $item['desc'],
            'offers'   => [
                '@type'         => 'Offer',
                'price'         => bomba_number((float) $item['price']),
                'priceCurrency' => 'EUR',
            ],
        ];
    }
}

$openingHours = [];
foreach ($CFG['hours'] as $h) {
    if (!empty($h['closed'])) {
        continue;
    }
    $dowMap = [1 => 'Mo', 2 => 'Tu', 3 => 'We', 4 => 'Th', 5 => 'Fr', 6 => 'Sa', 7 => 'Su'];
    foreach ((array) $h['open'] as $slot) {
        [$s, $e] = bomba_slot_minutes((string) $slot);
        $fmt = static fn (int $m): string => sprintf('%02d:%02d', intdiv($m % 1440, 60), $m % 60);
        $openingHours[] = $dowMap[(int) $h['dow']] . ' ' . $fmt($s) . '-' . $fmt($e);
    }
}

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'Restaurant',
    'name'     => $brand['name'] . ' — ' . $brand['subtitle'],
    'description' => $brand['meta_desc'],
    'servesCuisine' => 'Street food italiano',
    'priceRange' => '€',
    'image'    => bomba_asset($brand['og_image']) ?? '',
    'telephone' => $CFG['contact']['phone'],
    'email'    => $CFG['contact']['email'],
    'address'  => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $CFG['contact']['street'],
        'postalCode'      => $CFG['contact']['zip'],
        'addressLocality' => $CFG['contact']['city'],
        'addressRegion'   => $CFG['contact']['province'],
        'addressCountry'  => 'IT',
    ],
    'openingHours'    => $openingHours,
    'acceptsReservations' => 'True',
    'hasMenu' => [
        '@type'          => 'Menu',
        'name'           => 'Menu ' . $brand['name'],
        'hasMenuSection' => array_values(array_map(
            static fn (array $cat): array => [
                '@type'          => 'MenuSection',
                'name'           => $cat['label'],
                'description'    => $cat['note'] ?? '',
                'hasMenuItem'    => array_values(array_filter(
                    $menuForSchema,
                    static fn (array $mi): bool => true
                )),
            ],
            $CFG['menu']
        )),
    ],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="<?php echo e($brand['theme_color']); ?>">
<meta name="color-scheme" content="dark light">

<title><?php echo e($brand['meta_title']); ?></title>
<meta name="description" content="<?php echo e($brand['meta_desc']); ?>">
<meta name="author" content="<?php echo e($brand['name']); ?>">
<link rel="canonical" href="<?php echo e('https://' . (($_SERVER['HTTP_HOST'] ?? 'localhost')) . '/'); ?>">

<meta property="og:type" content="restaurant">
<meta property="og:site_name" content="<?php echo e($brand['name']); ?>">
<meta property="og:title" content="<?php echo e($brand['meta_title']); ?>">
<meta property="og:description" content="<?php echo e($brand['meta_desc']); ?>">
<meta property="og:image" content="<?php echo e(bomba_asset($brand['og_image']) ?? ''); ?>">
<meta property="og:locale" content="it_IT">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($brand['meta_title']); ?>">
<meta name="twitter:description" content="<?php echo e($brand['meta_desc']); ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="<?php echo e($CFG['cdn']['fonts']); ?>">
<link rel="stylesheet" href="<?php echo e($CFG['cdn']['swiper_css']); ?>">
<link rel="stylesheet" href="assets/css/style.css">

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' fill='%23FF3B1F'/%3E%3Ctext x='16' y='24' font-family='Arial Black,Arial' font-size='20' font-weight='900' text-anchor='middle' fill='%23F5F1E8'%3EB%3C/text%3E%3C/svg%3E">

<script type="application/ld+json"><?php echo bomba_json($jsonLd); ?></script>

<script>
    /* Anti-FOUC : marque l'état JS + détecte reduced-motion + preloader déjà vu. */
    (function () {
        var d = document.documentElement;
        d.classList.add('js');
        try {
            if (sessionStorage.getItem('bomba:seen') === '1') { d.classList.add('preloader-done'); }
        } catch (e) { d.classList.add('preloader-done'); }
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            d.classList.add('reduced-motion');
        }
        if (!('CSS' in window) || !CSS.supports('font-variation-settings', '"wdth" 100')) {
            d.classList.add('no-varfont');
        }
        /* Filet de sécurité : si le JS plante, le contenu redevient lisible. */
        setTimeout(function () { d.classList.add('boot-timeout'); }, 4000);
    })();
</script>

<style>
    /* Palette injectée depuis config.php — changer les couleurs rebrando tutto. */
    :root {
        <?php echo $rootVars; ?>

        --carta-dim: color-mix(in srgb, var(--carta) 88%, var(--inchiostro));
        --asfalto-soft: color-mix(in srgb, var(--asfalto) 88%, var(--carta));

        --font-display: 'Archivo', 'Arial Black', 'Helvetica Neue', sans-serif;
        --font-body: 'Inter Tight', system-ui, -apple-system, 'Segoe UI', sans-serif;
        --font-mono: 'DM Mono', ui-monospace, 'SFMono-Regular', Menlo, monospace;
        --font-marker: 'Permanent Marker', 'Bradley Hand', cursive;

        --offset: 6px;
        --offset-sm: 4px;
        --shadow-card: var(--offset) var(--offset) 0 var(--ombra);
        --shadow-btn: var(--offset-sm) var(--offset-sm) 0 var(--ombra);
        --radius-pill: 999px;
        --edge: 3px solid var(--inchiostro);

        --header-h: 64px;
        --pad: clamp(16px, 4vw, 56px);
        --maxw: 1600px;
    }
</style>
</head>
