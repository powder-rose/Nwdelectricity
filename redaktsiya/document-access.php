<?php
// Kept under the editor cookie's /redaktsiya/ path; never vary cached article HTML by login.
define('NWD_PANEL',true);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, no-store, max-age=0');
header('Vary: Cookie');
header('X-Content-Type-Options: nosniff');
$allowed=false;
if (($_SERVER['REQUEST_METHOD'] ?? '')!=='GET') {
    http_response_code(405);header('Allow: GET');
} elseif (!empty($_COOKIE['NWD_EDITOR']) && is_string($_COOKIE['NWD_EDITOR'])) {
    try {
        require_once __DIR__.'/lib/db.php';
        require_once __DIR__.'/lib/auth.php';
        // Load database constants before panelTable() is evaluated inside panelAuthorized().
        panelDb();
        $allowed=panelAuthorized();
    } catch (Throwable $error) {
        $allowed=false;
    } finally {
        if (session_status()===PHP_SESSION_ACTIVE) { session_write_close(); }
    }
}
echo json_encode(array('copyAllowed'=>$allowed));
