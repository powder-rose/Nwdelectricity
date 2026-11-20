<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

function panelUuid() {
    $hex=bin2hex(random_bytes(16));
    return substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20);
}

function panelRecord($row) {
    $item=json_decode($row['payload'],true,512,JSON_THROW_ON_ERROR);
    foreach (array('created','updated','published') as $key) {
        $stamp=$row[$key.'_at']; $item[$key.'At']=$stamp ? gmdate('c',(int)$stamp) : null;
    }
    $item['id']=$row['id']; $item['revision']=(int)$row['revision'];
    $item['publicationSchedule']=panelScheduleRecord(panelScheduleRow($row['id']));
    $item['scheduledAt']=$item['publicationSchedule']['at'] ?? null;
    return $item;
}

function panelLoad($id,$lock=false) {
    $row=panelRow('SELECT * FROM '.panelTable('panel_article').' WHERE id=? AND deleted=0'.($lock?' FOR UPDATE':''),array($id));
    if (!$row) { panelFail('Статья не найдена.',404); }
    return $row;
}

function panelCategories() {
    return array_column(panelRows('SELECT DISTINCT category FROM '.panelTable('panel_article')." WHERE deleted=0 AND category<>'' ORDER BY category"),'category');
}

function panelArticleList(array $query) {
    $table=panelTable('panel_article');
    $where='deleted=0'; $params=array();
    $status=isset($query['status']) ? $query['status'] : 'all';
    if (in_array($status,array('draft','published','scheduled'),true)) { $where.=' AND status=?';$params[]=$status; }
    $search=mb_substr((string)($query['q'] ?? ''),0,100);
    if ($search!=='') { $where.=" AND (LOCATE(?,title)>0 OR LOCATE(?,category)>0)";$params[]=$search;$params[]=$search; }
    if (!empty($query['category'])) { $where.=' AND category=?';$params[]=$query['category']; }
    $total=(int)panelRow("SELECT COUNT(*) AS n FROM $table WHERE $where",$params)['n'];
    $pages=max(1,(int)ceil($total/25));
    $page=max(1,min($pages,(int)($query['page'] ?? 1)));
    $rows=panelRows("SELECT id,title,slug,category,status,revision,created_at,updated_at,published_at, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.image')) AS image, JSON_UNQUOTE(JSON_EXTRACT(payload,'$.imageAlt')) AS imageAlt FROM $table WHERE $where ORDER BY updated_at DESC,id LIMIT 25 OFFSET ".(($page-1)*25),$params);
    foreach ($rows as &$row) {
        $row['revision']=(int)$row['revision'];
        $row['publicationSchedule']=panelScheduleRecord(panelScheduleRow($row['id']));
        $row['scheduledAt']=$row['publicationSchedule']['at'] ?? null;
        foreach (array('created','updated','published') as $key) { $row[$key.'At']=$row[$key.'_at']?gmdate('c',(int)$row[$key.'_at']):null; unset($row[$key.'_at']); }
    }
    return array('articles'=>$rows,'categories'=>panelCategories(),'total'=>$total,'pages'=>$pages,'page'=>$page,'pageSize'=>25,'status'=>$status,'search'=>$search);
}

function panelSave(array $input, $id = null, $delete = false) {
    $images=array();
    $form=null;
    if (!$delete) {
        if (!isset($input['form']) || !is_array($input['form'])) { panelFail('Не переданы поля статьи.'); }
        $form=panelForm($input['form'],$images);
    }
    $key=$input['requestId'] ?? '';
    if (!$id && (!is_string($key) || !preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/D',$key))) { panelFail('Не указан идентификатор сохранения.'); }
    $result=panelTransaction(function() use ($input,$id,$delete,$form,$images,$key) {
        $state=panelRow('SELECT journal_id FROM '.panelTable('panel_state').' WHERE id=1 FOR UPDATE');
        if (!$state) { panelFail('Панель ещё не установлена.',503); }
        $table=panelTable('panel_article'); $aliases=panelTable('panel_slug');
        $digest=hash('sha256',panelJson($form)); $old=null;
        if ($id) {
            $old=panelLoad($id,true);
            if ((int)($input['revision'] ?? 0)!==(int)$old['revision']) { panelFail('Статья уже изменена. Откройте актуальную версию в другой вкладке.',409); }
        } else {
            $retry=panelRow("SELECT * FROM $table WHERE request_id=?",array($key));
            if ($retry) {
                if ((int)$retry['deleted'] || !hash_equals($retry['request_hash'],$digest)) { panelFail('Данные повторного сохранения изменились.',409); }
                return panelRecord($retry);
            }
            $id=panelUuid();
        }
        if ($delete) { $form=json_decode($old['payload'],true);$form['status']='draft'; }
        $schedule=panelScheduleNext($form,$old,$delete,$input);
        $base=$form['slug'] ?: ($old['slug'] ?? panelSlug($form['title']));
        $slug=$base; $suffix=1;
        while (panelRow("SELECT 1 FROM $aliases WHERE slug=? AND article_id<>?",array($slug,$id)) || panelRouteTaken('articles/'.$slug,(int)($old['site_id'] ?? 0))) {
            if ($old || $form['slug']) { panelFail('Такой адрес уже занят. Выберите другой.',409); }
            $tail='-'.(++$suffix); $slug=rtrim(substr($base,0,72-strlen($tail)),'-').$tail;
        }
        $form['slug']=$slug;
        foreach (array_keys($images) as $image) {
            if (!panelRow('SELECT id FROM '.panelTable('panel_image').' WHERE id=?',array($image))) { panelFail('Загруженное изображение не найдено.'); }
        }
        $now=time(); $published=$old['published_at'] ?? ($form['status']==='published' ? $now : null);
        $siteId=panelPublish($form,$old['site_id'] ?? null,$published,$state);
        $values=array(panelJson($form),$form['title'],$form['category'],$slug,$form['status'],$siteId,$now,$published);
        $fields='payload=?,title=?,category=?,slug=?,status=?,site_id=?,updated_at=?,published_at=?';
        if ($old) {
            panelQuery("UPDATE $table SET $fields,revision=revision+1,deleted=? WHERE id=?",array_merge($values,array((int)$delete,$id)));
        } else {
            panelQuery("INSERT INTO $table SET $fields,revision=1,id=?,request_id=?,request_hash=?,created_at=?",array_merge($values,array($id,$key,$digest,$now)));
        }
        if (!$old || $old['slug']!==$slug) {
            if (!panelRow("SELECT 1 FROM $aliases WHERE slug=?",array($slug))) { panelQuery("INSERT INTO $aliases (slug,article_id) VALUES (?,?)",array($slug,$id)); }
        }
        $refs=panelTable('panel_image_ref');
        panelScheduleStore($id,$schedule);
        panelQuery("DELETE FROM $refs WHERE article_id=?",array($id));
        foreach (array_keys($images) as $image) { panelQuery("INSERT INTO $refs VALUES (?,?)",array($image,$id)); }
        if ($siteId) { panelRefreshJournal(panelRow('SELECT journal_id FROM '.panelTable('panel_state').' WHERE id=1')['journal_id']); }
        return $delete ? null : panelRecord(panelLoad($id));
    });
    $affectedId=$id ?: ($result['id'] ?? null);
    if ($affectedId) {
        $page=panelRow('SELECT site_id FROM '.panelTable('panel_article').' WHERE id=?',array($affectedId));
        if ($page && $page['site_id']) { panelScheduleCache(); }
        panelScheduleFinish($affectedId);
    }
    return $delete ? null : panelRecord(panelLoad($affectedId));
}
