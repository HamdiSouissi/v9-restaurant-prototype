<?php

/**
 * /api/booking.php — réservation de table simulée.
 *
 * POST (JSON ou form) :
 *   name, phone, email, date (AAAA-MM-GG), time (HH:MM), guests (1–20),
 *   occasion?, notes?, privacy ("1")
 *
 * → {"ok":true,"code":"BMB-T-0712", …}
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

function api_str(array $in, string $key, int $max = 240): string
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

$raw = file_get_contents('php://input');
$in  = [];
if (is_string($raw) && $raw !== '' && str_starts_with(ltrim($raw), '{')) {
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        api_fail('body', 'JSON non valido.', 400);
    }
    $in = $decoded;
} elseif (!empty($_POST)) {
    $in = $_POST;
}
if ($in === []) {
    api_fail('body', 'Mancano i dati della prenotazione.', 400);
}

$bk = $CFG['booking'];

/* ------------------------------------------------------------- champs --- */
$name     = api_str($in, 'name', 80);
$phone    = api_str($in, 'phone', 32);
$email    = api_str($in, 'email', 160);
$date     = api_str($in, 'date', 10);
$time     = api_str($in, 'time', 5);
$occasion = api_str($in, 'occasion', 60);
$notes    = api_str($in, 'notes', 400);
$privacy  = (string) ($in['privacy'] ?? '') !== '' && (string) ($in['privacy'] ?? '') !== '0';
$guests   = (int) ($in['guests'] ?? 0);

if ($name === '' || preg_match('/^[A-Za-zÀ-ÿ0-9\'\-\. ]{2,80}$/u', $name) !== 1) {
    api_fail('name', 'Ci serve un nome vero, dai.');
}
$digits = preg_replace('/\D+/', '', $phone);
if ($digits === null || strlen($digits) < 9 || strlen($digits) > 15) {
    api_fail('phone', 'Ci serve un numero valido per chiamarti.');
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    api_fail('email', 'Email strana: ricontrolla.');
}
if (!in_array($occasion, (array) $bk['occasions'], true)) {
    $occasion = (string) ($bk['occasions'][0] ?? 'Nessuna');
}
if (!$privacy) {
    api_fail('privacy', 'Senza ok sulla privacy non possiamo tenerti il tavolo.');
}

/* --------------------------------------------------------------- date --- */
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
    api_fail('date', 'Scegli un giorno dal calendario.');
}
$dt = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('Europe/Rome'));
if ($dt === false || $dt->format('Y-m-d') !== $date) {
    api_fail('date', 'Quella data non esiste.');
}
$now   = new DateTimeImmutable('now', new DateTimeZone('Europe/Rome'));
$today = $now->setTime(0, 0, 0);
if ($dt < $today) {
    api_fail('date', 'Quel giorno è già passato.');
}
if ($dt > $today->modify('+6 months')) {
    api_fail('date', 'Massimo 6 mesi in avanti.');
}
if ((int) $dt->format('N') === (int) $bk['closed_dow']) {
    api_fail('date', 'Lunedì siamo chiusi. Riproviamo da martedì.');
}

/* --------------------------------------------------------------- ora ---- */
if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) !== 1) {
    api_fail('time', 'Scegli un orario.');
}
[$hh, $mm] = array_map('intval', explode(':', $time));
$mins = $hh * 60 + $mm;

$inRange = false;
foreach (['lunch', 'dinner'] as $grp) {
    [$fh, $fm] = array_map('intval', explode(':', $bk[$grp]['from']));
    [$th, $tm] = array_map('intval', explode(':', $bk[$grp]['to']));
    $s = $fh * 60 + $fm;
    $e = $th * 60 + $tm;
    if ($e <= $s) {
        $e += 1440;
    }
    $m2 = $mins;
    if ($m2 < $s && $e > 1440) {
        $m2 += 1440;              // 00:30 appartient au service du soir
    }
    if ($m2 >= $s && $m2 <= $e) {
        $inRange = true;
        break;
    }
}
if (!$inRange) {
    api_fail('time', 'Quell\'ora è fuori dai nostri turni.');
}
if ($dt->format('Y-m-d') === $today->format('Y-m-d')) {
    $nowMin = ((int) $now->format('G')) * 60 + (int) $now->format('i');
    if ($mins <= $nowMin + 20) {
        api_fail('time', 'Troppo tardi per oggi: chiamaci direttamente.');
    }
}

/* ---------------------------------------------------------- personnes --- */
if ($guests < 1 || $guests > 20) {
    api_fail('guests', 'Da 1 a 20 persone. Poi serve una sala tutta vostra.');
}
if ($guests > (int) $bk['guests_max']) {
    $notes = trim('Gruppo numeroso — richiamiamo per conferma. ' . $notes);
}

/* -------------------------------------------------------------- code ---- */
$prefix = (string) ($bk['code_prefix'] ?? 'BMB-T-');
$code   = $prefix . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

$itDays = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];
$itMon  = ['GENNAIO', 'FEBBRAIO', 'MARZO', 'APRILE', 'MAGGIO', 'GIUGNO',
           'LUGLIO', 'AGOSTO', 'SETTEMBRE', 'OTTOBRE', 'NOVEMBRE', 'DICEMBRE'];

api_out([
    'ok'       => true,
    'code'     => $code,
    'name'     => $name,
    'phone'    => $phone,
    'email'    => $email,
    'date'     => $date,
    'date_ui'  => strtoupper($itDays[(int) $dt->format('w')]) . ' '
                 . $dt->format('d') . ' ' . $itMon[(int) $dt->format('n') - 1],
    'time'     => $time,
    'guests'   => $guests,
    'occasion' => $occasion,
    'notes'    => $notes,
    'address'  => (string) $CFG['contact']['address'],
    'ics'      => [
        'summary'     => $CFG['brand']['name'] . ' — tavolo per ' . $guests,
        'description' => 'Codice prenotazione ' . $code . '. ' . $CFG['brand']['tagline'],
        'location'    => (string) $CFG['contact']['address'],
        'start'       => $date . 'T' . $time . ':00',
    ],
    'demo'     => true,
    'message'  => 'Tavolo bloccato. Ti aspettiamo, non fare tardi.',
]);
