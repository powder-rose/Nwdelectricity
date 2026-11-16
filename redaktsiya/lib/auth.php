<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelSession() {
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('NWD_EDITOR');
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    // A production TLS-terminating proxy can set this explicitly.
    $secure = $https || getenv('NWD_PANEL_HTTPS') === '1' || getenv('NWD_LOCAL') !== '1';
    session_set_cookie_params(array('path'=>'/redaktsiya/', 'secure'=>$secure, 'httponly'=>true, 'samesite'=>'Strict'));
    session_start();
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
}

function panelAuthorized() {
    panelSession();
    if (empty($_SESSION['generation']) || (int)$_SESSION['expires'] <= time()) { return false; }
    $state = panelRow('SELECT generation FROM '.panelTable('panel_state').' WHERE id=1');
    return $state && hash_equals($state['generation'], $_SESSION['generation']);
}

function panelCsrf($token) {
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) { panelFail('Обновите вкладку и повторите действие.', 403); }
}

function panelLogin($password) {
    $key = hash('sha256', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    $attempts = panelTable('panel_attempt');
    $now = time();
    // Reserve an attempt before verifying so concurrent requests share the same budget.
    panelTransaction(function () use ($key,$now,$attempts) {
        panelQuery("INSERT INTO $attempts (client, started, attempts) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE client=VALUES(client)", array($key,$now));
        $row = panelRow("SELECT started, attempts FROM $attempts WHERE client=? FOR UPDATE", array($key));
        $expired = (int)$row['started'] + 600 <= $now;
        if (!$expired && (int)$row['attempts'] >= 10) { panelFail('Слишком много попыток. Попробуйте через 10 минут.',429); }
        panelQuery("UPDATE $attempts SET started=?,attempts=? WHERE client=?", array($expired?$now:(int)$row['started'], $expired?1:(int)$row['attempts']+1, $key));
    });
    $state = panelRow('SELECT password_hash, generation FROM '.panelTable('panel_state').' WHERE id=1');
    if (!is_string($password) || strlen($password)>512 || !$state || !password_verify($password,$state['password_hash'])) {
        panelFail('Неверный пароль.',401);
    }
    panelQuery("DELETE FROM $attempts WHERE client=? OR started<?",array($key,$now-600));
    session_regenerate_id(true);
    $_SESSION = array('generation'=>$state['generation'],'expires'=>$now+28800,'csrf'=>bin2hex(random_bytes(32)));
}

function panelLogout() {
    $_SESSION = array();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-3600, $params['path'], '', $params['secure'], true);
    session_destroy();
}
