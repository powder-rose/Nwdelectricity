<?php
// This worker is deliberately unavailable over HTTP.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('NWD_PANEL',true);
foreach (array('db','content','publication','scheduling','articles') as $file) { require __DIR__.'/lib/'.$file.'.php'; }
ini_set('display_errors','0');
try {
    panelDb();
    if (in_array('--self-test',$argv,true)) {
        foreach (array('panel_article','panel_schedule','panel_schedule_worker','panel_state') as $table) {
            panelQuery('SELECT 1 FROM '.panelTable($table).' LIMIT 0');
        }
        echo "SCHEDULE_WORKER_SELFTEST_OK\n";
        exit;
    }
    $database = panelRow('SELECT DATABASE() AS name')['name'];
    $lock = 'nwd-article-schedule-'.sha1($database.DB_PREFIX);
    if (!(int)panelRow('SELECT GET_LOCK(?,0) AS acquired',array($lock))['acquired']) { echo "SCHEDULE_WORKER_BUSY\n"; exit; }
    try {
        $worker = in_array('--worker',$argv,true);
        $start = microtime(true);
        do {
            $error = '';
            try { panelScheduleTick(); }
            catch (Throwable $e) { $error = 'Не удалось проверить расписание. Следующая проверка выполнится автоматически.'; }
            panelQuery('INSERT INTO '.panelTable('panel_schedule_worker').' (id,checked_at,error) VALUES (1,?,?) ON DUPLICATE KEY UPDATE checked_at=VALUES(checked_at),error=VALUES(error)',array(time(),$error));
            if ($error) { fwrite(STDERR,"SCHEDULE_CHECK_FAILED\n"); }
            if (!$worker || microtime(true)-$start >= 44) { break; }
            usleep((int)(max(0,15-fmod(microtime(true)-$start,15))*1000000));
        } while (microtime(true)-$start < 55);
        echo $error ? "SCHEDULE_CHECK_ERROR\n" : "SCHEDULE_CHECK_OK\n";
    } finally { panelQuery('SELECT RELEASE_LOCK(?)',array($lock)); }
} catch (Throwable $e) {
    fwrite(STDERR,"SCHEDULE_WORKER_FAILED=".get_class($e).' '.(int)$e->getCode()."\n"); exit(1);
}
