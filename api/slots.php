<?php

/**
 * /api/slots.php — créneaux de réservation (simulation déterministe).
 *
 * GET ?date=YYYY-MM-DD[&guests=N]
 * → {"ok":true,"date":"2026-11-14","lunch":[{"time":"12:00","status":"free"}],…}
 *
 * status : free | last (« Ultimi posti ») | full (« Pieno »)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$CFG = require dirname(__DIR__) . '/config.php';

/** Envoie une réponse JSON et termine. */
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

/** Hachage stable : même date → mêmes créneaux (démo reproductible). */
function api_seed(string $key): int
{
    return (int) hexdec(substr(md5($key), 0, 8));
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    api_out(['ok' => false, 'error' => 'Metodo non consentito.'], 405);
}

$bk = $CFG['booking'];

/* ---------------------------------------------------------------- date --- */
$rawDate = trim((string) ($_GET['date'] ?? ''));
if ($rawDate === '') {
    api_out(['ok' => false, 'error' => 'Serve una data.'], 400);
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) !== 1) {
    api_out(['ok' => false, 'error' => 'Data non valida: usa AAAA-MM-GG.'], 400);
}

$dt = DateTimeImmutable::createFromFormat('!Y-m-d', $rawDate, new DateTimeZone('Europe/Rome'));
if ($dt === false || $dt->format('Y-m-d') !== $rawDate) {
    api_out(['ok' => false, 'error' => 'Quella data non esiste.'], 400);
}

$now  = new DateTimeImmutable('now', new DateTimeZone('Europe/Rome'));
$today = $now->setTime(0, 0, 0);
if ($dt < $today) {
    api_out(['ok' => false, 'error' => 'Quel giorno è già passato, purtroppo.'], 400);
}
if ($dt > $today->modify('+6 months')) {
    api_out(['ok' => false, 'error' => 'Troppa fame in anticipo: massimo 6 mesi.'], 400);
}

$dow = (int) $dt->format('N');
if ($dow === (int) $bk['closed_dow']) {
    api_out([
        'ok'     => false,
        'reason' => 'closed',
        'date'   => $rawDate,
        'error'  => 'Lunedì siamo chiusi. Friggiamo anche noi, ogni tanto.',
    ], 200);
}

/* ------------------------------------------------------------- persone --- */
$guests = (int) ($_GET['guests'] ?? 2);
$guests = max(1, min(20, $guests));

/* ------------------------------------------------------------- créneaux -- */
$step = max(10, (int) ($bk['step_minutes'] ?? 30));

$buildSlots = static function (array $group, string $prefix, int $step) use ($rawDate, $guests, $dow, $now, $today, $dt): array {
    [$fromH, $fromM] = array_map('intval', explode(':', $group['from']));
    [$toH, $toM]     = array_map('intval', explode(':', $group['to']));

    $start = $fromH * 60 + $fromM;
    $end   = $toH * 60 + $toM;
    if ($end <= $start) {
        $end += 1440;                     // le soir dépasse minuit
    }

    $isToday = $dt->format('Y-m-d') === $today->format('Y-m-d');
    $nowMin  = ((int) $now->format('G')) * 60 + (int) $now->format('i');

    $out = [];
    for ($m = $start; $m <= $end; $m += $step) {
        $clock = sprintf('%02d:%02d', intdiv($m % 1440, 60), $m % 60);

        // Un créneau déjà passé aujourd'hui n'est plus réservable.
        if ($isToday && ($m % 1440) <= $nowMin + 20) {
            $status = 'full';
        } else {
            $s   = api_seed($prefix . '|' . $rawDate . '|' . $clock);
            $mod = $s % 100;
            // Le week-end et les gros groupes remplissent davantage.
            $busy = ($dow >= 5 ? 12 : 0) + max(0, ($guests - 4) * 6);
            $status = $mod < (18 + $busy) ? 'full' : ($mod < (40 + $busy) ? 'last' : 'free');
        }

        $out[] = ['time' => $clock, 'status' => $status];
    }
    return $out;
};

api_out([
    'ok'      => true,
    'date'    => $rawDate,
    'guests'  => $guests,
    'day'     => $dt->format('D'),
    'lunch'   => ['label' => $bk['lunch']['label'],  'slots' => $buildSlots($bk['lunch'],  'lunch',  $step)],
    'dinner'  => ['label' => $bk['dinner']['label'], 'slots' => $buildSlots($bk['dinner'], 'dinner', $step)],
]);
