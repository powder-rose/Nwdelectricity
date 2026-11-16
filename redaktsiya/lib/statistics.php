<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelStatistics($days, DateTimeImmutable $today) {
    panelDb();
    $start = $today->modify('-'.($days - 1).' days');
    $from = $start->getTimestamp();
    $until = $today->modify('+1 day')->getTimestamp();
    $views = panelTable('panel_traffic_view');
    $period = array($from, $until);
    $daily = array();
    for ($i = 0; $i < $days; $i++) {
        $daily[] = array('date'=>$start->modify('+'.$i.' days'), 'visits'=>0, 'views'=>0, 'articles'=>0, 'leads'=>0);
    }
    // Calculate day boundaries from Moscow midnight independently of the database time zone.
    $rows = panelRows("SELECT (viewed_at - ?) DIV 86400 AS slot, COUNT(*) AS views, SUM(entry) AS visits, SUM(article_site_id>0) AS articles
        FROM $views WHERE viewed_at>=? AND viewed_at<? GROUP BY slot", array($from,$from,$until));
    foreach ($rows as $row) {
        foreach (array('views','visits','articles') as $key) { $daily[(int)$row['slot']][$key] = (int)$row[$key]; }
    }
    $leads = panelRows('SELECT (created - ?) DIV 86400 AS slot, COUNT(*) AS total FROM '.panelTable('feedback')
        ." WHERE trash='0' AND created>=? AND created<? GROUP BY slot", array($from,$from,$until));
    foreach ($leads as $row) { $daily[(int)$row['slot']]['leads'] = (int)$row['total']; }

    $totals = array();
    foreach (array('views','visits','articles','leads') as $key) { $totals[$key] = array_sum(array_column($daily, $key)); }
    $sources = panelRows("SELECT source, COUNT(*) AS total FROM $views WHERE viewed_at>=? AND viewed_at<? AND entry=1
        GROUP BY source ORDER BY total DESC,source LIMIT 6", $period);
    $pages = panelRows("SELECT path, COUNT(*) AS total FROM $views WHERE viewed_at>=? AND viewed_at<?
        GROUP BY path ORDER BY total DESC,path LIMIT 8", $period);
    $articles = panelRows("SELECT ranked.path,ranked.total,a.title,a.slug,a.status,a.deleted FROM
        (SELECT article_site_id, MIN(path) AS path, COUNT(*) AS total FROM $views WHERE viewed_at>=? AND viewed_at<? AND article_site_id>0
        GROUP BY article_site_id ORDER BY total DESC,article_site_id LIMIT 8) ranked
        LEFT JOIN ".panelTable('panel_article').' a ON a.site_id=ranked.article_site_id ORDER BY ranked.total DESC,ranked.article_site_id', $period);
    return compact('start','today','daily','totals','sources','pages','articles');
}

function panelStatsNumber($n) { return number_format((int)$n, 0, ',', ' '); }

function panelStatsSource($key) {
    if (strpos($key,'utm:') === 0) { return 'UTM · '.substr($key,4); }
    if (strpos($key,'ref:') === 0) { return substr($key,4); }
    $labels = array('direct'=>'Прямой заход', 'internal'=>'Со страниц сайта', 'unknown'=>'Источник не определён');
    return $labels[$key] ?? $labels['unknown'];
}

function panelStatsChart(array $daily) {
    $highest = max(1, max(array_column($daily,'views')));
    $step = pow(10, floor(log10($highest)));
    $ceiling = ceil($highest / $step) * $step;
    $coordinates = array('visits'=>array(),'views'=>array());
    $last = count($daily) - 1;
    foreach ($daily as $i=>$day) {
        foreach ($coordinates as $key=>$_) {
            $coordinates[$key][] = sprintf('%.2F,%.2F', 4 + 992*$i/$last, 4 + 148*(1 - $day[$key]/$ceiling));
        }
    }
    ?>
    <div class="stats-plot" aria-hidden="true">
      <div class="stats-axis"><span><?=panelStatsNumber($ceiling)?></span><span>0</span></div>
      <svg viewBox="0 0 1000 156" preserveAspectRatio="none" focusable="false">
        <path class="stats-gridline" d="M0 4H1000 M0 78H1000 M0 152H1000"/>
        <polygon class="stats-area" points="4,152 <?=implode(' ', $coordinates['views'])?> 996,152"/>
        <polyline class="stats-line-views" points="<?=implode(' ', $coordinates['views'])?>"/>
        <polyline class="stats-line-visits" points="<?=implode(' ', $coordinates['visits'])?>"/>
      </svg>
    </div>
    <div class="stats-dates" aria-hidden="true">
      <?php foreach (array(0,(int)floor($last/2),$last) as $index): ?><span><?=$daily[$index]['date']->format('d.m')?></span><?php endforeach ?>
    </div>
    <?php
}

function panelShowStatistics($period) {
    $days = in_array((string)(is_scalar($period) ? $period : ''), array('7','30','90'), true) ? (int)$period : 30;
    $report = panelStatistics($days, new DateTimeImmutable('today', new DateTimeZone('Europe/Moscow')));
    $totals = $report['totals'];
    $metrics = array('visits'=>'Визиты', 'views'=>'Просмотры страниц', 'articles'=>'Просмотры статей', 'leads'=>'Заявки с сайта');
    ?>
    <div class="stats">
      <header class="stats-heading">
        <div><h1>Статистика</h1><p><?=$report['start']->format('d.m.Y')?> — <?=$report['today']->format('d.m.Y')?> · Москва</p></div>
        <nav class="stats-period" aria-label="Период отчёта"><?php foreach (array(7,30,90) as $option): ?>
          <a href="?tab=visits&amp;days=<?=$option?>" <?=$option === $days ? 'aria-current="page"' : ''?>><?=$option?> дней</a>
        <?php endforeach ?></nav>
      </header>
      <dl class="stats-metrics">
        <?php foreach ($metrics as $key=>$label): ?><div><dt><?=$label?></dt><dd><?=panelStatsNumber($totals[$key])?></dd></div><?php endforeach ?>
      </dl>
      <div class="stats-pair stats-traffic">
        <section class="stats-card">
          <h2>Динамика трафика</h2>
          <?php if ($totals['views']): ?>
          <div class="stats-legend"><span>Просмотры</span><span>Визиты</span></div>
          <?php panelStatsChart($report['daily']); else: ?>
          <p class="stats-empty">За этот период просмотров пока нет. После установки откройте публичную страницу сайта и обновите отчёт.</p>
          <?php endif ?>
          <details class="stats-details"><summary>Данные по дням</summary>
            <div class="stats-scroll" role="region" aria-label="Статистика по дням" tabindex="0"><table>
              <thead><tr><th scope="col">Дата</th><th scope="col">Визиты</th><th scope="col">Просмотры</th><th scope="col">Просмотры статей</th><th scope="col">Заявки</th></tr></thead>
              <tbody><?php foreach (array_reverse($report['daily']) as $day): ?><tr><th scope="row"><?=$day['date']->format('d.m.Y')?></th><?php foreach (array_keys($metrics) as $key): ?><td><?=panelStatsNumber($day[$key])?></td><?php endforeach ?></tr><?php endforeach ?></tbody>
            </table></div>
          </details>
        </section>
        <section class="stats-card">
          <h2>Источники визитов</h2>
          <?php if (!$report['sources']): ?><p class="stats-empty">Здесь появятся источники первых переходов.</p><?php else: ?>
          <ul class="stats-sources"><?php foreach ($report['sources'] as $row):
            $width = (int)round(100 * (int)$row['total'] / max(1,$totals['visits'])); ?>
            <li><div><span><?=panelHtml(panelStatsSource($row['source']))?></span><strong><?=panelStatsNumber($row['total'])?></strong></div>
              <div class="stats-meter" aria-hidden="true"><i style="width:<?=$width?>%"></i></div></li>
          <?php endforeach ?>
          <?php $other = $totals['visits'] - array_sum(array_column($report['sources'],'total')); if ($other > 0): ?>
            <li><div><span>Другие источники</span><strong><?=panelStatsNumber($other)?></strong></div></li>
          <?php endif ?></ul><?php endif ?>
        </section>
      </div>
      <div class="stats-pair stats-tops">
        <section class="stats-card"><h2>Популярные страницы</h2>
          <?php if (!$report['pages']): ?><p class="stats-empty">Пока нет просмотров страниц.</p><?php else: ?>
          <table><thead><tr><th scope="col">Адрес страницы</th><th scope="col">Просмотры</th></tr></thead><tbody>
          <?php foreach ($report['pages'] as $row): ?><tr><th scope="row"><?=panelHtml($row['path'])?></th><td><?=panelStatsNumber($row['total'])?></td></tr><?php endforeach ?>
          </tbody></table><?php endif ?>
        </section>
        <section class="stats-card"><h2>Статьи журнала</h2>
          <?php if (!$report['articles']): ?><p class="stats-empty">Здесь появятся статьи, которые открывали читатели.</p><?php else: ?>
          <table><thead><tr><th scope="col">Статья</th><th scope="col">Просмотры</th></tr></thead><tbody>
          <?php foreach ($report['articles'] as $row): ?><tr><th scope="row">
            <?php if ($row['status']==='published' && !$row['deleted']): ?><a href="/zhurnal/<?=rawurlencode($row['slug'])?>/"><?=panelHtml($row['title'])?></a><?php else: ?><?=panelHtml($row['title'] ?: $row['path'])?><?php endif ?>
          </th><td><?=panelStatsNumber($row['total'])?></td></tr><?php endforeach ?>
          </tbody></table><?php endif ?>
        </section>
      </div>
      <footer class="stats-foot"><p>Новый визит начинается после 30 минут без просмотров. Источник определяется по первому переходу. Заявки берутся из форм сайта.</p>
        <a href="?tab=visits&amp;history=diafan&amp;days=<?=$days?>">История DIAFAN <span aria-hidden="true">↗</span></a></footer>
    </div>
    <?php
}
