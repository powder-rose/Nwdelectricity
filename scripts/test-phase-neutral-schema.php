<?php
/** Isolated bootstrap contracts; no site config, real database or deliveries are loaded. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
define('NWD_PANEL',true);
require __DIR__.'/phase-neutral-schema.php';
if (!class_exists('mysqli_sql_exception')) { class mysqli_sql_exception extends RuntimeException {} }
class NwdSchemaResult { public function free() {} }
class NwdSchemaDatabase {
    public $tables=array();public $writes=array();public $reads=0;public $denied=false;public $badColumns=false;
    public function query($sql) {
        if (preg_match('/^SELECT .* FROM `([^`]+)` LIMIT 0$/D',$sql,$match)) {
            $this->reads++;
            if (!isset($this->tables[$match[1]])) { throw new mysqli_sql_exception('missing',1146); }
            if ($this->badColumns) { throw new mysqli_sql_exception('bad column',1054); }
            return new NwdSchemaResult;
        }
        if (preg_match('/^CREATE TABLE IF NOT EXISTS `([^`]+)` /D',$sql,$match)) {
            if ($this->denied) { throw new mysqli_sql_exception('denied',1142); }
            $this->writes[]=$sql;
            if (!isset($this->tables[$match[1]])) { $this->tables[$match[1]]=array(); }
            return true;
        }
        throw new RuntimeException('Unexpected query');
    }
}
$checks=0;
function schemaCheck($ok,$label) { global $checks;if (!$ok) { throw new RuntimeException($label); }$checks++; }
$db=new NwdSchemaDatabase;
schemaCheck(nwdFeedbackSchemaMissing($db,'test_')===array('nwd_feedback_request','boykov_lead_outbox'),'detects both missing tables');
schemaCheck(!$db->writes && !$db->tables,'inspection is read-only');
try { nwdPrepareLocalFeedbackSchema($db,'test_',false);throw new LogicException('Local guard failed'); }
catch (RuntimeException $error) { schemaCheck(!$db->writes,'production is not bootstrapped implicitly'); }
ob_start();nwdPrepareLocalFeedbackSchema($db,'test_',true);$output=ob_get_clean();
schemaCheck(count($db->writes)===2 && count($db->tables)===2,'creates missing tables');
schemaCheck(strpos($output,'PHASE_NEUTRAL_LOCAL_SCHEMA_OK')!==false,'explicit completion marker');
$db->tables['test_nwd_feedback_request'][]=array('sentinel'=>'existing local receipt');
$before=$db->tables;
ob_start();nwdPrepareLocalFeedbackSchema($db,'test_',true);ob_end_clean();
schemaCheck(count($db->writes)===2 && $db->tables===$before,'repeat preserves existing tables and records');
$one=new NwdSchemaDatabase;$one->tables['test_boykov_lead_outbox']=array(array('sentinel'=>'existing local outbox'));
ob_start();nwdPrepareLocalFeedbackSchema($one,'test_',true);ob_end_clean();
schemaCheck(count($one->writes)===1 && $one->tables['test_boykov_lead_outbox'][0]['sentinel']==='existing local outbox','one missing table leaves the other unchanged');
foreach ($db->writes as $sql) {
    schemaCheck(strpos($sql,'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4')!==false,'transactional UTF-8 schema');
    schemaCheck(!preg_match('/\b(?:ALTER|DROP|TRUNCATE|DELETE)\b/i',$sql),'additive DDL only');
}
$denied=new NwdSchemaDatabase;$denied->denied=true;
try { nwdPrepareLocalFeedbackSchema($denied,'test_',true);throw new LogicException('Permission error ignored'); }
catch (mysqli_sql_exception $error) { schemaCheck($error->getCode()===1142 && !$denied->tables,'permission failure is preserved'); }
$bad=new NwdSchemaDatabase;$bad->tables=$db->tables;$bad->badColumns=true;
try { nwdFeedbackSchemaMissing($bad,'test_');throw new LogicException('Bad columns ignored'); }
catch (RuntimeException $error) { schemaCheck(strpos($error->getMessage(),'MYSQL_CODE=1054')!==false && !$bad->writes,'incompatible existing table is reported, not replaced'); }
try { nwdFeedbackSchemaMissing($db,'bad`prefix');throw new LogicException('Bad prefix accepted'); }
catch (RuntimeException $error) { schemaCheck($db->tables===$before,'unsafe identifiers rejected'); }
echo 'PHASE_NEUTRAL_SCHEMA_TEST_OK='.$checks."\n";
