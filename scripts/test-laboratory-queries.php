<?php
/** Exercise DIAFAN's real placeholder formatter, without requiring a live database. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('DIAFAN',true);define('_LANG',1);define('DB_PREFIX','nwd_fixture_');define('MOD_DEVELOPER',true);
require dirname(__DIR__).'/includes/database.php';
require dirname(__DIR__).'/custom/energo/themes/laboratory_links.php';
class NwdLaboratoryQueryBackend {
    public $queries=array();public $rows=array();
    public function query($sql) {
        $this->queries[]=$sql;
        if(!preg_match('~TRIM\(BOTH \'/\' FROM r\.rewrite\) IN \((.*?)\) AND~s',$sql,$matches)) { throw new RuntimeException('Expected native route lookup'); }
        // DIAFAN %s escapes the value but does not supply SQL string delimiters.
        $literal="'(?:\\\\.|[^'\\\\])*'";
        if(!preg_match('~^'.$literal.'(?:,'.$literal.')*$~D',$matches[1])) { throw new RuntimeException('Route strings must be quoted in formatted SQL'); }
        if(strpos($sql,'{rewrite}')!==false || strpos($sql,'`nwd_fixture_rewrite`')===false) { throw new RuntimeException('Native table prefix was not applied'); }
        if(strpos($sql,'act1')===false || strpos($sql,"access='0'")===false) { throw new RuntimeException('Public route guard missing'); }
        return (object)array('rows'=>$this->rows);
    }
    public function fetch_array($result) { return array_shift($result->rows); }
    public function free_result($result) { return true; }
    public function escape_string($value) { return addslashes($value); }
}
DB::$backend=new NwdLaboratoryQueryBackend;
$checks=0;
function queryCheck($ok,$label){global $checks;if(!$ok){throw new RuntimeException($label);}$checks++;}
set_error_handler(function($code,$message){throw new RuntimeException($message);});
queryCheck(nwdLaboratoryActivePaths(array())===array() && !DB::$backend->queries,'empty lookup does not run SQL');
DB::$backend->rows=array(array('rewrite'=>'services/proverka-uzo'),array('rewrite'=>'/services/ispytanie-elektroinstrumenta/'));
$paths=array(3=>'/services/proverka-uzo/',8=>'services/ispytanie-elektroinstrumenta/');
$active=nwdLaboratoryActivePaths($paths);$sql=end(DB::$backend->queries);
queryCheck(isset($active['/services/proverka-uzo/'],$active['/services/ispytanie-elektroinstrumenta/']),'native formatter preserves sparse argument lists and paths');
queryCheck(strpos($sql,"IN ('services/proverka-uzo','services/ispytanie-elektroinstrumenta')")!==false,'final SQL contains string literals');
DB::$backend->rows=array();queryCheck(nwdLaboratoryActivePaths(array('/articles/unpublished/'))===array(),'empty native result stays unavailable');
$bad="/services/quote'path/";nwdLaboratoryActivePaths(array($bad));
queryCheck(strpos(end(DB::$backend->queries),"quote\\'path")!==false,'native string escaping stays active');
restore_error_handler();
echo 'LABORATORY_NATIVE_QUERY_OK='.$checks."\n";
