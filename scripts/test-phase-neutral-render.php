<?php
/** Exercises the actual DIAFAN parser/controller/model and the service view with isolated adapters. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
define('DIAFAN',true);define('ABSOLUTE_PATH',dirname(__DIR__).'/');
require ABSOLUTE_PATH.'includes/diafan.php';
require ABSOLUTE_PATH.'includes/controller.php';
require ABSOLUTE_PATH.'includes/parser_theme.php';
class Custom {
    public static function path($path) { return is_file(ABSOLUTE_PATH.'custom/energo/'.$path) ? 'custom/energo/'.$path : (is_file(ABSOLUTE_PATH.$path) ? $path : false); }
    public static function exists($path) { return (bool)self::path($path); }
    public static function inc($path) { require_once ABSOLUTE_PATH.self::path($path); }
}
class DB {
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
        if ($view!=='form' || $module!=='feedback' || $template!=='phase_neutral') { throw new RuntimeException('Unexpected native form view'); }
        ob_start();require ABSOLUTE_PATH.'custom/energo/modules/feedback/views/feedback.view.form_phase_neutral.php';return ob_get_clean();
    }
}
class NwdRenderContext {
    public $current_module='site';public $current_insert_tag;public $installed_modules=array('feedback');public $_tpl;public $_site;
    public function attributes(&$attributes,...$names) { foreach ($names as $name) { if (!isset($attributes[$name])) { $attributes[$name]=''; } } }
}
$context=new NwdRenderContext;$context->_tpl=new NwdRenderTemplate;$context->_site=(object)array('id'=>999);
$parser=new Parser_theme($context);
$html=$parser->get_function_in_theme('<insert name="show_phase_neutral_content" />');
$document=new DOMDocument;$previous=libxml_use_internal_errors(true);$document->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($previous);
$xpath=new DOMXPath($document);$checks=0;
function renderCheck($condition,$label) { global $checks;if (!$condition) { throw new RuntimeException($label); }$checks++; }
renderCheck($xpath->query('//insert')->length===0,'no unexpanded native tags reach the browser');
renderCheck($xpath->query('//form[@data-fn-form]')->length===1,'real parser expands the inline service form');
renderCheck($xpath->query('//input[@name="p_name"]')->length===1,'native honeypot is not duplicated');
renderCheck($xpath->query('//form[@data-fn-form]//input[@type="text" and @name="p109"]')->length===1,'name field uses native parameter ID');
renderCheck($xpath->query('//form[@data-fn-form]//input[@type="tel" and @name="p110" and @required]')->length===1,'required phone renders');
renderCheck($xpath->query('//form[@data-fn-form]//input[@type="email" and @name="p111"]')->length===1,'optional email renders');
renderCheck($xpath->query('//form[@data-fn-form]//textarea[@name="p112"]')->length===1,'object details render');
renderCheck($xpath->query('//form[@data-fn-form]//input[@name="nwd_consent" and @required and not(@checked)]')->length===1,'consent starts unchecked');
renderCheck($xpath->query('//h1')->length===1 && $xpath->query('//section')->length===6,'one H1 and six sections');
$order=array();foreach ($xpath->query('//section') as $node) { $order[]=$node->getAttribute('aria-labelledby'); }
renderCheck($order===array('fn-title','fn-checks-title','fn-when-title','fn-process-title','fn-cost-title','fn-faq-title'),'section order stays exact');
renderCheck($xpath->query('//details')->length===5,'five FAQs');
renderCheck($xpath->query('//a[starts-with(@href,"http")]')->length===0,'publication links stay internal');
renderCheck($context->current_module==='site','nested native form restores its module context');
if (in_array('--html',$argv,true)) { echo $html; } else { echo 'PHASE_NEUTRAL_RENDER_OK='.$checks."\n"; }
