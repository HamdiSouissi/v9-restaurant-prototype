<?php

/**
 * BOMBA — Street Food Italiano · Parma
 * ---------------------------------------------------------------------------
 * One-page. PHP natif 8.x. Aucun framework, aucune base de données.
 * Tout le contenu vient de config.php : changer brand / colors / menu
 * rebrande l'intégralité du site.
 *
 * Démarrage : php -S localhost:8000
 * ---------------------------------------------------------------------------
 */

declare(strict_types=1);

$CFG = require __DIR__ . '/config.php';

/* ========================================================================= *
 * HELPERS
 * ========================================================================= */

/** Échappement systématique de toute sortie. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Format prix « ticket de caisse » à l'italienne.
 * 9.0 -> « € 9 » · 3.5 -> « € 3,50 » · 1.9 -> « € 1,90 »
 */
function bomba_money(float|int $value): string
{
    $v = (float) $value;
    $decimals = (abs($v - round($v)) < 0.0001) ? 0 : 2;
    return '€ ' . number_format($v, $decimals, ',', '.');
}

/** Prix sans le symbole (pour les inputs cachés, JSON-LD…). */
function bomba_number(float|int $value): string
{
    return number_format((float) $value, 2, '.', '');
}

/** Slug sûr pour les id / data-attributes. */
function bomba_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = str_replace(['&', "'", '’'], ['e', '', ''], $value);
    $value = (string) preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
}

/** Rotation « collée à la main » déterministe (même rendu à chaque requête). */
function bomba_rot(string $seed, float $range = 6.0): float
{
    $h = crc32($seed);
    return round((($h % 1000) / 1000 * 2 - 1) * $range, 2);
}

/** URL d'un asset seulement s'il existe vraiment (jamais d'image cassée). */
function bomba_asset(string $relative): ?string
{
    $relative = ltrim($relative, '/');
    $path = __DIR__ . '/' . $relative;
    return is_file($path) ? '/' . $relative : null;
}

/**
 * Détouré d'un plat.
 * Ordre de résolution dans /assets/img/cutouts/ :
 *   1. png / webp / jpg  → vos vraies photos détourées (prioritaires)
 *   2. svg               → illustration vectorielle livrée avec le prototype
 *   3. rien              → null, l'appelant affiche un bloc couleur + le nom
 */
function bomba_cutout_src(string $slug): ?string
{
    foreach (['png', 'webp', 'jpg', 'jpeg', 'svg'] as $ext) {
        $url = bomba_asset("assets/img/cutouts/{$slug}.{$ext}");
        if ($url !== null) {
            return $url;
        }
    }
    return null;
}

/**
 * Affiche un détouré, quel que soit l'état des assets.
 * Jamais d'image cassée : le dernier recours est un bloc couleur de la palette
 * avec le nom du plat en grand.
 */
function bomba_cutout(string $slug, string $label = '', string $class = '', array $attrs = []): string
{
    $class = trim('cutout ' . $class);
    $attr  = '';
    foreach ($attrs as $k => $v) {
        $attr .= ' ' . e($k) . '="' . e((string) $v) . '"';
    }

    $src = bomba_cutout_src($slug);
    if ($src !== null) {
        $alt = $label !== '' ? $label : '';
        return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt) . '"'
            . ($alt === '' ? ' aria-hidden="true"' : '')
            . ' width="1200" height="1200" loading="lazy" decoding="async"' . $attr . '>';
    }

    $name = $label !== '' ? $label : str_replace('-', ' ', $slug);
    return '<span class="cutout cutout--fallback" data-cutout="' . e($slug) . '"' . $attr . '>'
        . '<span class="cutout__fallback-name">' . e($name) . '</span></span>';
}

/** Cherche un article du menu par son id. */
function bomba_item(array $cfg, string $id): ?array
{
    foreach ($cfg['menu'] as $catKey => $cat) {
        foreach ($cat['items'] as $item) {
            if ($item['id'] === $id) {
                return $item + ['cat' => $catKey, 'cat_label' => $cat['label']];
            }
        }
    }
    return null;
}

/** '12:00–15:00' → [720, 900] en minutes (les fermetures après minuit dépassent 1440). */
function bomba_slot_minutes(string $slot): array
{
    $parts = preg_split('/[–—\-]/u', $slot) ?: [];
    if (count($parts) < 2) {
        return [0, 0];
    }
    $a = explode(':', trim($parts[0]));
    $b = explode(':', trim($parts[1]));
    $s = ((int) ($a[0] ?? 0)) * 60 + (int) ($a[1] ?? 0);
    $e = ((int) ($b[0] ?? 0)) * 60 + (int) ($b[1] ?? 0);
    if ($e <= $s) {
        $e += 1440;
    }
    return [$s, $e];
}

/**
 * État d'ouverture à l'instant T.
 * → ['open' => bool, 'label' => 'APERTO ORA' | 'CHIUSO · RIAPRE ALLE 18:00']
 */
function bomba_open_state(array $cfg, ?DateTimeImmutable $now = null): array
{
    $tz   = new DateTimeZone('Europe/Rome');
    $now ??= new DateTimeImmutable('now', $tz);
    $now  = $now->setTimezone($tz);

    $dow  = (int) $now->format('N');              // 1 = lunedì
    $mins = ((int) $now->format('G')) * 60 + (int) $now->format('i');

    $hoursByDow = [];
    foreach ($cfg['hours'] as $h) {
        $hoursByDow[(int) $h['dow']] = $h;
    }

    $slotsOf = static function (int $d) use ($hoursByDow, $cfg): array {
        $day = $hoursByDow[$d] ?? null;
        if ($day === null || !empty($day['closed'])) {
            return [];
        }
        $out = [];
        foreach ((array) ($day['open'] ?? []) as $slot) {
            [$s, $e] = bomba_slot_minutes((string) $slot);
            if ($e > $s) {
                $out[] = [$s, $e, (string) $slot];
            }
        }
        return $out;
    };

    // 1) Ouvert sur un créneau d'aujourd'hui…
    foreach ($slotsOf($dow) as [$s, $e]) {
        if ($mins >= $s && $mins < $e) {
            return ['open' => true, 'label' => 'APERTO ORA', 'until' => $e];
        }
    }
    // 2) …ou sur la nuit d'hier (créneau qui traverse minuit).
    $yDow = $dow === 1 ? 7 : $dow - 1;
    foreach ($slotsOf($yDow) as [$s, $e]) {
        if ($e > 1440 && ($mins + 1440) >= $s && ($mins + 1440) < $e) {
            return ['open' => true, 'label' => 'APERTO ORA', 'until' => $e - 1440];
        }
    }

    // 3) Fermé : prochaine ouverture dans les 7 jours.
    for ($i = 0; $i < 8; $i++) {
        $d    = (($dow - 1 + $i) % 7) + 1;
        $base = $i === 0 ? $mins : -1;
        foreach ($slotsOf($d) as [$s]) {
            if ($s > $base) {
                $hh = str_pad((string) intdiv($s, 60), 2, '0', STR_PAD_LEFT);
                $mm = str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT);
                return [
                    'open'  => false,
                    'label' => 'CHIUSO · RIAPRE ALLE ' . $hh . ':' . $mm,
                    'at'    => $hh . ':' . $mm,
                ];
            }
        }
    }

    return ['open' => false, 'label' => 'CHIUSO', 'at' => ''];
}

/** Libellé d'un jour de la semaine (1 = lunedì, ISO). */
function bomba_day_label(int $dow): string
{
    return ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'][($dow % 7)] ?? '';
}

/** JSON sûr pour injection dans <script>. */
function bomba_json(mixed $data): string
{
    return (string) json_encode(
        $data,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
}

/** Menu aplati pour window.MENU. */
function bomba_menu_payload(array $cfg): array
{
    $items = [];
    $cats  = [];
    foreach ($cfg['menu'] as $key => $cat) {
        $ids = [];
        foreach ($cat['items'] as $item) {
            $ids[] = $item['id'];
            $items[$item['id']] = [
                'id'        => $item['id'],
                'name'      => $item['name'],
                'desc'      => $item['desc'],
                'price'     => (float) $item['price'],
                'price_ui'  => bomba_money((float) $item['price']),
                'cat'       => $key,
                'cat_label' => $cat['label'],
                'tags'      => array_values($item['tags'] ?? []),
                'allergens' => array_values($item['allergens'] ?? []),
                'cutout'    => $item['cutout'] ?? '',
                'note'      => $item['note'] ?? '',
            ];
        }
        $cats[] = ['key' => $key, 'label' => $cat['label'], 'note' => $cat['note'] ?? '', 'items' => $ids];
    }
    return ['items' => $items, 'categories' => $cats];
}

/* ========================================================================= *
 * ÉTAT PAGE
 * ========================================================================= */

$OPEN      = bomba_open_state($CFG);
$MENU      = bomba_menu_payload($CFG);

/* Détourés résolus une bonne fois : id d'article ET slug → URL (ou null).
   Consommé par le panier, le combo et l'aperçu du menu. */
$CUTOUTS = [];
foreach ($MENU['items'] as $itemId => $row) {
    $slug = (string) ($row['cutout'] ?? '');
    $src  = $slug !== '' ? bomba_cutout_src($slug) : null;
    $CUTOUTS[$itemId] = $src;
    if ($slug !== '' && !isset($CUTOUTS[$slug])) {
        $CUTOUTS[$slug] = $src;
    }
}
foreach ($CFG['hero']['collage'] as $piece) {
    $slug = (string) ($piece['cutout'] ?? '');
    if ($slug !== '' && !isset($CUTOUTS[$slug])) {
        $CUTOUTS[$slug] = bomba_cutout_src($slug);
    }
}
$CART_MIN  = (float) $CFG['delivery']['min_order'];
$BASE_PATH = '';

/* ========================================================================= *
 * RENDU
 * ========================================================================= */

require __DIR__ . '/partials/head.php';
?>
<body>

<a class="skip" href="#menu">Vai al menu</a>

<?php require __DIR__ . '/partials/stickers-svg.php'; ?>
<?php require __DIR__ . '/partials/preloader.php'; ?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main id="main">
    <?php require __DIR__ . '/partials/hero.php'; ?>
    <?php require __DIR__ . '/partials/ticker.php'; ?>
    <?php require __DIR__ . '/partials/bestsellers.php'; ?>
    <?php require __DIR__ . '/partials/menu.php'; ?>
    <?php require __DIR__ . '/partials/combo.php'; ?>
    <?php require __DIR__ . '/partials/story.php'; ?>
    <?php require __DIR__ . '/partials/booking.php'; ?>
    <?php require __DIR__ . '/partials/gallery.php'; ?>
    <?php require __DIR__ . '/partials/reviews.php'; ?>
    <?php require __DIR__ . '/partials/info.php'; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
<?php require __DIR__ . '/partials/cart-drawer.php'; ?>
<?php require __DIR__ . '/partials/checkout-modal.php'; ?>
<?php require __DIR__ . '/partials/toast.php'; ?>

<script>
    /* Données du site : une seule source, config.php. */
    window.BOMBA = {
        brand:    <?php echo bomba_json($CFG['brand']); ?>,
        colors:   <?php echo bomba_json($CFG['colors']); ?>,
        contact:  <?php echo bomba_json($CFG['contact']); ?>,
        delivery: <?php echo bomba_json($CFG['delivery']); ?>,
        booking:  <?php echo bomba_json($CFG['booking']); ?>,
        combo:    <?php echo bomba_json($CFG['combo']); ?>,
        stickers: <?php echo bomba_json($CFG['stickers']); ?>,
        gallery:  <?php echo bomba_json($CFG['gallery']); ?>,
        hours:    <?php echo bomba_json($CFG['hours']); ?>,
        open:     <?php echo bomba_json($OPEN); ?>,
        cutouts:  <?php echo bomba_json($CUTOUTS); ?>,
        demo:     <?php echo $CFG['demo_badge'] ? 'true' : 'false'; ?>
    };
    /* window.MENU — rendu PHP, consommé par le JS. */
    window.MENU = <?php echo bomba_json($MENU); ?>;
</script>

<?php
$cdn = $CFG['cdn'];
$libs = [
    $cdn['gsap'] . 'gsap.min.js',
    $cdn['gsap'] . 'ScrollTrigger.min.js',
    $cdn['gsap'] . 'Draggable.min.js',
    $cdn['gsap'] . 'InertiaPlugin.min.js',
    $cdn['gsap'] . 'SplitText.min.js',
    $cdn['lenis'],
    $cdn['swiper_js'],
    $cdn['matter'],
];
foreach ($libs as $lib) : ?>
<script src="<?php echo e($lib); ?>" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php endforeach; ?>

<script src="assets/js/main.js"></script>
<script src="assets/js/kinetic.js"></script>
<script src="assets/js/spray.js"></script>
<script src="assets/js/stickers.js"></script>
<script src="assets/js/cart.js"></script>
<script src="assets/js/combo.js"></script>
<script src="assets/js/booking.js"></script>
<script src="assets/js/gallery.js"></script>

</body>
</html>
