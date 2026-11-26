<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
define('DIAFAN',true);define('NWD_PANEL',true);define('_LANG',1);define('ABSOLUTE_PATH',dirname(__DIR__).'/');define('EMAIL_CONFIG','office@nwdelectricity.ru');putenv('NWD_LOCAL=1');
require ABSOLUTE_PATH.'redaktsiya/lib/db.php';require ABSOLUTE_PATH.'redaktsiya/lib/content.php';
require ABSOLUTE_PATH.'custom/energo/modules/feedback/lightning.php';
$checks=0;function check($ok,$name){global $checks;if(!$ok){throw new RuntimeException($name);} $checks++;}
class DB {
 static $article;static $receipt=null;static $feedback=array();static $outbox=array();static $values=array();static $writes=0;
 static function query_fetch_array($sql,...$args){
  if(strpos($sql,"SELECT s.id FROM {site}")!==false)return self::$article;
  if(strpos($sql,'SELECT digest,feedback_id')!==false)return self::$receipt;
  if(strpos($sql,'SELECT id,created,url')!==false)return self::$feedback;
  if(strpos($sql,'SELECT payload_hash')!==false)return self::$outbox[$args[0]] ?? null;
  throw new Exception('Unhandled SQL read');
 }
 static function query_result($sql,...$args){return 'Обратная связь';}
 static function query_fetch_all($sql,...$args){if(strpos($sql,'SELECT r.rewrite FROM {rewrite}')!==false){return self::$article ? array(array('rewrite'=>'services/proverka-sistem-molniezaschity')):array();}return self::$values;}
 static function query($sql,...$args){
  if(strpos($sql,'INSERT IGNORE INTO {nwd_feedback_request}')!==false){if(!self::$receipt)self::$receipt=array('digest'=>$args[1],'feedback_id'=>0,'mail_id'=>null);return 1;}
  if(strpos($sql,'INSERT INTO {feedback}')!==false){self::$feedback=array('id'=>77,'created'=>$args[0],'url'=>htmlspecialchars($args[3],ENT_QUOTES,'UTF-8'));self::$writes++;return 77;}
  if(strpos($sql,'UPDATE {nwd_feedback_request}')!==false){self::$receipt['feedback_id']=$args[0];self::$receipt['mail_id']=$args[1];return 1;}
  if(strpos($sql,'INSERT IGNORE INTO {boykov_lead_outbox}')!==false){self::$outbox[$args[0]]=array('payload_hash'=>$args[2],'payload'=>$args[1]);return 1;}
  if(in_array($sql,array('START TRANSACTION','COMMIT','ROLLBACK'),true))return 1;
  throw new Exception('Unhandled SQL write');
 }
}

DB::$article=array('id'=>100);
$params=array(array('id'=>8,'name'=>'Ваше имя','type'=>'text','required'=>true),array('id'=>19,'name'=>'Телефон','type'=>'phone','required'=>false),array('id'=>42,'name'=>'Почта','type'=>'email','required'=>true),array('id'=>67,'name'=>'Сообщение','type'=>'textarea','required'=>true));
$post=array('p8'=>'','p19'=>'+7 (999) 123-45-67','p42'=>'','p67'=>"Москва, 4 щита\nДоступ после 18:00",'nwd_attribution'=>'{}');
$p=$params;$v=$post;$errors=array();$context=nwdLightningValidate($p,$v,$errors);
check(!$errors && count($p)===4,'optional name/email/details');
check(array_column($p,'required')===array(false,true,false,false),'only phone required');
check($context['source']==='https://nwdelectricity.ru/services/proverka-sistem-molniezaschity/','canonical source comes from server');
foreach(array(array('p19',''),array('p19','123'),array('p19',array('oops')),array('p42','bad'),array('p8',array('oops')),array('p67',str_repeat('x',4001)),array('nwd_attribution','{bad'),array('nwd_attribution','{"utm_source":["bad"]}')) as $invalid){list($key,$value)=$invalid;$p=$params;$v=array_merge($post,array($key=>$value));$e=array();nwdLightningValidate($p,$v,$e);check((bool)$e,'invalid '.$key);}
DB::$article=null;$p=$params;$v=$post;$e=array();nwdLightningValidate($p,$v,$e);check((bool)$e,'unpublished service rejected');DB::$article=array('id'=>100);
$p=$params;$v=array_merge($post,array('nwd_attribution'=>'{"utm_source":"yandex","gclid":"example","evil":"ignored"}'));$e=array();nwdLightningValidate($p,$v,$e);
check(!$e && strpos($v['p67'],'utm_source: yandex')!==false && strpos($v['p67'],'evil')===false,'attribution allowlist in native message');
class Action {public $result=array();public $site_id=27;public $model;public $diafan;function result(){return !empty($this->result['errors']);}function check_site_id(){}function empty_required_field($v){}function insert_values($v){global $params;foreach($v['params'] as $p)DB::$values[]=array('label'=>$p['name'],'type'=>$p['type'],'value'=>$_POST['p'.$p['id']]);}}
class ModelStub {function get_params($v){global $params;return $params;}}
class MailStub {public $mails=array();function db_add(...$v){$this->mails[]=$v;return 'test-mail';}function message_send(){throw new Exception('External send forbidden');}}
class DiafanStub {public $_users;public $_captcha;public $_postman;function __construct(){$this->_users=(object)array('id'=>0);$this->_captcha=new class {function configmodules(...$v){return false;}};$this->_postman=new MailStub;}function filter($v,$type,$key){return $v[$key];}function configmodules(...$v){return false;}}
require ABSOLUTE_PATH.'custom/energo/modules/feedback/feedback.action.php';
function action($post){$a=new Feedback_action;$a->model=new ModelStub;$a->diafan=new DiafanStub;$_POST=$post;$a->add();return $a;}

$valid=$post+array('nwd_consent'=>'1','nwd_request_id'=>str_repeat('b',32),'add_c'=>'4l','nwd_service'=>'lightning');
$a=action(array_diff_key($valid,array('nwd_consent'=>1)));check(!empty($a->result['errors']['nwd_consent']) && DB::$writes===0,'missing consent writes nothing');
$a=action(array_merge($valid,array('p19'=>'123')));check($a->result['errors']['p19']==='Проверьте номер телефона.' && DB::$writes===0,'server phone error matches brief');
$a=action(array_merge($valid,array('nwd_service'=>'unknown')));check(!empty($a->result['errors']) && DB::$writes===0,'unknown service rejected');
$a=action(array_merge($valid,array('nwd_service'=>array('laboratory'))));check(!empty($a->result['errors']) && DB::$writes===0,'non-string service rejected');
$a=action($valid);check(($a->result['result'] ?? '')==='success' && DB::$writes===1,'request saved');
check($a->result['errors'][0]===nwdLightningCopy()['errors']['success'],'exact success copy');
check(count($a->diafan->_postman->mails)===1 && $a->diafan->_postman->mails[0][0]==='office@nwdelectricity.ru','notification queued to office');
$mail=$a->diafan->_postman->mails[0][2];check(strpos($mail,'Проверка систем молниезащиты')!==false && strpos($mail,'Объект и причина проверки')!==false && strpos($mail,'Москва, 4 щита')!==false,'HTML email contains service and visitor data');
$hub=json_decode(array_values(DB::$outbox)[0]['payload'],true);check($hub['form']==='Проверка систем молниезащиты' && $hub['pageUrl']==='https://nwdelectricity.ru/services/proverka-sistem-molniezaschity/' && strpos($hub['message'],'Москва, 4 щита')!==false,'hub includes service and URL');
$a=action($valid);check(($a->result['result'] ?? '')==='success' && DB::$writes===1 && !$a->diafan->_postman->mails,'retry does not duplicate lead or mail');
$log=tempnam(sys_get_temp_dir(),'nwd-phase-test-');$previousLog=ini_set('error_log',$log);
try { $a=action(array_merge($valid,array('p67'=>'Changed request'))); }
finally { ini_set('error_log',$previousLog);unlink($log); }
check(!empty($a->result['errors']) && DB::$writes===1,'changed replay rejected');
echo 'LIGHTNING_BEHAVIOR_OK='.$checks."\n";
