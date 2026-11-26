<?php
/** In-memory native migration adapter: no real database or external writes. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('DIAFAN',true);require __DIR__.'/relay-page.php';
$copy=require dirname(__DIR__).'/custom/energo/themes/relay_content.php';
$state=array();$checks=0;
function labAssert($ok,$label){global $checks;if(!$ok){throw new RuntimeException($label);}$checks++;}
function labReject(callable $fn){try{$fn();}catch(RuntimeException $e){return true;}return false;}
function panelTable($table){return 'fixture_'.$table;}
function panelHtml($text){return htmlspecialchars($text,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');}
function panelDb(){return (object)array('insert_id'=>321);}
function panelRows($sql,$args=array()){
 global $state;
 if(strpos($sql,'FROM fixture_rewrite')!==false){return $state['routes'];}
 if(strpos($sql,'FROM fixture_shop_price')!==false){return $state['prices']??array();}
 if(strpos($sql,'FROM fixture_map_index')!==false){return $state['maps'];}
 if(strpos($sql,'FROM fixture_site_parents')!==false){return array(array('parent_id'=>1));}
 throw new RuntimeException('Unexpected list: '.$sql);
}
function panelRow($sql,$args=array()){
 global $state;
 if(strpos($sql,'FROM fixture_shop_price')!==false){foreach(($state['prices']??array()) as $price){if((int)$price['id']===$args[0]){return $price;}}return null;}
 if(strpos($sql,'FROM fixture_redirect')!==false){return $state['redirect'];}
 if(strpos($sql,'JOIN fixture_rewrite')!==false){return array('id'=>10);}
 if(preg_match('/FROM fixture_(site|shop|shop_category) WHERE id=\?/',$sql,$m)){
  labAssert($m[1]===$state['table'] && $args[0]===(int)$state['row']['id'],'read correct native target');return $state['row'];
 }
 throw new RuntimeException('Unexpected single: '.$sql);
}
function panelQuery($sql,$args=array()){
 global $state;
 if(strpos($sql,'UPDATE fixture_shop_price')===0){foreach($state['prices'] as &$price){if((int)$price['id']===$args[1]){$price['price']=$args[0];}}unset($price);return;}
 if(strpos($sql,'UPDATE fixture_')===0){
  preg_match_all('/`(\w+)`=\?/',$sql,$m);foreach($m[1] as $i=>$key){$state['row'][$key]=(string)$args[$i];}$state['writes']++;return;
 }
 if(strpos($sql,'INSERT INTO fixture_map_index')===0){$state['maps']=array(array('id'=>'8','url'=>$args[0],'module_name'=>$args[1],'element_id'=>(string)$args[2],'element_type'=>$args[3]));return;}
 if(strpos($sql,'DELETE FROM fixture_map_index')===0){$state['maps']=array();return;}
 if(strpos($sql,'INSERT INTO fixture_site SET')===0){
  $state['table']='site';$state['row']=array('id'=>'321','name1'=>$args[1],'title_meta1'=>$args[2],'descr1'=>$args[3],'canonical1'=>$args[4],'noindex'=>'0','map_no_show'=>'0','timeedit'=>(string)$args[5],'act1'=>'1','trash'=>'0','access'=>'0','theme'=>'relay.php','text1'=>'');return;
 }
 if(strpos($sql,'INSERT INTO fixture_rewrite')===0){$state['routes']=array(array('module_name'=>'site','element_type'=>'element','element_id'=>'321'));return;}
 if(strpos($sql,'INSERT INTO fixture_site_parents')===0){return;}
 if(strpos($sql,'DELETE FROM fixture_rewrite')===0){$state['routes']=array();return;}
 if(strpos($sql,'DELETE FROM fixture_site_parents')===0){return;}
 if(strpos($sql,'DELETE FROM fixture_site')===0){$state['row']=null;return;}
 throw new RuntimeException('Unexpected write: '.$sql);
}
function fixture($module='shop',$type='cat'){
 global $state;$table=nwdRcdLaboratoryTable($module,$type);
 $state=array('routes'=>array(array('module_name'=>$module,'element_type'=>$type,'element_id'=>'42')),'table'=>$table,'row'=>array('id'=>'42','name1'=>'Старое имя','title_meta1'=>'Старый title','descr1'=>'Старое описание','canonical1'=>'','noindex'=>'0','map_no_show'=>'0','timeedit'=>'10','act1'=>'1','trash'=>'0','access'=>'0','text1'=>'<p>Старое содержимое, другие служебные данные.</p>','theme'=>'module.php','unrelated'=>'unchanged'),'maps'=>array(array('id'=>'9','url'=>'existing-native-sitemap','element_id'=>'42','module_name'=>$module,'element_type'=>$type)),'redirect'=>null,'prices'=>array(),'writes'=>0);
}
putenv('NWD_LOCAL=0');
foreach(array(array('site','element'),array('shop','cat'),array('shop','element')) as $case){
 fixture(...$case);$before=$state;nwdRelayInstall($copy,1,false,true);labAssert($state===$before,'preflight is read-only');
 $record=nwdRelayInstall($copy,1,false,false);
 labAssert($state['row']['id']==='42' && $state['routes']===$before['routes'],'route and identity remain unchanged');
 labAssert($state['row']['text1']===$before['row']['text1'] && $state['row']['unrelated']==='unchanged','legacy source and unrelated native fields retained for rollback');
 labAssert($state['row']['title_meta1']===panelHtml($copy['seoTitle']) && $state['row']['descr1']===panelHtml($copy['description']),'native SEO updated on resolved table');
 labAssert($state['maps']===$before['maps'],'existing sitemap entry preserved');
 labAssert($state['row']['theme']==='relay.php' && $record['before']['theme']==='module.php','native target selects the laboratory shell and backs up the previous theme');
 $current=$state;nwdRelayInstall($copy,1,false,false);labAssert($state===$current,'repeat installation does not change native metadata or time');
 nwdRelayRollback($copy,$record);labAssert($state['row']===$before['row'],'native record restored exactly');
}
fixture();$state['maps']=array();$before=$state;$saved=nwdRelayInstall($copy,1,false,false);labAssert($saved['map_created'] && count($state['maps'])===1,'missing map entry added only for this native route');nwdRelayRollback($copy,$saved);labAssert(!$state['maps'] && $state['row']===$before['row'],'rollback removes only newly added map entry');
fixture();$state['row']['counter']='9';$saved=nwdRelayInstall($copy,1,false,false);$state['row']['counter']='10';nwdRelayRollback($copy,$saved);labAssert($state['row']['counter']==='10','traffic counters do not block metadata rollback or get overwritten');
fixture();$saved=nwdRelayInstall($copy,1,false,false);$state['row']['text1']='Independent editor change';labAssert(labReject(function()use($copy,$saved){nwdRelayRollback($copy,$saved);}), 'post-install editor changes block destructive rollback');
fixture();$state['routes']=array();labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}), 'missing production route is not created');labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,true,false);}), 'local bootstrap flag rejected in production');
putenv('NWD_LOCAL=1');nwdRelayInstall($copy,1,true,true);labAssert(!$state['routes'],'local bootstrap preflight does not create page');$saved=nwdRelayInstall($copy,1,true,false);labAssert($saved['created'] && $saved['id']===321,'explicit local bootstrap creates intended page');nwdRelayRollback($copy,$saved);labAssert(!$state['routes'] && !$state['row'],'local bootstrap rollback removes created page');
fixture();$state['routes'][]=$state['routes'][0];labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}), 'ambiguous native routes rejected');
fixture();$state['redirect']=array('id'=>3);labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}), 'existing redirect conflict rejected');
foreach(array('act1'=>'0','trash'=>'1','access'=>'1') as $key=>$value){fixture();$state['row'][$key]=$value;labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}), 'private or archived target rejected');}
fixture();unset($state['row']['title_meta1']);labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}), 'incomplete schema blocks updates');
require dirname(__DIR__).'/custom/energo/themes/editorial_pages.php';
foreach(array($copy['path'], '/'.$copy['path'].'/', 'https://nwdelectricity.ru/'.$copy['path'].'/?q=1') as $path){$d=(object)array('_site'=>(object)array('rewrite'=>$path));labAssert(NwdEditorialPages::isRelay($d),'laboratory dispatch matches exact page');}
foreach(array('services','services/proverka-uzo',$copy['path'].'/child') as $path){$d=(object)array('_site'=>(object)array('rewrite'=>$path));labAssert(!NwdEditorialPages::isRelay($d),'other pages keep their existing dispatch');}
fixture('shop','element');$state['prices']=array(array('id'=>'7','price'=>'213'));$before=$state;$saved=nwdRelayInstall($copy,1,false,false);labAssert((float)$state['prices'][0]['price']===9900.0,'native card price updated only for resolved service');nwdRelayRollback($copy,$saved);labAssert($state['prices']===$before['prices'],'native price restored');
fixture('shop','element');$state['prices']=array(array('id'=>'7','price'=>'213'),array('id'=>'8','price'=>'1421'));$before=$state;labAssert(labReject(function()use($copy){nwdRelayInstall($copy,1,false,false);}) && $state===$before,'ambiguous service price variants require review');
fixture();$state['row']['anons1']='от 213 руб.';$saved=nwdRelayInstall($copy,1,false,false);labAssert($state['row']['anons1']===panelHtml($copy['intro']),'obsolete card teaser replaced');nwdRelayRollback($copy,$saved);labAssert($state['row']['anons1']==='от 213 руб.','old card teaser backed up and restored');
echo 'RELAY_PAGE_OK='.$checks."\n";
