<?php
/** Narrow, transactional changes to old thermal links, tariff rows, menu targets and sitemap. */
if(!defined('DIAFAN')){http_response_code(404);exit;}
require_once dirname(__DIR__).'/custom/energo/includes/thermal_links.php';
function nwdThermalTableColumns($table) {
    if(!in_array($table,array('site','shop_category','shop','news','clauses','menu'),true)){throw new RuntimeException('Unsupported consolidation table');}
    return array_column(panelRows('SHOW COLUMNS FROM '.panelTable($table)),'Field');
}
function nwdThermalDelta($table,array $row,array $changes) {
    $columns=nwdThermalTableColumns($table);$before=array();$after=array();
    foreach($changes as $key=>$value){
        if(!in_array($key,$columns,true) || !preg_match('/^(?:text\d+|anons\d+|act\d+|noindex|map_no_show|timeedit|module_name|element_type|element_id|othurl)$/D',$key)){throw new RuntimeException('Unsupported consolidation field');}
        if((string)$row[$key] !== (string)$value){$before[$key]=$row[$key];$after[$key]=(string)$value;}
    }
    if(!$after){return null;}
    if(in_array('timeedit',$columns,true)){$before['timeedit']=$row['timeedit'];$after['timeedit']=(string)time();}
    return array('table'=>$table,'id'=>(int)$row['id'],'before'=>$before,'after'=>$after);
}
function nwdThermalApplyDelta(array $delta,$direction) {
    $table=$delta['table'];nwdThermalTableColumns($table);$values=$delta[$direction];$sets=array();
    foreach($values as $key=>$v){if(!preg_match('/^(?:text\d+|anons\d+|act\d+|noindex|map_no_show|timeedit|module_name|element_type|element_id|othurl)$/D',$key)){throw new RuntimeException('Invalid delta field');}$sets[]='`'.$key.'`=?';}
    panelQuery('UPDATE '.panelTable($table).' SET '.implode(',',$sets).' WHERE id=?',array_merge(array_values($values),array((int)$delta['id'])));
}
function nwdThermalConsolidate(array $copy,$lang,$check) {
    $local=getenv('NWD_LOCAL')==='1';$main=nwdThermalRoute($copy['path']);$old=nwdThermalRoute(nwdThermalOldPath());
    if(!$old && !$local){throw new RuntimeException('Existing legacy thermal route not found');}
    if($old && $main && $old['table']===$main['table'] && (int)$old['element_id']===(int)$main['element_id']){throw new RuntimeException('Thermal URLs resolve to the same native record');}
    $deltas=array();$maps=array();
    if($old){
        $delta=nwdThermalDelta($old['table'],$old['row'],array('act'.$lang=>'0','noindex'=>'1','map_no_show'=>'1'));if($delta){$deltas[]=$delta;}
        $maps=panelRows('SELECT * FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=? FOR UPDATE',array($old['module_name'],$old['element_type'],(int)$old['element_id']));
    }
    foreach(array('site','shop_category','shop','news','clauses') as $table){
        $columns=nwdThermalTableColumns($table);$fields=array_values(array_filter(array('text'.$lang,'anons'.$lang),function($f)use($columns){return in_array($f,$columns,true);}));
        if(!$fields){continue;}$where=array();$values=array();
        foreach($fields as $field){$where[]='(`'.$field.'` LIKE ? OR `'.$field.'` LIKE ?)';$values[]='%'.nwdThermalOldPath().'%';$values[]='%тепловизион%';}
        foreach(panelRows('SELECT * FROM '.panelTable($table).' WHERE '.implode(' OR ',$where).' FOR UPDATE',$values) as $row){
            if(($main && $table===$main['table'] && (int)$row['id']===(int)$main['element_id']) || ($old && $table===$old['table'] && (int)$row['id']===(int)$old['element_id'])){continue;}
            if(($row['theme']??'')==='panel_article.php'){continue;}
            $changes=array();foreach($fields as $field){$next=nwdThermalMarkup($row[$field],$copy);if($next!==$row[$field]){$changes[$field]=$next;}}
            $delta=nwdThermalDelta($table,$row,$changes);if($delta){$deltas[]=$delta;}
        }
    }
    $columns=nwdThermalTableColumns('menu');$where=array('othurl LIKE ?');$values=array('%'.nwdThermalOldPath().'%');
    if($old){$where[]='(module_name=? AND element_type=? AND element_id=?)';array_push($values,$old['module_name'],$old['element_type'],(int)$old['element_id']);}
    foreach(panelRows('SELECT * FROM '.panelTable('menu')." WHERE trash='0' AND (".implode(' OR ',$where).') FOR UPDATE',$values) as $row){
        $changes=array();$next=nwdThermalInternalUrl($row['othurl'],$copy);if($next!==$row['othurl']){$changes['othurl']=$next;}
        if($old && $main && $row['module_name']===$old['module_name'] && $row['element_type']===$old['element_type'] && (int)$row['element_id']===(int)$old['element_id']){$changes=array_merge($changes,array('module_name'=>$main['module_name'],'element_type'=>$main['element_type'],'element_id'=>(string)$main['element_id']));}
        $delta=nwdThermalDelta('menu',$row,$changes);if($delta){$deltas[]=$delta;}
    }
    if(!$check){
        foreach($deltas as $delta){nwdThermalApplyDelta($delta,'after');}
        if($old && $maps){panelQuery('DELETE FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=?',array($old['module_name'],$old['element_type'],(int)$old['element_id']));}
    }
    return array('deltas'=>$deltas,'removed_maps'=>$maps,'legacy'=>$old?array('module'=>$old['module_name'],'type'=>$old['element_type'],'id'=>(int)$old['element_id']):null);
}
function nwdThermalConsolidationRollback(array $saved) {
    // Check every touched field before restoring any related record.
    foreach($saved['deltas'] as $delta){nwdThermalTableColumns($delta['table']);$row=panelRow('SELECT * FROM '.panelTable($delta['table']).' WHERE id=? FOR UPDATE',array((int)$delta['id']));foreach($delta['after'] as $key=>$value){if(!$row || (string)($row[$key]??'')!==(string)$value){throw new RuntimeException('Related thermal content changed after installation; restore requires review');}}}
    if($saved['removed_maps']){
        $old=$saved['legacy'];$maps=panelRows('SELECT * FROM '.panelTable('map_index').' WHERE module_name=? AND element_type=? AND element_id=? FOR UPDATE',array($old['module'],$old['type'],(int)$old['id']));if($maps){throw new RuntimeException('Legacy thermal sitemap changed after installation');}
        foreach($saved['removed_maps'] as $row){if(panelRow('SELECT id FROM '.panelTable('map_index').' WHERE id=? FOR UPDATE',array((int)$row['id']))){throw new RuntimeException('Sitemap backup ID is occupied');}}
    }
    foreach($saved['deltas'] as $delta){nwdThermalApplyDelta($delta,'before');}
    foreach($saved['removed_maps'] as $row){$fields=array_keys($row);foreach($fields as $f){if(!preg_match('/^[a-z_]+$/D',$f)){throw new RuntimeException('Invalid sitemap backup field');}}panelQuery('INSERT INTO '.panelTable('map_index').' (`'.implode('`,`',$fields).'`) VALUES ('.implode(',',array_fill(0,count($fields),'?')).')',array_values($row));}
}
