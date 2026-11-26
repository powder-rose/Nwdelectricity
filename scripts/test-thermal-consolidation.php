<?php
/** In-memory transactional plan adapter: exact scoped writes, not a live database test. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('DIAFAN',true);require __DIR__.'/thermal-page.php';require __DIR__.'/thermal-consolidation.php';
$copy=require dirname(__DIR__).'/custom/energo/themes/thermal_content.php';$checks=0;
function thermalAssert($ok,$label){global $checks;if(!$ok){throw new RuntimeException($label);}$checks++;}
function thermalReject(callable $f){try{$f();}catch(RuntimeException $e){return true;}return false;}
function panelTable($table){return 'fixture_'.$table;}
$fixture=array();$routes=array();
function panelRows($sql,$args=array()){
 global $fixture,$routes;
 if(preg_match('/^SHOW COLUMNS FROM fixture_(\w+)/',$sql,$m)){
  $cols=$m[1]==='menu'?array('id','module_name','element_type','element_id','othurl','trash','timeedit'):array('id','text1','anons1','act1','noindex','map_no_show','timeedit');return array_map(function($c){return array('Field'=>$c);},$cols);
 }
 if(strpos($sql,'FROM fixture_rewrite')!==false){$out=array();foreach($routes as $path=>$route){if(in_array($path,$args,true)){$out[]=$route;}}return $out;}
 if(strpos($sql,'FROM fixture_map_index')!==false){return array_values(array_filter($fixture['map_index'],function($r)use($args){return $r['module_name']===$args[0] && $r['element_type']===$args[1] && (int)$r['element_id']===$args[2];}));}
 if(preg_match('/FROM fixture_(site|shop|shop_category|news|clauses|menu) WHERE/',$sql,$m)){
  if($m[1]==='menu'){return array_values(array_filter($fixture['menu'],function($r){return strpos($r['othurl'],nwdThermalOldPath())!==false || ($r['module_name']==='shop' && $r['element_type']==='cat' && $r['element_id']==='43');}));}
  return array_values(array_filter($fixture[$m[1]],function($r){$s=($r['text1']??'').' '.($r['anons1']??'');return strpos($s,nwdThermalOldPath())!==false || mb_stripos($s,'тепловизион',0,'UTF-8')!==false;}));
 }
 throw new RuntimeException('Unexpected list query: '.$sql);
}
function panelRow($sql,$args=array()){
 global $fixture;
 if(preg_match('/FROM fixture_(\w+) WHERE id=\?/',$sql,$m)){return $fixture[$m[1]][$args[0]]??null;}
 throw new RuntimeException('Unexpected row query: '.$sql);
}
function panelQuery($sql,$args=array()){
 global $fixture;
 if(preg_match('/^UPDATE fixture_(\w+) SET/',$sql,$m)){preg_match_all('/`(\w+)`=\?/',$sql,$fields);$id=$args[count($args)-1];foreach($fields[1] as $i=>$key){$fixture[$m[1]][$id][$key]=(string)$args[$i];}return;}
 if(strpos($sql,'DELETE FROM fixture_map_index')===0){foreach(panelRows(str_replace('DELETE FROM','SELECT * FROM',$sql),$args) as $row){unset($fixture['map_index'][$row['id']]);}return;}
 if(preg_match('/^INSERT INTO fixture_map_index \((.+)\) VALUES/',$sql,$m)){preg_match_all('/`(\w+)`/',$m[1],$names);$row=array_combine($names[1],$args);$fixture['map_index'][$row['id']]=$row;return;}
 throw new RuntimeException('Unexpected write query: '.$sql);
}
$old='/'.nwdThermalOldPath().'/';$main='/'.$copy['path'].'/';
$markup='<p>Other prose.</p><a href="'.$old.'?utm_source=old#node">Тепловизионный контроль</a><a href="https://external.example'.$old.'">External</a><table><tr><td>Тепловизионное обследование электрического оборудования</td><td>11&nbsp;000 руб.</td></tr><tr><td>Other service</td><td>11000 руб.</td></tr></table>';
$changed=nwdThermalMarkup($markup,$copy);
thermalAssert(strpos($changed,'href="'.$main.'?utm_source=old#node"')!==false,'tagged internal link points directly to primary');
thermalAssert(strpos($changed,'href="https://external.example'.$old.'"')!==false,'external URL remains unchanged');
thermalAssert(strpos($changed,'от 4 900 ₽')!==false && substr_count($changed,'11000 руб.')===1,'only the thermal tariff row changes');
thermalAssert(nwdThermalMarkup($changed,$copy)===$changed,'markup update is idempotent');
$escaped=htmlspecialchars($markup,ENT_QUOTES,'UTF-8');thermalAssert(html_entity_decode(nwdThermalMarkup($escaped,$copy),ENT_QUOTES,'UTF-8')===$changed,'HTML-escaped native field is handled');
foreach(array('11000','11 000 руб.','11 000 ₽','11&nbsp;000 руб.','<strong>11 000</strong> руб.') as $p){$s='<tr><td>Тепловизионный контроль</td><td>'.$p.'</td></tr>';thermalAssert(strpos(nwdThermalMarkup($s,$copy),'от 4 900 ₽')!==false,'supported old tariff formats');}
foreach(array('https://nwdelectricity.ru'.$old,'http://www.nwdelectricity.ru'.$old,ltrim($old,'/'),'//nwdelectricity.ru'.$old) as $u){thermalAssert(nwdThermalInternalUrl($u,$copy)===$main,'same-site URL variant');}
thermalAssert(nwdThermalInternalUrl('https://evil.example'.$old,$copy)==='https://evil.example'.$old,'foreign host not retargeted');
function resetThermalFixture(){
 global $fixture,$routes,$copy,$markup;
 $row=function($id,$text=''){return array('id'=>(string)$id,'text1'=>$text,'anons1'=>'','act1'=>'1','noindex'=>'0','map_no_show'=>'0','timeedit'=>'10');};
 $routes=array($copy['path']=>array('module_name'=>'shop','element_type'=>'cat','element_id'=>'42'),nwdThermalOldPath()=>array('module_name'=>'shop','element_type'=>'cat','element_id'=>'43'));
 $fixture=array('site'=>array(1=>$row(1,$markup),2=>$row(2,'Unrelated page')),'shop_category'=>array(42=>$row(42,'Primary source'),43=>$row(43,'Legacy source')),'shop'=>array(),'news'=>array(5=>$row(5,'<a href="/'.nwdThermalOldPath().'/">Article link</a>')),'clauses'=>array(),'menu'=>array(7=>array('id'=>'7','module_name'=>'shop','element_type'=>'cat','element_id'=>'43','othurl'=>'','trash'=>'0','timeedit'=>'10'),8=>array('id'=>'8','module_name'=>'','element_type'=>'','element_id'=>'0','othurl'=>'/'.nwdThermalOldPath().'/','trash'=>'0','timeedit'=>'10')),'map_index'=>array(90=>array('id'=>'90','module_name'=>'shop','element_type'=>'cat','element_id'=>'43','url'=>nwdThermalOldPath().'ROUTE_END'),91=>array('id'=>'91','module_name'=>'shop','element_type'=>'cat','element_id'=>'42','url'=>$copy['path'].'ROUTE_END')));
}
putenv('NWD_LOCAL=0');resetThermalFixture();$before=$fixture;$plan=nwdThermalConsolidate($copy,1,true);thermalAssert($fixture===$before,'preflight does not write');thermalAssert(count($plan['deltas'])===5,'only old record, two content records and two menu targets');
$saved=nwdThermalConsolidate($copy,1,false);thermalAssert($fixture['shop_category'][43]['act1']==='0' && $fixture['shop_category'][43]['noindex']==='1' && $fixture['shop_category'][43]['map_no_show']==='1','duplicate excluded from active catalogue and indexing');
thermalAssert($fixture['shop_category'][43]['text1']==='Legacy source' && $fixture['shop_category'][42]===$before['shop_category'][42],'original content and primary record retained by consolidation');
thermalAssert(count($fixture['map_index'])===1 && isset($fixture['map_index'][91]),'sitemap keeps primary and removes duplicate');
thermalAssert($fixture['menu'][7]['element_id']==='42' && $fixture['menu'][8]['othurl']===$main,'native and custom menu links point to primary');
thermalAssert($fixture['site'][2]===$before['site'][2],'unrelated content unchanged');
$again=nwdThermalConsolidate($copy,1,false);thermalAssert(!$again['deltas'] && !$again['removed_maps'],'repeat consolidation is empty');
nwdThermalConsolidationRollback($saved);ksort($fixture['map_index']);thermalAssert($fixture===$before,'all touched native fields and sitemap restored exactly');
resetThermalFixture();$saved=nwdThermalConsolidate($copy,1,false);$fixture['site'][1]['text1']='Independent article edit';$current=$fixture;thermalAssert(thermalReject(function()use($saved){nwdThermalConsolidationRollback($saved);}) && $fixture===$current,'editor changes stop rollback before any writes');
resetThermalFixture();unset($routes[nwdThermalOldPath()]);$before=$fixture;thermalAssert(thermalReject(function()use($copy){nwdThermalConsolidate($copy,1,true);}) && $fixture===$before,'missing legacy production route stops before writes');
putenv('NWD_LOCAL=1');nwdThermalConsolidate($copy,1,true);thermalAssert($fixture===$before,'missing old local route can be previewed without creating it');
resetThermalFixture();$routes[nwdThermalOldPath()]=$routes[$copy['path']];thermalAssert(thermalReject(function()use($copy){nwdThermalConsolidate($copy,1,true);}), 'shared native record cannot be disabled');
echo 'THERMAL_CONSOLIDATION_OK='.$checks."\n";
