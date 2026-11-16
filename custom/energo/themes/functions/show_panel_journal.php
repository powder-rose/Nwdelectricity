<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
echo '<link rel="stylesheet" href="/redaktsiya/assets/reading.css">';
$rows = DB::query_fetch_all("SELECT title,slug,category,published_at FROM {panel_article} WHERE status='published' AND deleted=0 ORDER BY published_at DESC,id");
echo '<section class="nwd-reading"><header class="nwd-reading-head"><h1>Журнал</h1></header><ul class="nwd-journal-list">';
foreach ($rows as $row) {
    echo '<li><div class="nwd-reading-meta">'.htmlspecialchars($row['category'], ENT_QUOTES,'UTF-8').' · '. date('d.m.Y',(int)$row['published_at']).'</div><h2><a href="/zhurnal/'.rawurlencode($row['slug']).'/">'.htmlspecialchars($row['title'],ENT_QUOTES,'UTF-8').'</a></h2></li>';
}
echo '</ul></section>';
