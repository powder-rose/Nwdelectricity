<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelTrafficInput($data) {
    if (!is_array($data)) { panelFail('Некорректное событие.'); }
    foreach (array('event','session') as $key) {
        if (!isset($data[$key]) || !is_string($data[$key]) || !preg_match('/^[a-f0-9]{32}$/D', $data[$key])) {
            panelFail('Некорректный идентификатор.');
        }
    }
    $path = $data['page'] ?? '';
    if (!is_string($path) || strlen($path) > 500 || !preg_match('~^/[\pL\pN_./\~-]*$~uD', $path)
        || strpos($path, '//') !== false || preg_match('~(?:^|/)\.{1,2}(?:/|$)|\.(?:php|js|css|xml|json|jpe?g|png|svg|webp|pdf|txt)$~i', $path)
        || preg_match('~^/(?:adm|admin|redaktsiya|cache|tmp|includes|userfls|users|registration|reminding)(?:/|$)~i', $path)) {
        panelFail('Некорректная страница.');
    }
    $source = $data['source'] ?? 'direct';
    if (!is_string($source) || !preg_match('~^(?:direct|internal|utm:[a-z0-9а-яё_-]{1,64}|ref:(?=.{1,120}$)[a-z0-9-]+(?:\.[a-z0-9-]+)*)$~uD', $source)) {
        $source = 'unknown';
    }
    $slug = trim($path, '/');
    return array('event'=>$data['event'], 'session'=>$data['session'], 'path'=>$slug === '' ? '/' : '/'.$slug.'/', 'source'=>$source);
}

function panelTrafficRecord(array $event, $now) {
    panelDb();
    $path = $event['path'];
    $articleId = 0;
    if ($path !== '/') {
        // Accept only routes published in the site's own routing table.
        $route = panelRow('SELECT module_name,element_type,element_id FROM '.panelTable('rewrite').' WHERE rewrite=? LIMIT 1', array(trim($path,'/')));
        if (!$route) { return; }
        if (strpos($path, '/zhurnal/') === 0 && $route['module_name'] === 'site' && $route['element_type'] === 'element') {
            $article = panelRow('SELECT site_id FROM '.panelTable('panel_article')." WHERE site_id=? AND deleted=0 AND status='published'", array((int)$route['element_id']));
            if ($article) { $articleId = (int)$article['site_id']; }
        }
    }
    $views = panelTable('panel_traffic_view');
    $sessions = panelTable('panel_traffic_session');
    panelTransaction(function () use ($event, $path, $articleId, $now, $views, $sessions) {
        $insert = panelQuery("INSERT IGNORE INTO $views (event_id,viewed_at,path,article_site_id) VALUES (UNHEX(?),?,?,?)",
            array($event['event'],$now,$path,$articleId));
        if ($insert->affected_rows !== 1) { return; }
        // Lock this browser session, including its very first two simultaneous requests.
        panelQuery("INSERT INTO $sessions (token,seen_at) VALUES (UNHEX(?),0) ON DUPLICATE KEY UPDATE seen_at=seen_at", array($event['session']));
        $state = panelRow("SELECT seen_at FROM $sessions WHERE token=UNHEX(?) FOR UPDATE", array($event['session']));
        $previous = (int)$state['seen_at'];
        if ($previous === 0 || $now - $previous >= 1800) {
            panelQuery("UPDATE $views SET entry=1,source=? WHERE event_id=UNHEX(?)",array($event['source'],$event['event']));
        }
        if ($now > $previous) {
            panelQuery("UPDATE $sessions SET seen_at=? WHERE token=UNHEX(?)",array($now,$event['session']));
        }
    });
}

function panelTrafficCleanup($now) {
    // Bounded batches keep cleanup outside the recording transaction.
    panelQuery('DELETE FROM '.panelTable('panel_traffic_session').' WHERE seen_at < ? ORDER BY seen_at LIMIT 500', array($now - 86400));
    panelQuery('DELETE FROM '.panelTable('panel_traffic_view').' WHERE viewed_at < ? ORDER BY viewed_at LIMIT 500', array($now - 92 * 86400));
}
