<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
define('DIAFAN',true);define('NWD_PANEL',true);define('_LANG',1);define('ABSOLUTE_PATH',dirname(__DIR__).'/');define('EMAIL_CONFIG','office@nwdelectricity.ru');putenv('NWD_LOCAL=1');
require ABSOLUTE_PATH.'redaktsiya/lib/db.php';require ABSOLUTE_PATH.'redaktsiya/lib/content.php';
require ABSOLUTE_PATH.'custom/energo/modules/feedback/document-request.php';
$checks=0;function check($ok,$name){global $checks;if(!$ok){throw new RuntimeException($name);} $checks++;}
$html='<p>Вводный текст статьи</p><section data-nwd-document="1" data-title="Инструкция &quot;Электрик&quot;"><p>УТВЕРЖДАЮ</p><p>Текст документа № 1</p><ol><li>Первый пункт</li><li>Второй пункт</li></ol></section><section data-nwd-document="1" data-title="Образец приказа"><p>Другой документ</p></section>';
$guest=panelRenderDocuments($html);$admin=panelRenderDocuments($html,true);
check(strpos($guest,'data-document-access="visitor"')!==false && substr_count($guest,'data-document-request')===2,'guest requests');
check(strpos($guest,'data-document-copy hidden')!==false,'guest copy hidden');
check(strpos($admin,'data-document-access="editor"')!==false && strpos($admin,'data-document-copy hidden')===false,'preview editor permission');
$images=array();$clean='';foreach(panelUtf8Document($guest)->getElementsByTagName('body')->item(0)->childNodes as $node){$clean.=panelCleanNode($node,$images);}
check(strpos($clean,'Запросить документ')===false && strpos($clean,'Текст документа № 1')!==false,'clean preserves only document content');
class DB {
 static $article;static $receipt=null;static $feedback=array();static $outbox=array();static $values=array();static $writes=0;
 static function query_fetch_array($sql,...$args){
  if(strpos($sql,'a.slug,a.payload')!==false)return self::$article;
  if(strpos($sql,'SELECT digest,feedback_id')!==false)return self::$receipt;
  if(strpos($sql,'SELECT id,created,url')!==false)return self::$feedback;
  if(strpos($sql,'SELECT payload_hash')!==false)return self::$outbox[$args[0]] ?? null;
  throw new Exception('Unhandled SQL read');
 }
 static function query_result($sql,...$args){return 'Обратная связь';}
 static function query_fetch_all($sql,...$args){return self::$values;}
 static function query($sql,...$args){
  if(strpos($sql,'INSERT IGNORE INTO {nwd_feedback_request}')!==false){if(!self::$receipt)self::$receipt=array('digest'=>$args[1],'feedback_id'=>0,'mail_id'=>null);return 1;}
  if(strpos($sql,'INSERT INTO {feedback}')!==false){self::$feedback=array('id'=>77,'created'=>$args[0],'url'=>htmlspecialchars($args[3],ENT_QUOTES,'UTF-8'));self::$writes++;return 77;}
  if(strpos($sql,'UPDATE {nwd_feedback_request}')!==false){self::$receipt['feedback_id']=$args[0];self::$receipt['mail_id']=$args[1];return 1;}
  if(strpos($sql,'INSERT IGNORE INTO {boykov_lead_outbox}')!==false){self::$outbox[$args[0]]=array('payload_hash'=>$args[2],'payload'=>$args[1]);return 1;}
  if(in_array($sql,array('START TRANSACTION','COMMIT','ROLLBACK'),true))return 1;
  throw new Exception('Unhandled SQL write');
 }
}
DB::$article=array('slug'=>'instruktsiya','payload'=>json_encode(array('content'=>$html),JSON_UNESCAPED_UNICODE));
$params=array(array('id'=>8,'name'=>'Ваше имя','type'=>'text','required'=>false),array('id'=>19,'name'=>'Телефон','type'=>'phone','required'=>false),array('id'=>42,'name'=>'Почта','type'=>'email','required'=>false),array('id'=>67,'name'=>'Сообщение','type'=>'textarea','required'=>false));
$post=array('p8'=>'Тестовый клиент','p19'=>'+7 (999) 123-45-67','p42'=>'test@example.invalid','nwd_document_article'=>'123','nwd_document_index'=>'1');
$p=$params;$errors=array();$context=nwdDocumentRequestValidate($p,$post,$errors);
check(!$errors && count($p)===3,'valid required fields independent of legacy required config');
$meta=nwdDocumentRequestContext($context['source']);check($meta['title']==='Инструкция "Электрик"' && $meta['index']===1,'server derives exact document title');
foreach(array(array('p8',''),array('p19','123'),array('p42','not-an-email'),array('nwd_document_index','99'),array('nwd_document_article',array('123')),array('p8',array('malformed'))) as $invalid){list($key,$value)=$invalid;$p=$params;$e=array();nwdDocumentRequestValidate($p,array_merge($post,array($key=>$value)),$e);check((bool)$e,'invalid '.$key);}
DB::$article=null;$p=$params;$e=array();nwdDocumentRequestValidate($p,$post,$e);check((bool)$e,'unpublished or missing article rejected');DB::$article=array('slug'=>'instruktsiya','payload'=>json_encode(array('content'=>$html)));
check(nwdDocumentRequestContext('https://evil.invalid/articles/a/#nwd-document=1&title=test')===null,'foreign source rejected');
class Action {public $result=array();public $site_id=27;public $model;public $diafan;function result(){return !empty($this->result['errors']);}function check_site_id(){}function empty_required_field($v){}function insert_values($v){global $params;foreach($v['params'] as $p)DB::$values[]=array('label'=>$p['name'],'type'=>$p['type'],'value'=>$_POST['p'.$p['id']]);}}
class ModelStub {function get_params($v){global $params;return $params;}}
class MailStub {public $mails=array();function db_add(...$v){$this->mails[]=$v;return 'test-mail';}function message_send(){throw new Exception('External send forbidden');}}
class DiafanStub {public $_users;public $_captcha;public $_postman;function __construct(){$this->_users=(object)array('id'=>0);$this->_captcha=new class {function configmodules(...$v){return false;}};$this->_postman=new MailStub;}function filter($v,$type,$key){return $v[$key];}function configmodules(...$v){return false;}}
require ABSOLUTE_PATH.'custom/energo/modules/feedback/feedback.action.php';
function action($post){$a=new Feedback_action;$a->model=new ModelStub;$a->diafan=new DiafanStub;$_POST=$post;$a->add();return $a;}
$valid=$post+array('nwd_consent'=>'1','nwd_request_id'=>str_repeat('a',32),'add_c'=>'4l','nwd_document_request'=>'1');
$a=action(array_diff_key($valid,array('nwd_consent'=>1)));check(!empty($a->result['errors']['nwd_consent']) && DB::$writes===0,'missing consent writes nothing');
$a=action($valid);check(($a->result['result'] ?? '')==='success' && DB::$writes===1,'request saved');
check(count($a->diafan->_postman->mails)===1 && $a->diafan->_postman->mails[0][0]==='office@nwdelectricity.ru','notification queued to office');
$mail=$a->diafan->_postman->mails[0][2];check(strpos($mail,'Запрос документа')!==false && strpos($mail,'Инструкция &quot;Электрик&quot;')!==false,'HTML email has document name');
$hub=json_decode(array_values(DB::$outbox)[0]['payload'],true);check($hub['form']==='Запрос документа' && end($hub['fields'])['label']==='Номер заявки на сайте','hub remains queued');
$a=action($valid);check(($a->result['result'] ?? '')==='success' && DB::$writes===1 && !$a->diafan->_postman->mails,'retry does not duplicate lead or mail');
// The rejected replay intentionally logs a server error; keep that expected log out of test output.
$log=tempnam(sys_get_temp_dir(),'nwd-document-test-');$previousLog=ini_set('error_log',$log);
try { $a=action(array_merge($valid,array('nwd_document_index'=>'2'))); }
finally { ini_set('error_log',$previousLog);unlink($log); }
check(!empty($a->result['errors']) && DB::$writes===1,'same request id with changed document rejected');
echo 'DOCUMENT_BEHAVIOR_OK='.$checks."\n";
