<?php
define('NWD_PANEL', true);
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/traffic.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); http_response_code(405); exit; }
if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') { http_response_code(415); exit; }

$site = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
// Fetch Metadata survives a local reverse proxy that rewrites Host. Older browsers send Origin.
if ($site !== '' ? $site !== 'same-origin' : !preg_match('~^https?://'.preg_quote($_SERVER['HTTP_HOST'] ?? '', '~').'$~iD', $origin)) {
    http_response_code(403); exit;
}
if (($_SERVER['HTTP_DNT'] ?? '') === '1' || ($_SERVER['HTTP_SEC_GPC'] ?? '') === '1'
    || preg_match('~bot\b|crawler|spider|headlesschrome|facebookexternalhit|lighthouse|pagespeed~i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    http_response_code(204); exit;
}
$raw = file_get_contents('php://input', false, null, 0, 1537);
if ($raw === false || strlen($raw) > 1536) { http_response_code(413); exit; }
try {
    $event = panelTrafficInput(json_decode($raw, true, 5));
    $now = time();
    panelTrafficRecord($event, $now);
    if (random_int(1,64) === 1) { panelTrafficCleanup($now); }
    http_response_code(204);
} catch (PanelError $e) {
    http_response_code($e->getCode());
} catch (Throwable $e) {
    http_response_code(503);
    error_log('NWD traffic: '.get_class($e).' ('.(int)$e->getCode().')');
}
