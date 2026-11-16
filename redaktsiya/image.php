<?php
define('NWD_PANEL',true);
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
try {
    if (!in_array($_SERVER['REQUEST_METHOD'],array('GET','HEAD'),true)) { panelFail('Метод недоступен.',405); }
    $id=$_GET['id'] ?? '';
    if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/D',$id)) { panelFail('Изображение не найдено.',404); }
    panelDb();
    $imgs=panelTable('panel_image');$refs=panelTable('panel_image_ref');$articles=panelTable('panel_article');
    $row=panelRow("SELECT mime,OCTET_LENGTH(bytes) size,EXISTS(SELECT 1 FROM $refs r JOIN $articles a ON r.article_id=a.id WHERE r.image_id=i.id AND a.deleted=0 AND a.status='published') visible FROM $imgs i WHERE i.id=?",array($id));
    if (!$row || (!(int)$row['visible'] && !panelAuthorized())) { panelFail('Изображение не найдено.',404); }
    header('Content-Type: '.$row['mime']);
    header('Cache-Control: '.((int)$row['visible']?'public, no-cache':'private, no-store'));
    header('X-Content-Type-Options: nosniff');
    $tag='"'.$id.'"'; header('ETag: '.$tag);
    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')===$tag) { http_response_code(304);exit; }
    header('Content-Length: '.$row['size']);
    if ($_SERVER['REQUEST_METHOD']!=='HEAD') { echo panelRow("SELECT bytes FROM $imgs WHERE id=?",array($id))['bytes']; }
} catch (Throwable $e) { panelHeaders(); echo panelErrorResponse($e); }
