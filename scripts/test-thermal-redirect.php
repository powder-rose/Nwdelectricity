<?php
/** The real Init private hook, using only a stub for Core transport and native lookup. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('DIAFAN',true);define('ABSOLUTE_PATH',dirname(__DIR__).'/');define('BASE_PATH','http://localhost:8080/');define('REVATIVE_PATH','');
class ThermalRedirectCaptured extends RuntimeException {public $status;public function __construct($target,$code){parent::__construct($target);$this->status=$code;}}
class Core {public function redirect($target,$code=302){throw new ThermalRedirectCaptured($target,$code);}}
class DB {public static $reads=0;public static function query_fetch_array($sql,...$args){self::$reads++;return null;}}
require ABSOLUTE_PATH.'includes/init.php';require_once ABSOLUTE_PATH.'custom/energo/includes/thermal_redirect.php';
$checks=0;$assert=function($ok,$label)use(&$checks){if(!$ok){throw new RuntimeException($label);}$checks++;};
$old='/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya/';$main='/services/teplovizionnoe-obsledovanie-elektrooborudovaniya/';
$init=(new ReflectionClass('Init'))->newInstanceWithoutConstructor();$method=new ReflectionMethod('Init','get_redirect');$method->setAccessible(true);
foreach(array('','?utm_source=yandex&yclid=abc','?q=%D1%82%D0%B5%D1%81%D1%82') as $query){
 putenv('REQUEST_URI='.$old.$query);$caught=false;try{$method->invoke($init);}catch(ThermalRedirectCaptured $e){$caught=true;$assert($e->status===301 && $e->getMessage()===BASE_PATH.ltrim($main,'/').$query,'direct server 301 and intact query');}$assert($caught,'legacy URL redirected');
}
$assert(DB::$reads===0,'redirect runs before native route or active flags lookup');
$assert(nwdThermalRedirectTarget($old,'https://nwdelectricity.ru/')==='https://nwdelectricity.ru'.$main,'canonical production HTTPS destination');
$assert(nwdThermalRedirectTarget($old,'http://www.nwdelectricity.ru/')==='https://nwdelectricity.ru'.$main,'WWW cannot introduce a redirect chain');
$assert(nwdThermalRedirectTarget($old,'https://untrusted.example/')==='https://nwdelectricity.ru'.$main,'request host cannot become an open redirect');
$assert(nwdThermalRedirectTarget(rtrim($old,'/'),BASE_PATH)===BASE_PATH.ltrim($main,'/'),'PHP hook accepts slashless legacy route');
foreach(array($main,$old.'child','/articles/example/','/redaktsiya/api/',$old."?x=\r\nBad") as $request){$assert(nwdThermalRedirectTarget($request,BASE_PATH)===null,'only exact legacy path is redirected');}
putenv('REQUEST_URI=/services/other/');$method->invoke($init);$assert(DB::$reads===1,'other requests retain existing native redirect lookup');
echo 'THERMAL_REDIRECT_OK='.$checks."\n";
