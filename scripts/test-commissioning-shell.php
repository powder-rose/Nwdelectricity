<?php
/** Full DIAFAN document: real parser/shared blocks/form, isolated CMS data only. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
ob_start();require __DIR__.'/test-commissioning-render.php';ob_end_clean();
chdir(ABSOLUTE_PATH);
if(!defined('BASE_PATH')){define('BASE_PATH','/');}
define('IS_MOBILE',in_array('--mobile',$argv,true));
DB::$useful=false;
$copy=require ABSOLUTE_PATH.'custom/energo/themes/commissioning_content.php';
$context->_site=(object)array('id'=>42,'rewrite'=>$copy['path'],'theme'=>'commissioning.php','js_view'=>array());
$context->_route=new class { public function link($id){return 'services/';} };
$context->_users=(object)array('useradmin'=>false);
class NwdLaboratoryShellParser extends Parser_theme {
    public $includes=array();
    public function start_element($attributes){
        if(($attributes['module']??'')==='site' && trim($attributes['name'])==='show_block'){
            $values=array('1'=>'8 800 201-20-69','2'=>'office@nwdelectricity.ru','3'=>'Москва');echo $values[$attributes['id']]??'';return;
        }
        if(($attributes['module']??'')==='menu'){
            if(($attributes['template']??'')==='header'){
                echo '<ul class="main-menu main-menu__page-header"><li class="main-menu__item"><a class="main-menu__link" href="/services/">Услуги</a></li><li class="main-menu__item"><a class="main-menu__link" href="/o-nas/">О нас</a></li><li class="main-menu__item"><a class="main-menu__link" href="/ceny/">Цены</a></li><li class="main-menu__item"><a class="main-menu__link" href="/kontakty/">Контакты</a></li><li class="main-menu__item"><a class="main-menu__link" href="/articles/">Статьи</a></li></ul>';
            }
            return;
        }
        parent::start_element($attributes);
    }
    public function functions($name,$attributes=array()){
        if($name==='show_include'){
            $file=$attributes['file'];$this->includes[$file]=($this->includes[$file]??0)+1;
        }
        if($name==='show_head'){
            $copy=require ABSOLUTE_PATH.'custom/energo/themes/commissioning_content.php';
            echo '<meta charset="utf-8"><title>'.htmlspecialchars($copy['seoTitle'],ENT_QUOTES,'UTF-8').'</title><meta name="description" content="'.htmlspecialchars($copy['description'],ENT_QUOTES,'UTF-8').'"><link rel="canonical" href="https://nwdelectricity.ru/'.$copy['path'].'/">';return;
        }
        if($name==='show_css'){echo '<link rel="stylesheet" href="/custom/energo/css/style.css">';return;}
        if($name==='show_js'){
            echo '<script src="/js/jquery-2.0.3.min.js"></script><script src="/js/jquery.form.min.js"></script><script src="/custom/energo/js/site.js"></script>';
            foreach(array_unique($this->diafan->_site->js_view) as $src){echo '<script src="'.htmlspecialchars($src,ENT_QUOTES,'UTF-8').'"></script>';}
            return;
        }
        if($name==='path'){echo BASE_PATH;return;}
        if($name==='show_breadcrumb'){
            echo '<div class="container"><ul class="breadcrumbs"><li class="breadcrumbs__item"><a href="/">Главная</a></li><li class="breadcrumbs__item"><a href="/services/">Услуги</a></li><li class="breadcrumbs__item">Электроизмерительная лаборатория</li></ul></div>';return;
        }
        parent::functions($name,$attributes);
    }
}
// Native production diagnostics are not silenced by the repair. Fail on notices/warnings here.
set_error_handler(function($no,$message,$file,$line){throw new RuntimeException($message.' at '.basename($file).':'.$line);},E_ALL & ~E_DEPRECATED);
$native=new NwdLaboratoryShellParser($context);ob_start();$native->show_theme();$shell=ob_get_clean();restore_error_handler();
$verify=function($ok,$label){if(!$ok){throw new RuntimeException($label);}};
foreach(array('html','head','body','main') as $tag){$verify(preg_match_all('/<'.$tag.'\b/i',$shell)===1,'one complete '.$tag.' element');}
foreach(array('head','header','footer','scripts','modal') as $file){$verify(($native->includes[$file]??0)===1,'shared '.$file.' included once');}
$verify(strpos($shell,'[ERROR#')===false && strpos($shell,'<?')===false,'no CMS diagnostic or raw PHP in shell');
$verify(preg_match('~commissioning\.js\?v=\d+~',$shell)===1,'cache key is evaluated by the native parser');
$doc=new DOMDocument;$prev=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$shell,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prev);$x=new DOMXPath($doc);
$verify($x->query('//body[@style]')->length===0,'body does not inherit an inline diagnostic color');
$verify($x->query('//header[contains(@class,"page-header")]')->length===1 && $x->query('//footer[contains(@class,"page-footer")]')->length===1,'one real shared header and footer');
$verify($x->query('//main//section')->length===6 && $x->query('//h1')->length===1 && $x->query('//main//h2')->length===5,'full shell preserves the exact six sections');
$verify($x->query('//main//form[@data-fn-form]')->length===1,'native form remains in the full shell');
$verify($x->query('//nav[@aria-label="Хлебные крошки"]//a')->length===3 && $x->query('//nav//span[@aria-current="page"]')->length===1,'breadcrumbs have three internal links and a non-linked current page');
$ids=array();foreach($x->query('//*[@id]') as $el){$id=$el->getAttribute('id');$verify(!isset($ids[$id]),'duplicate DOM ID: '.$id);$ids[$id]=true;}
$sources=array();foreach($x->query('//script[@src]') as $el){$src=$el->getAttribute('src');$verify(!isset($sources[$src]),'duplicate script: '.$src);$sources[$src]=true;}
$verify($x->query('//a[contains(concat(" ",normalize-space(@class)," ")," home-callback ")]')->length===1 && $x->query('//*[@id="modal"]//form')->length===1,'one fixed callback and existing modal form');
$verify(strpos($context->_site->theme,IS_MOBILE?'m/commissioning.php':'commissioning.php')===0,'native desktop/mobile selector uses the commissioning shell');
if(in_array('--html',$argv,true)){echo $shell;}else{echo 'COMMISSIONING_SHELL_OK='.(IS_MOBILE?'mobile':'desktop')."\n";}
