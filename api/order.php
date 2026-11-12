<?php

/**
 * /api/order.php — prise de commande simulée.
 *
 * POST (JSON ou form) :
 *   name, phone, email, mode (delivery|pickup), address?, rider_note?,
 *   pickup_time?, payment, items[] = {id, qty}
 *
 * Les prix sont recalculés côté serveur à partir de config.php : le client
 * ne peut pas inventer un tarif. L'identifiant d'un combo a la forme
 * « combo:main|fried|drink ».
 *
 * → {"ok":true,"code":"BMB-4821","total":15.8,…}
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$CFG = require dirname(__DIR__) . '/config.php';

function api_out(array $payload, int $status = 200): never
{
    if ($status !== 200) {
        http_response_code($status);
    }
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

function api_fail(string $field, string $message, int $status = 422): never
{
    api_out(['ok' => false, 'field' => $field, 'error' => $message], $status);
}

/** Lit le corps de la requête, JSON ou form-urlencoded. */
function api_input(): array
{
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '' && str_starts_with(ltrim($raw), '{')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        api_fail('body', 'JSON non valido.', 400);
    }
    if (!empty($_POST)) {
        return $_POST;
    }
    return [];
}

function api_str(array $in, string $key, int $max = 200): string
{
    $v = $in[$key] ?? '';
    if (!is_scalar($v)) {
        return '';
    }
    $v = trim((string) $v);
    return mb_strlen($v) > $max ? mb_substr($v, 0, $max) : $v;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    api_out(['ok' => false, 'error' => 'Metodo non consentito.'], 405);
}

$in = api_input();

/* ---------------------------------------------------------- identité ---- */
$name  = api_str($in, 'name', 80);
$phone = api_str($in, 'phone', 32);
$email = api_str($in, 'email', 160);
$mode  = api_str($in, 'mode', 16);

if ($name === '' || mb_strlen($name) < 2) {
    api_fail('name', 'Ci serve un nome per chiamarti.');
}
if (preg_match('/^[A-Za-zÀ-ÿ0-9\'\-\. ]{2,80}$/u', $name) !== 1) {
    api_fail('name', 'Nome non valido: solo lettere.');
}
$digits = preg_replace('/\D+/', '', $phone);
if ($digits === null || strlen($digits) < 9 || strlen($digits) > 15) {
    api_fail('phone', 'Ci serve un numero valido per chiamarti.');
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    api_fail('email', 'Email strana: ricontrolla.');
}
if (!in_array($mode, ['delivery', 'pickup'], true)) {
    api_fail('mode', 'Scegli consegna o asporto.');
}

/* ------------------------------------------------- spécifiques au mode -- */
$address = api_str($in, 'address', 200);
if ($mode === 'delivery' && mb_strlen($address) < 6) {
    api_fail('address', 'Senza indirizzo il rider gira a vuoto.');
}

$pickup = api_str($in, 'pickup_time', 16);
if ($mode === 'pickup' && $pickup !== 'asap' && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $pickup) !== 1) {
    api_fail('pickup_time', 'Orario non valido.');
}

/* ---------------------------------------------------------- paiement ---- */
$payIds  = array_column((array) $CFG['delivery']['payment_methods'], 'id');
$payment = api_str($in, 'payment', 24);
if (!in_array($payment, $payIds, true)) {
    api_fail('payment', 'Metodo di pagamento non riconosciuto.');
}

/* ------------------------------------------------------------- lignes --- */
$rawItems = $in['items'] ?? [];
if (!is_array($rawItems) || $rawItems === []) {
    api_fail('items', 'Il sacchetto è vuoto.');
}
if (count($rawItems) > 60) {
    api_fail('items', 'Troppa roba tutta insieme: massimo 60 righe.');
}

/** Index des articles du menu, par id. */
$menuIndex = [];
foreach ($CFG['menu'] as $catKey => $cat) {
    foreach ((array) $cat['items'] as $item) {
        $menuIndex[(string) $item['id']] = [
            'name'  => (string) $item['name'],
            'price' => (float) $item['price'],
            'cat'   => (string) $catKey,
        ];
    }
}

$discount = (float) ($CFG['combo']['discount'] ?? 0);
$lines    = [];
$subtotal = 0.0;

foreach ($rawItems as $raw) {
    if (!is_array($raw)) {
        api_fail('items', 'Riga ordine non valida.');
    }
    $id  = trim((string) ($raw['id'] ?? ''));
    $qty = (int) ($raw['qty'] ?? 0);
    if ($qty < 1 || $qty > 99) {
        api_fail('items', 'Quantità fuori scala (1–99).');
    }

    if (str_starts_with($id, 'combo:')) {
        $parts = explode('|', substr($id, 6));
        if (count($parts) !== 3) {
            api_fail('items', 'Combo incompleto.');
        }
        $sum   = 0.0;
        $names = [];
        foreach ($parts as $pid) {
            $pid = trim($pid);
            if (!isset($menuIndex[$pid])) {
                api_fail('items', 'Combo con un piatto che non esiste.');
            }
            $sum    += $menuIndex[$pid]['price'];
            $names[] = $menuIndex[$pid]['name'];
        }
        $cut   = min($discount, max(0, $sum - 0.5));
        $unit  = round($sum - $cut, 2);
        $lines[] = [
            'id'    => $id,
            'name'  => 'Combo ' . $CFG['brand']['name'],
            'note'  => implode(' + ', $names),
            'qty'   => $qty,
            'unit'  => $unit,
            'total' => round($unit * $qty, 2),
            'saved' => round($cut * $qty, 2),
        ];
        $subtotal += $unit * $qty;
        continue;
    }

    if (!isset($menuIndex[$id])) {
        api_fail('items', 'Questo piatto non è nel menu.');
    }
    $unit = $menuIndex[$id]['price'];
    $lines[] = [
        'id'    => $id,
        'name'  => $menuIndex[$id]['name'],
        'note'  => '',
        'qty'   => $qty,
        'unit'  => $unit,
        'total' => round($unit * $qty, 2),
        'saved' => 0.0,
    ];
    $subtotal += $unit * $qty;
}

$subtotal = round($subtotal, 2);

/* ---------------------------------------------------------- tarifs ------ */
$fee       = 0.0;
$freeFrom  = (float) $CFG['delivery']['free_from'];
$minOrder  = (float) $CFG['delivery']['min_order'];

if ($mode === 'delivery') {
    if ($subtotal < $minOrder) {
        api_fail('items', 'Consegna da ' . number_format($minOrder, 2, ',', '.') . ' € in su.');
    }
    $fee = $subtotal >= $freeFrom ? 0.0 : (float) $CFG['delivery']['fee'];
}

$total = round($subtotal + $fee, 2);

/* -------------------------------------------------------------- code ---- */
$code = 'BMB-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

api_out([
    'ok'        => true,
    'code'      => $code,
    'mode'      => $mode,
    'name'      => $name,
    'lines'     => $lines,
    'subtotal'  => $subtotal,
    'fee'       => $fee,
    'total'     => $total,
    'payment'   => $payment,
    'eta'       => (string) $CFG['delivery']['eta'],
    'address'   => $mode === 'delivery' ? $address : null,
    'pickup'    => $mode === 'pickup' ? $pickup : null,
    'rider_note'=> api_str($in, 'rider_note', 200),
    'demo'      => true,
    'message'   => $mode === 'delivery'
        ? 'Ordine ricevuto. Il rider sta già scaldando il motore.'
        : 'Ordine ricevuto. Passa quando vuoi, trovi tutto caldo.',
]);
