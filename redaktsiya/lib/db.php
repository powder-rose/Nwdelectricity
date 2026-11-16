<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

class PanelError extends RuntimeException {}

function panelFail($message, $status = 400) {
    throw new PanelError($message, $status);
}

function panelDb() {
    static $db;
    if ($db) { return $db; }
    if (!defined('DIAFAN')) { define('DIAFAN', 1); }
    if (!defined('ABSOLUTE_PATH')) { define('ABSOLUTE_PATH', dirname(__DIR__, 2).'/'); }
    require_once ABSOLUTE_PATH.'config.php';
    if (!preg_match('/^\w*$/D', DB_PREFIX)) { throw new RuntimeException('Invalid database prefix'); }
    $parts = parse_url(DB_URL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli($parts['host'], rawurldecode($parts['user']), rawurldecode($parts['pass'] ?? ''),
        ltrim($parts['path'], '/'), isset($parts['port']) ? $parts['port'] : 3306);
    $db->set_charset('utf8mb4');
    return $db;
}

function panelTable($name) {
    return '`'.DB_PREFIX.$name.'`';
}

function panelQuery($sql, array $values = array()) {
    $stmt = panelDb()->prepare($sql);
    if ($values) {
        $types = '';
        foreach ($values as $value) { $types .= is_int($value) ? 'i' : 's'; }
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    return $stmt;
}

function panelRows($sql, array $values = array()) {
    return panelQuery($sql, $values)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function panelRow($sql, array $values = array()) {
    return panelQuery($sql, $values)->get_result()->fetch_assoc();
}

function panelTransaction(callable $work) {
    $db = panelDb();
    $db->begin_transaction();
    try {
        $result = $work();
        $db->commit();
        return $result;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function panelLanguage() {
    static $language;
    if (!$language) {
        $row = panelRow("SELECT id FROM ".panelTable('languages')." WHERE base_site='1' LIMIT 1");
        if (!$row || !(int)$row['id']) { panelFail('Основной язык DIAFAN не найден.', 503); }
        $language = (int)$row['id'];
    }
    return $language;
}

function panelHtml($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function panelJson($value) { return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); }

function panelHeaders() {
    header('Cache-Control: private, no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: SAMEORIGIN');
}

function panelErrorResponse($error) {
    if ($error instanceof PanelError) {
        http_response_code($error->getCode());
        return $error->getMessage();
    }
    http_response_code(503);
    error_log('NWD panel: '.get_class($error).' ('.(int)$error->getCode().')');
    return 'Панель временно недоступна. Проверьте подключение к базе и установку панели.';
}
