<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelLeads() {
    $lang=panelLanguage(); $table=panelTable('feedback');
    $total=(int)panelRow("SELECT COUNT(*) n FROM $table WHERE trash='0'")['n'];
    $pages=max(1,(int)ceil($total/25));$page=max(1,min((int)($_GET['page'] ?? 1),$pages));
    $site=panelTable('site');
    $rows=panelRows("SELECT f.id,f.created,f.url,f.text,s.name$lang form_name FROM $table f LEFT JOIN $site s ON s.id=f.site_id WHERE f.trash='0' ORDER BY f.id DESC LIMIT 25 OFFSET ".(($page-1)*25));
    echo '<p class="nwd-kicker">Формы сайта</p><h1>Заявки</h1>';
    if (!$rows) { echo '<p>В формах DIAFAN пока нет сохранённых заявок.</p>';return; }
    $fields=panelTable('feedback_param_element');$params=panelTable('feedback_param');$options=panelTable('feedback_param_select');
    foreach ($rows as $row) {
        $form=$row['form_name'] ?: 'Обратная связь';
        echo '<details class="report-row"><summary><span>'.panelHtml($form).'</span><time>'.date('d.m.Y · H:i',(int)$row['created']).'</time></summary><dl>';
        $values=panelRows("SELECT p.name$lang label,CASE WHEN p.type IN ('select','multiple','radio') THEN COALESCE(o.name$lang,v.value) WHEN p.type='checkbox' THEN IF(v.value='1','Да','Нет') ELSE v.value END value FROM $fields v LEFT JOIN $params p ON p.id=v.param_id LEFT JOIN $options o ON o.param_id=v.param_id AND o.id=v.value AND o.trash='0' WHERE v.element_id=? AND v.trash='0' ORDER BY p.sort,v.id",array((int)$row['id']));
        foreach ($values as $field) { echo '<dt>'.panelHtml($field['label'] ?: 'Поле формы').'</dt><dd>'.nl2br(panelHtml($field['value'])).'</dd>'; }
        if ($row['text']) { echo '<dt>Сообщение</dt><dd>'.nl2br(panelHtml(strip_tags($row['text']))).'</dd>'; }
        if ($row['url']) { echo '<dt>Страница формы</dt><dd>'.panelHtml($row['url']).'</dd>'; }
        echo '</dl></details>';
    }
    if ($pages>1) {
        echo '<nav class="report-pages">';
        if ($page>1) { echo '<a href="?tab=leads&amp;page='.($page-1).'">Назад</a>'; }
        echo '<span>'.$page.' / '.$pages.'</span>';
        if ($page<$pages) { echo '<a href="?tab=leads&amp;page='.($page+1).'">Далее</a>'; }
        echo '</nav>';
    }
}

function panelVisits() {
    $days=(int)($_GET['days'] ?? 30); if (!in_array($days,array(7,30,90),true)) { $days=30; }
    echo '<a class="stats-back" href="?tab=visits&amp;days='.$days.'">← К статистике</a><h1>История DIAFAN</h1><nav class="report-pages" aria-label="Период истории">';
    foreach (array(7,30,90) as $period) { echo '<a '.($days===$period?'aria-current="page"':'').' href="?tab=visits&amp;history=diafan&amp;days='.$period.'">'.$period.' дней</a>'; }
    echo '</nav>';
    $name=DB_PREFIX.'visitors_stat_traffic';
    if (!panelRow('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array($name))) {
        echo '<p>В этой копии сайта нет таблицы статистики DIAFAN.</p>';return;
    }
    $start=strtotime('-'.($days-1).' days',strtotime('today'));
    $rows=panelRows('SELECT `date`,visits_count,pageviews_count FROM '.panelTable('visitors_stat_traffic').' WHERE `date`>=? AND `date`<? ORDER BY `date` DESC',array($start,strtotime('tomorrow')));
    if (!$rows) { echo '<p>За выбранный период в счётчике DIAFAN нет записей.</p>';return; }
    echo '<table class="report-table"><thead><tr><th>Дата</th><th>Визиты</th><th>Просмотры</th></tr></thead><tbody>';
    foreach ($rows as $row) { echo '<tr><td>'.date('d.m.Y',(int)$row['date']).'</td><td>'.(int)$row['visits_count'].'</td><td>'.(int)$row['pageviews_count'].'</td></tr>'; }
    echo '</tbody></table>';
}
