<?php
/** Exercises the actual DIAFAN parser/controller/model and the service view with isolated adapters. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
define('DIAFAN',true);define('_LANG',1);define('ABSOLUTE_PATH',dirname(__DIR__).'/');
require_once __DIR__.'/relay-html.php';
require ABSOLUTE_PATH.'includes/diafan.php';
require ABSOLUTE_PATH.'includes/controller.php';
require ABSOLUTE_PATH.'includes/parser_theme.php';
class Custom {
    public static function path($path) { return is_file(ABSOLUTE_PATH.'custom/energo/'.$path) ? 'custom/energo/'.$path : (is_file(ABSOLUTE_PATH.$path) ? $path : false); }
    public static function exists($path) { return (bool)self::path($path); }
    public static function inc($path) { require_once ABSOLUTE_PATH.self::path($path); }
}
class DB {
    public static $useful=false;
    public static function query_fetch_all($sql,...$args) {
        $paths=array_filter($args,function($path){return self::$useful || strpos($path,'articles/')!==0;});
        return array_map(function($path){return array('rewrite'=>$path);},array_values($paths));
    }
    public static function query_fetch_value($sql,...$args) {
        if (strpos($sql,'{inserts_site_rel}')!==false) { return array(); }
        throw new RuntimeException('Unexpected insert override read');
    }
    public static function query_fetch_array($sql,...$args) {
        if (strpos($sql,'SELECT access, [name] FROM {site}')!==false && $args[0]===27) { return array('access'=>'0','name'=>'Обратная связь'); }
        throw new RuntimeException('Unexpected database read in rendering test');
    }
}
class Model extends Diafan {
    public function get_params($config) {
        return array(array('id'=>109,'type'=>'text','name'=>'Ваше имя','text'=>''),array('id'=>110,'type'=>'phone','name'=>'Телефон','text'=>''),array('id'=>111,'type'=>'email','name'=>'Почта','text'=>''),array('id'=>112,'type'=>'textarea','name'=>'Сообщение','text'=>''));
    }
    public function form_errors(&$result,$tag,$fields) {}
}
class NwdRenderTemplate {
    public function htmleditor($value) { return $value; }
    public function get($view,$module,$result,$template) {
        if ($view!=='form' || $module!=='feedback' || !in_array($template,array('relay','popup'),true)) { throw new RuntimeException('Unexpected native form view'); }
        if($template==='popup'){$result['error']='';foreach($result['rows'] as &$row){$row['required']=$row['required']??false;$result['error_p'.$row['id']]='';}unset($row);}
        ob_start();require ABSOLUTE_PATH.'custom/energo/modules/feedback/views/feedback.view.form_'.$template.'.php';return ob_get_clean();
    }
}
class NwdRenderContext {
    public $current_module='site';public $current_insert_tag;public $installed_modules=array('feedback');public $_tpl;public $_site;
    public function configmodules(...$args) { return true; }
    public function attributes(&$attributes,...$names) { foreach ($names as $name) { if (!isset($attributes[$name])) { $attributes[$name]=''; } } }
}
$context=new NwdRenderContext;$context->_tpl=new NwdRenderTemplate;$context->_site=(object)array('id'=>999);
$parser=new Parser_theme($context);
$html=$parser->get_function_in_theme('<insert name="show_relay_content" />');
$doc=new DOMDocument;$prev=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prev);$x=new DOMXPath($doc);
$copy=require ABSOLUTE_PATH.'custom/energo/themes/relay_content.php';$checks=0;
function relayRenderCheck($ok,$label){global $checks;if(!$ok){throw new RuntimeException($label);}$checks++;}
relayRenderCheck($x->query('//h1')->length===1 && nwdRelayHeadingText($x->query('//h1')->item(0))===$copy['name'],'one exact H1');
relayRenderCheck($x->query('//section')->length===6 && $x->query('//h2')->length===5,'six ordered sections');
$order=array();foreach($x->query('//section') as $node){$order[]=$node->getAttribute('aria-labelledby');}
relayRenderCheck($order===array('relay-title','relay-scope-title','relay-when-title','relay-process-title','relay-cost-title','relay-faq-title'),'brief section order');
relayRenderCheck($x->query('//form[@data-fn-form]')->length===1,'native form expanded');
relayRenderCheck($x->query('//input[@name="nwd_service" and @value="relay"]')->length===1,'scoped native service');
relayRenderCheck($x->query('//input[@name="p_name"]')->length===1,'one native trap');
relayRenderCheck($x->query('//input[@type="tel" and @required]')->length===1 && $x->query('//input[@type="email" and not(@required)]')->length===1,'only phone required');
relayRenderCheck($x->query('//input[@name="nwd_consent" and @required and not(@checked)]')->length===1,'unchecked consent');
relayRenderCheck($x->query('//li')->length>=12 && $x->query('//details')->length===5,'all groups and FAQs');
relayRenderCheck($x->query('//a[starts-with(@href,"http")]')->length===0,'internal publication links only');
relayRenderCheck($x->query('//a[@href="#raschet-stoimosti"]')->length===1 && $x->query('//*[@id="raschet-stoimosti"]')->length===1,'hero anchor has one destination');
relayRenderCheck($x->query('//p[@class="relay-price"]')->length===2 && $x->query('//p[@class="relay-included"]')->length===2,'price and included work occur twice');
relayRenderCheck($x->query('//img[@fetchpriority="high"]')->length===1,'hero image eager');
$schema=json_decode($x->query('//script[@type="application/ld+json"]')->item(0)->textContent,true);
relayRenderCheck(count($schema['@graph'][2]['itemListElement'])===4 && $schema['@graph'][1]['offers']['price']==='9900','four crumbs and offer');
if(in_array('--html',$argv,true)){echo $html;}else{echo 'RELAY_RENDER_OK='.$checks."\n";}
