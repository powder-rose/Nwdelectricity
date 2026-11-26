<?php
/** Public article output changes without editing payload, revision or API state. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('DIAFAN',true);define('ABSOLUTE_PATH',dirname(__DIR__).'/');
require ABSOLUTE_PATH.'custom/energo/themes/author_blog.php';
$before='<h2>Обследование</h2><p>Точный авторский текст.</p><p><a href="/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya/">Тепловизионный контроль</a></p>';
$copy=$before;$result=NwdAuthorBlog::body($before);
if($before!==$copy || strpos($result['html'],'href="/services/teplovizionnoe-obsledovanie-elektrooborudovaniya/"')===false || strpos($result['html'],'Точный авторский текст.')===false || count($result['toc'])!==1){throw new RuntimeException('Public article link rewrite must preserve editable source and native TOC');}
echo "THERMAL_ARTICLE_LINKS_OK\n";
