<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelScheduleInstant($value) {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,3})?(?:Z|[+-]\d{2}:\d{2})$/D', $value)) { return null; }
    try {
        $date = new DateTimeImmutable($value);
        $errors = DateTimeImmutable::getLastErrors();
        if ($errors && ($errors['warning_count'] || $errors['error_count'])) { return null; }
        $stamp = $date->getTimestamp();
        return $stamp > 0 && $stamp <= 4294967295 ? $stamp : null;
    } catch (Exception $e) { return null; }
}

function panelScheduleRow($id) {
    return panelRow('SELECT * FROM '.panelTable('panel_schedule').' WHERE article_id=?', array($id));
}

function panelScheduleRecord($row) {
    if (!$row) { return null; }
    $result = array('id'=>$row['token'], 'state'=>$row['state'], 'error'=>$row['error'] ?: null);
    foreach (array('at'=>'scheduled_at', 'createdAt'=>'created_at', 'startedAt'=>'started_at', 'finishedAt'=>'finished_at') as $key=>$column) {
        $result[$key] = $row[$column] ? gmdate('c', (int)$row[$column]) : null;
    }
    return $result;
}

// Called only while the editor holds panel_state and the article row locks.
function panelScheduleNext(array $form, $old, $delete, array $input) {
    $previous = $old ? panelScheduleRow($old['id']) : null;
    $now = time();
    if (isset($input['scheduleAction'])) {
        $action = $input['scheduleAction'];
        $valid = $previous && hash_equals($previous['token'], (string)($input['scheduleId'] ?? ''))
            && ($action === 'retry' ? $previous['state'] === 'error' : ($old['status'] === 'scheduled' && $previous['state'] === 'scheduled'));
        if (!$valid) { panelFail('Расписание уже изменилось. Обновите план публикаций.',409); }
    }
    if (!$delete && $form['status'] === 'scheduled') {
        $stamp = panelScheduleInstant($form['scheduledAt'] ?? null);
        if (!$stamp) { panelFail('Выберите корректную дату и время публикации по Москве.'); }
        $unchanged = $previous && $old['status'] === 'scheduled' && $previous['state'] === 'scheduled' && (int)$previous['scheduled_at'] === $stamp;
        if ($stamp <= $now && !$unchanged) { panelFail('Выберите будущую дату и время публикации.'); }
        if ($unchanged) { return $previous; }
        return array('token'=>bin2hex(random_bytes(16)), 'scheduled_at'=>$stamp, 'state'=>'scheduled', 'created_at'=>$now, 'started_at'=>null, 'finished_at'=>null, 'error'=>'');
    }
    if (!$previous) { return null; }
    if ($delete || $form['status'] === 'draft') {
        if ($previous['state'] !== 'cancelled') {
            $previous['state'] = 'cancelled'; $previous['finished_at'] = $now; $previous['error'] = '';
        }
    } elseif ($old['status'] === 'scheduled' || $previous['state'] === 'error') {
        $previous['state'] = 'publishing'; $previous['started_at'] = $now; $previous['finished_at'] = null; $previous['error'] = '';
    }
    return $previous;
}

function panelScheduleStore($id, $schedule) {
    if (!$schedule) { return; }
    panelQuery('INSERT INTO '.panelTable('panel_schedule').' (article_id,token,scheduled_at,state,created_at,started_at,finished_at,error) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE token=VALUES(token),scheduled_at=VALUES(scheduled_at),state=VALUES(state),created_at=VALUES(created_at),started_at=VALUES(started_at),finished_at=VALUES(finished_at),error=VALUES(error)',
        array($id,$schedule['token'],$schedule['scheduled_at'],$schedule['state'],$schedule['created_at'],$schedule['started_at'],$schedule['finished_at'],$schedule['error']));
}

function panelScheduleCache() {
    require_once ABSOLUTE_PATH.'includes/custom.php';
    Custom::init();
    Custom::inc('includes/file.php');
    Custom::inc('includes/cache.php');
    $cache = new Cache();
    foreach (array('site','map','rewrite','custom') as $module) { $cache->delete('', $module); }
    $homepage = ABSOLUTE_PATH.'index.html';
    if (is_file($homepage) && !is_link($homepage) && !unlink($homepage)) { throw new RuntimeException('Cannot refresh homepage cache'); }
}

function panelScheduleSettle($id, $token, $error = '') {
    panelTransaction(function() use ($id,$token,$error) {
        panelRow('SELECT id FROM '.panelTable('panel_state').' WHERE id=1 FOR UPDATE');
        $row = panelRow('SELECT status,deleted FROM '.panelTable('panel_article').' WHERE id=? FOR UPDATE',array($id));
        $schedule = panelScheduleRow($id);
        if (!$row || (int)$row['deleted'] || !$schedule || !hash_equals($schedule['token'],$token) || !in_array($schedule['state'],array('publishing','error'),true) || ($error === '' && $row['status'] !== 'published')) { return; }
        panelQuery('UPDATE '.panelTable('panel_schedule').' SET state=?,finished_at=?,error=? WHERE article_id=? AND token=?',array($error === '' ? 'success' : 'error',time(),$error,$id,$token));
    });
}

// A committed publication may still need cache invalidation after a crash.
// Finishing an old job cannot finish a newer schedule for the same article.
function panelScheduleFinish($id = null) {
    $where = $id ? ' AND s.article_id=?' : '';
    $rows = panelRows('SELECT s.article_id,s.token FROM '.panelTable('panel_schedule').' s JOIN '.panelTable('panel_article')." a ON a.id=s.article_id WHERE s.state='publishing' AND a.status='published' AND a.deleted=0".$where,$id ? array($id) : array());
    if (!$rows) { return; }
    try {
        panelScheduleCache();
        foreach ($rows as $row) { panelScheduleSettle($row['article_id'],$row['token']); }
    } catch (Throwable $e) {
        foreach ($rows as $row) { panelScheduleSettle($row['article_id'],$row['token'],'Не удалось обновить публичную версию. Повторите публикацию.'); }
        throw $e;
    }
}

function panelScheduleList() {
    $rows = panelRows('SELECT a.id,a.title,a.slug,a.category,a.status,a.revision,a.updated_at,s.token,s.state,s.scheduled_at,s.created_at,s.started_at,s.finished_at,s.error FROM '.panelTable('panel_schedule').' s JOIN '.panelTable('panel_article').' a ON a.id=s.article_id WHERE a.deleted=0 ORDER BY s.scheduled_at,a.id');
    $items = array();
    foreach ($rows as $row) {
        $items[] = array('id'=>$row['id'],'title'=>$row['title'],'slug'=>$row['slug'],'category'=>$row['category'],'status'=>$row['status'],'revision'=>(int)$row['revision'],'updatedAt'=>gmdate('c',(int)$row['updated_at']),'schedule'=>panelScheduleRecord($row));
    }
    $worker = panelRow('SELECT checked_at,error FROM '.panelTable('panel_schedule_worker').' WHERE id=1');
    $checked = $worker ? (int)$worker['checked_at'] : 0;
    return array('schedules'=>$items,'serverNow'=>gmdate('c'),'scheduler'=>array('enabled'=>$checked >= time()-90,'lastCheckedAt'=>$checked ? gmdate('c',$checked) : null,'lastError'=>$worker && $worker['error'] ? $worker['error'] : null,'intervalMs'=>15000,'timeZone'=>'Europe/Moscow'));
}

function panelScheduleAction($id, array $input) {
    $action = $input['action'] ?? '';
    if (!in_array($action,array('schedule','cancel','publish','retry'),true)) { panelFail('Неизвестное действие.'); }
    $current = panelLoad($id);
    if ((int)($input['revision'] ?? 0) !== (int)$current['revision']) { panelFail('Статья уже изменена. Обновите план публикаций.',409); }
    $form = json_decode($current['payload'],true,512,JSON_THROW_ON_ERROR);
    $form['status'] = $action === 'schedule' ? 'scheduled' : ($action === 'cancel' ? 'draft' : 'published');
    if ($action === 'schedule') { $form['scheduledAt'] = $input['scheduledAt'] ?? null; }
    $result = panelSave(array('form'=>$form,'revision'=>$input['revision'],'scheduleAction'=>$action,'scheduleId'=>$input['scheduleId'] ?? ''),$id);
    panelScheduleFinish($id);
    return array('ok'=>true,'article'=>panelRecord(panelLoad($id)));
}

function panelScheduleTick() {
    panelScheduleFinish();
    $due = panelRows('SELECT s.article_id,s.token FROM '.panelTable('panel_schedule').' s JOIN '.panelTable('panel_article')." a ON a.id=s.article_id WHERE s.state='scheduled' AND a.status='scheduled' AND a.deleted=0 AND s.scheduled_at<=? ORDER BY s.scheduled_at,s.article_id LIMIT 100",array(time()));
    foreach ($due as $job) {
        try {
            $published = panelTransaction(function() use ($job) {
                $state = panelRow('SELECT journal_id FROM '.panelTable('panel_state').' WHERE id=1 FOR UPDATE');
                $row = panelRow('SELECT * FROM '.panelTable('panel_article').' WHERE id=? FOR UPDATE',array($job['article_id']));
                $schedule = panelScheduleRow($job['article_id']);
                if (!$state || !$row || (int)$row['deleted'] || $row['status'] !== 'scheduled' || !$schedule || $schedule['state'] !== 'scheduled' || !hash_equals($schedule['token'],$job['token']) || (int)$schedule['scheduled_at'] > time()) { return false; }
                $input = json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR); $input['status'] = 'published';
                $images = array(); $form = panelForm($input,$images);
                foreach (array_keys($images) as $image) {
                    if (!panelRow('SELECT id FROM '.panelTable('panel_image').' WHERE id=?',array($image))) { panelFail('Загруженное изображение не найдено.'); }
                }
                $date = $row['published_at'] ?: (int)$schedule['scheduled_at'];
                $siteId = panelPublish($form,$row['site_id'],$date,$state);
                panelQuery('UPDATE '.panelTable('panel_article')." SET payload=?,status='published',site_id=?,published_at=?,updated_at=?,revision=revision+1 WHERE id=?",array(panelJson($form),$siteId,$date,time(),$row['id']));
                panelQuery('UPDATE '.panelTable('panel_schedule')." SET state='publishing',started_at=?,finished_at=NULL,error='' WHERE article_id=? AND token=?",array(time(),$row['id'],$job['token']));
                panelRefreshJournal(panelRow('SELECT journal_id FROM '.panelTable('panel_state').' WHERE id=1')['journal_id']);
                return true;
            });
            if ($published) { panelScheduleFinish($job['article_id']); }
        } catch (Throwable $e) {
            // Store a safe, actionable error; never expose connection details.
            $message = $e instanceof PanelError ? $e->getMessage() : 'Не удалось опубликовать статью. Проверьте её и повторите публикацию.';
            panelTransaction(function() use ($job,$message) {
                panelRow('SELECT id FROM '.panelTable('panel_state').' WHERE id=1 FOR UPDATE');
                panelRow('SELECT id FROM '.panelTable('panel_article').' WHERE id=? FOR UPDATE',array($job['article_id']));
                panelQuery('UPDATE '.panelTable('panel_schedule')." SET state='error',finished_at=?,error=? WHERE article_id=? AND token=? AND state IN ('scheduled','publishing')",array(time(),mb_substr($message,0,500),$job['article_id'],$job['token']));
            });
        }
    }
}
