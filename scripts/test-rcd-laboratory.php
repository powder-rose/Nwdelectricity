<?php
/** Route and environment regression tests; no database, page writes or external calls. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
define('DIAFAN',true);require __DIR__.'/rcd-lab-link.php';
$checks=0;$routes=array();$target=null;$targetSql='';$targetArgs=array();
function labCheck($ok,$name) { global $checks;if (!$ok) { throw new RuntimeException($name); }$checks++; }
function labRefuses(callable $operation) { try { $operation(); } catch (RuntimeException $error) { return true; }return false; }
function panelTable($table) { return 'fixture_'.$table; }
function panelRows($sql,$args) {
    global $routes;
    if (strpos($sql,'fixture_rewrite')===false || $args!==array('services/elektroizmeritelnaya-laboratoriya','services/elektroizmeritelnaya-laboratoriya/','/services/elektroizmeritelnaya-laboratoriya','/services/elektroizmeritelnaya-laboratoriya/')) { throw new RuntimeException('Unexpected route lookup'); }
    return $routes;
}
function panelRow($sql,$args) { global $target,$targetSql,$targetArgs;$targetSql=$sql;$targetArgs=$args;return $target; }
foreach (array(array('site','element','site'),array('shop','cat','shop_category'),array('shop','element','shop')) as $case) {
    list($module,$type,$table)=$case;
    $routes=array(array('module_name'=>$module,'element_type'=>$type,'element_id'=>42));
    $target=array('id'=>42,'text1'=>'<ul><li>Срабатывание УЗО;</li></ul>','timeedit'=>10);
    $lab=nwdRcdLaboratory(1,false);
    labCheck($lab['table']===$table && $lab['id']===42 && $lab['text1']===$target['text1'],'native target resolved: '.$table);
    labCheck(strpos($targetSql,'FROM fixture_'.$table.' WHERE id=?')!==false && strpos($targetSql,"trash='0'")!==false && $targetArgs===array(42),'reads the resolved native row only');
}
$routes=array();
labCheck(labRefuses(function(){ nwdRcdLaboratory(1,false); }),'missing production route blocks deployment');
labCheck(nwdRcdLaboratory(1,true)===null,'old local dump may omit laboratory');
$routes=array(array('module_name'=>'shop','element_type'=>'cat','element_id'=>42));$target=null;
labCheck(labRefuses(function(){ nwdRcdLaboratory(1,false); }),'missing production target blocks deployment');
labCheck(nwdRcdLaboratory(1,true)===null,'missing local target leaves original data alone');
$routes[]=$routes[0];
labCheck(labRefuses(function(){ nwdRcdLaboratory(1,true); }),'ambiguous routes are never guessed');
$routes=array(array('module_name'=>'other','element_type'=>'element','element_id'=>42));
labCheck(labRefuses(function(){ nwdRcdLaboratory(1,true); }),'unknown module does not select an arbitrary table');
labCheck(nwdRcdLocalLaboratoryOption(true,true),'explicit local option works');
labCheck(!nwdRcdLocalLaboratoryOption(false,true),'local marker alone does not skip a missing route');
labCheck(!nwdRcdLocalLaboratoryOption(false,false),'production is strict by default');
labCheck(labRefuses(function(){ nwdRcdLocalLaboratoryOption(true,false); }),'skip option is rejected outside local mode');
labCheck(nwdRcdLaboratoryBackupTable(array())==='site','old rollback record stays compatible');
labCheck(nwdRcdLaboratoryBackupTable(array('table'=>'shop_category'))==='shop_category','rollback targets original category table');
labCheck(labRefuses(function(){ nwdRcdLaboratoryBackupTable(array('table'=>'other')); }),'rollback table is allowlisted');
echo 'RCD_LABORATORY_OK='.$checks."\n";
