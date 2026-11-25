<?php
/** Native record updates are scoped by an existing rewrite, never by a guessed site ID. */
if (!defined('DIAFAN')) { http_response_code(404);exit; }
require_once __DIR__.'/rcd-lab-link.php';
function nwdLabFingerprint(array $row) {
    // Public reads may increment counters. Protect the edited fields and editorial content,
    // while leaving independent traffic statistics outside the rollback comparison.
    $values=array();foreach($row as $key=>$value){if(preg_match('/^(?:id|theme|parent_id|site_id|name\d+|title_meta\d+|descr\d+|canonical\d+|text\d+|act\d+|noindex|map_no_show|access|trash|timeedit)$/D',$key)){$values[$key]=$value;}}
    ksort($values);return hash('sha256',json_encode($values,JSON_UNESCAPED_UNICODE));
}
function nwdLabRoute($path,$lock=true) {
    $routes=panelRows('SELECT module_name,element_type,element_id FROM '.panelTable('rewrite').' WHERE rewrite IN (?,?,?,?)'.($lock?' FOR UPDATE':''),array($path,$path.'/','/'.$path,'/'.$path.'/'));
    if (count($routes)>1) { throw new RuntimeException('Laboratory route is ambiguous'); }
    if (!$routes) { return null; }
    $route=$routes[0];$route['table']=nwdRcdLaboratoryTable($route['module_name'],$route['element_type']);
    $route['row']=panelRow('SELECT * FROM '.panelTable($route['table']).' WHERE id=?'.($lock?' FOR UPDATE':''),array((int)$route['element_id']));
    if (!$route['row']) { throw new RuntimeException('Laboratory route target is missing'); }
    return $route;
}
function nwdLabChanges(array $row,array $copy,$lang) {
    $values=array('theme'=>'laboratory.php','name'.$lang=>panelHtml($copy['name']),'title_meta'.$lang=>panelHtml($copy['seoTitle']),'descr'.$lang=>panelHtml($copy['description']),'canonical'.$lang=>$copy['path'].'/','noindex'=>'0','map_no_show'=>'0');
    foreach($values as $key=>$v){if(!array_key_exists($key,$row)){throw new RuntimeException('Native laboratory schema is incomplete: '.$key);}}
    if (($row['trash']??'1')!=='0' || ($row['act'.$lang]??'0')!=='1' || ($row['access']??'1')!=='0') { throw new RuntimeException('Laboratory must already be public'); }
    return $values;
}
function nwdLabWrite($table,$id,array $values) {
    nwdRcdLaboratoryBackupTable(array('table'=>$table));$sets=array();
    foreach($values as $key=>$v){if(!preg_match('/^(?:theme|name\d+|title_meta\d+|descr\d+|canonical\d+|noindex|map_no_show|timeedit)$/D',$key)){throw new RuntimeException('Invalid laboratory update field');}$sets[]='`'.$key.'`=?';}
    panelQuery('UPDATE '.panelTable($table).' SET '.implode(',',$sets).' WHERE id=?',array_merge(array_values($values),array((int)$id)));
}
function nwdLabInstall(array $copy,$lang,$allowLocal,$check) {
    if($allowLocal && getenv('NWD_LOCAL')!=='1'){throw new RuntimeException('Local bootstrap requires NWD_LOCAL=1');}
    $path=$copy['path'];$route=nwdLabRoute($path);$created=false;
    $redirect=panelRow('SELECT id FROM '.panelTable('redirect').' WHERE redirect IN (?,?,?,?) LIMIT 1',array($path,$path.'/','/'.$path,'/'.$path.'/'));
    if($redirect){throw new RuntimeException('Laboratory route conflicts with an existing redirect');}
    if(!$route){
        if(!$allowLocal){throw new RuntimeException('Existing laboratory route not found; production page creation is disabled');}
        $parent=panelRow('SELECT s.id FROM '.panelTable('site')." s JOIN ".panelTable('rewrite')." r ON r.element_id=s.id AND r.module_name='site' AND r.element_type='element' WHERE r.rewrite='services' AND s.trash='0' FOR UPDATE");
        if(!$parent){throw new RuntimeException('Services parent route not found');}
        if($check){echo "LABORATORY_LOCAL_BOOTSTRAP_READY\n";return null;}
        panelQuery('INSERT INTO '.panelTable('site')." SET parent_id=?,theme='laboratory.php',name$lang=?,title_meta$lang=?,descr$lang=?,text$lang='',act$lang='1',trash='0',access='0',canonical$lang=?,noindex='0',map_no_show='0',timeedit=?,changefreq='monthly'",array((int)$parent['id'],panelHtml($copy['name']),panelHtml($copy['seoTitle']),panelHtml($copy['description']),$path.'/',time()));
        $id=(int)panelDb()->insert_id;
        panelQuery('INSERT INTO '.panelTable('rewrite')." (rewrite,module_name,element_id,element_type) VALUES (?,'site',?,'element')",array($path,$id));
        panelQuery('INSERT INTO '.panelTable('site_parents').' (element_id,parent_id) VALUES (?,?)',array($id,(int)$parent['id']));
        foreach(panelRows('SELECT parent_id FROM '.panelTable('site_parents').' WHERE element_id=?',array((int)$parent['id'])) as $a){if((int)$a['parent_id']!==(int)$parent['id']){panelQuery('INSERT INTO '.panelTable('site_parents').' (element_id,parent_id) VALUES (?,?)',array($id,(int)$a['parent_id']));}}
        $route=nwdLabRoute($path);$created=true;
    }
    $row=$route['row'];$id=(int)$route['element_id'];$table=$route['table'];
    $values=nwdLabChanges($row,$copy,$lang);
    $maps=panelRows('SELECT * FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=? FOR UPDATE',array($route['module_name'],$route['element_type'],$id));
    if(count($maps)>1){throw new RuntimeException('Laboratory sitemap entry is ambiguous');}
    if($check){return null;}
    $before=array();$different=false;
    foreach($values as $k=>$v){$before[$k]=$row[$k];if((string)$row[$k]!==$v){$different=true;}}
    if($different){$before['timeedit']=$row['timeedit'];$values['timeedit']=time();nwdLabWrite($table,$id,$values);}
    $mapCreated=false;
    if(!$maps){panelQuery('INSERT INTO '.panelTable('map_index')." (url,module_name,element_id,element_type,timeedit,changefreq,priority,date_start,date_finish) VALUES (?,?,?,?,?,'monthly','0.7',0,0)",array($path.'ROUTE_END',$route['module_name'],$id,$route['element_type'],time()));$mapCreated=true;}
    // Existing sitemap entries and native text/media are deliberately not rewritten.
    $after=panelRow('SELECT * FROM '.panelTable($table).' WHERE id=?',array($id));
    return array('path'=>$path,'id'=>$id,'table'=>$table,'module'=>$route['module_name'],'type'=>$route['element_type'],'created'=>$created,'before'=>$before,'after'=>nwdLabFingerprint($after),'map_created'=>$mapCreated,'map_after'=>$mapCreated?panelRows('SELECT * FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=?',array($route['module_name'],$route['element_type'],$id)):array());
}
function nwdLabRollback(array $copy,array $saved) {
    if(($saved['path']??'')!==$copy['path']){throw new RuntimeException('Invalid laboratory backup');}
    $route=nwdLabRoute($copy['path']);
    if(!$route || $route['table']!==$saved['table'] || (int)$route['element_id']!==(int)$saved['id'] || !hash_equals($saved['after'],nwdLabFingerprint($route['row']))){throw new RuntimeException('Laboratory changed after installation; restore requires review');}
    $id=(int)$saved['id'];$table=$route['table'];
    if($saved['map_created']){
        $maps=panelRows('SELECT * FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=? FOR UPDATE',array($route['module_name'],$route['element_type'],$id));
        if($maps!==$saved['map_after']){throw new RuntimeException('Laboratory sitemap changed after installation');}
        panelQuery('DELETE FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=?',array($route['module_name'],$route['element_type'],$id));
    }
    if($saved['created']){
        if(getenv('NWD_LOCAL')!=='1' || $table!=='site'){throw new RuntimeException('Removing a bootstrap page requires local mode');}
        panelQuery('DELETE FROM '.panelTable('rewrite')." WHERE module_name='site' AND element_type='element' AND element_id=? AND rewrite=?",array($id,$copy['path']));
        panelQuery('DELETE FROM '.panelTable('site_parents').' WHERE element_id=?',array($id));panelQuery('DELETE FROM '.panelTable('site').' WHERE id=?',array($id));
    }else{nwdLabWrite($table,$id,$saved['before']);}
}
