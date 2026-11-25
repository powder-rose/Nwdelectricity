<?php
if (!defined('DIAFAN')) { http_response_code(404);exit; }
$nwd=require ABSOLUTE_PATH.Custom::path('themes/power_tools_content.php');
$e=function($v){return htmlspecialchars($v,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');};
$link=function($text,$anchor,$path)use($e){return str_replace($e($anchor),'<a href="'.$e($path).'">'.$e($anchor).'</a>',$e($text));};
$art=function($name,$class='')use($e){$view=$name==='report' ? '0 0 150 180' : (in_array($name,array('drill','hammer','grinder','saw'),true) ? '0 0 240 150' : '0 0 64 64');return '<svg class="'.$e($class).'" viewBox="'.$view.'" aria-hidden="true" focusable="false"><use href="/custom/energo/images/power-tools/details.svg#'.$e($name).'"></use></svg>';};
$price=str_replace($e('за единицу'),'<span class="pt-price-unit">'.$e('за единицу').'</span>',$e($nwd['price']));
?>
<div class="fn-breadcrumbs container"><nav aria-label="Хлебные крошки"><ol><li><a href="/">Главная</a></li><li><a href="/services/">Услуги</a></li><li><a href="/services/elektroizmeritelnaya-laboratoriya/">Электроизмерительная лаборатория</a></li><li aria-current="page"><?= $e($nwd['name']) ?></li></ol></nav></div>
<section class="fn-hero pt-hero" aria-labelledby="fn-title"><div class="container fn-hero__grid">
<div class="fn-hero__copy"><h1 id="fn-title"><?= str_replace($e('электроинструмента'),'<span>'.$e('электроинструмента').'</span>',$e($nwd['name'])) ?></h1>
<?php $intro=explode('. ',$nwd['intro'],2); ?>
<p class="fn-hero__intro"><?= $e($intro[0]) ?>.</p><p class="pt-hero__detail"><?= $e($intro[1]) ?></p>
<p class="fn-hero__registration"><?= $e($nwd['registration']) ?></p>
<div class="fn-hero__offer"><div class="fn-price"><p class="fn-price__number"><?= $price ?></p><p class="fn-price__included"><?= $e($nwd['included']) ?></p></div><a class="fn-button" href="#raschet-stoimosti" data-fn-price><?= $e($nwd['cta']) ?><span aria-hidden="true">↗</span></a></div></div>
<figure class="fn-hero__photo"><img src="/custom/energo/images/power-tools/ispytanie-perenosnogo-elektroinstrumenta.webp" width="1448" height="1086" alt="<?= $e($nwd['alt']) ?>" fetchpriority="high" decoding="async"></figure>
</div></section>
<section class="fn-section pt-scope container" aria-labelledby="fn-checks-title" data-fn-reveal>
<h2 id="fn-checks-title"><?= $e($nwd['scopeTitle']) ?></h2>
<div class="pt-scope__intro"><p class="pt-scope__range"><?php
$range=explode('В одну заявку',$nwd['scope'][0],2);
echo '<span class="pt-scope__lead">'.$e($range[0]).'</span><span class="pt-scope__caption">'.$e('В одну заявку'.explode('дрели,',$range[1],2)[0]).'</span>';
$tools=array(array('drill','дрели,'),array('hammer','перфораторы,'),array('grinder','угловые шлифовальные машины'),array('saw','и электрические пилы.'));
echo '<span class="pt-toolset">';foreach($tools as $tool){echo '<span class="pt-tool">'.$art($tool[0]).'<span>'.$e($tool[1]).'</span></span> ';}echo '</span>';
?></p><p class="pt-scope__request"><?= $art('record') ?> <span><?= $e($nwd['scope'][1]) ?></span></p></div>
<div class="pt-inspection"><header><h3><?= $e($nwd['checksTitle']) ?></h3><p><?= $e($nwd['checksLead']) ?></p></header><ul class="pt-inspection__list">
<?php
$emphasis=array('корпус, органы управления, сетевой кабель и вилку','работу инструмента на холостом ходу','сопротивление изоляции','цепи защитного заземления');
foreach($nwd['checks'] as $i=>$item){echo '<li><span>'.str_replace($e($emphasis[$i]),'<strong>'.$e($emphasis[$i]).'</strong>',$e($item)).'</span></li>';}
?>
</ul></div><p class="pt-scope__result"><span aria-hidden="true">↳</span> <span><?= str_replace($e('по каждой проверенной единице'),'<strong>'.$e('по каждой проверенной единице').'</strong>',$e($nwd['checksOutro'])) ?></span></p>
</section>
<section class="fn-section pt-period" aria-labelledby="fn-when-title" data-fn-reveal><div class="container">
<h2 id="fn-when-title"><?= $e($nwd['whenTitle']) ?></h2><div class="pt-period__grid">
<p class="pt-period__rule"><?php
$interval='не реже одного раза в 6 месяцев';$parts=explode($interval,$nwd['whenIntro'],2);
echo '<span class="pt-period__before">'.$e($parts[0]).'</span><strong class="pt-period__interval">'.$e('не реже одного раза в').' <span class="pt-period__duration"><span>6</span> '.$e('месяцев').'.</span></strong> <span class="pt-period__after">'.$e(ltrim(substr($parts[1],1))).'</span>';
?></p><div class="pt-period__situations"><p><?= $e($nwd['whenLead']) ?></p><ul><?php
$caseArt=array('calendar','repair','record');$caseLead=array('выполнить согласованные испытания партии','проверить отдельные единицы после ремонта,','получить документированные результаты');
foreach($nwd['when'] as $i=>$item){$tail=substr($item,strlen($caseLead[$i]));echo '<li>'.$art($caseArt[$i]).'<span><strong>'.$e($caseLead[$i]).'</strong> <span>'.$e(ltrim($tail)).'</span></span></li>';}
?></ul></div>
</div></div></section>
<section class="fn-section pt-process container" aria-labelledby="fn-process-title" data-fn-reveal>
<h2 id="fn-process-title"><?= $e($nwd['processTitle']) ?></h2><ol class="pt-process__steps"><?php foreach($nwd['steps'] as $item){$step=explode('. ',$item,2);echo '<li><h3>'.$e($step[0]).'.</h3><p>'.$e($step[1]).'</p></li>';} ?></ol>
<div class="pt-protocol"><?= $art('report','pt-protocol__art') ?><div class="pt-protocol__body"><h3><?= $e($nwd['reportTitle']) ?></h3><div class="pt-protocol__text"><p><?= $e($nwd['report'][0]) ?></p><p><?= str_replace($e('«Технический отчёт по электроизмерениям»'),'<a href="/services/tekhnicheskiy-otchet-po-elektroizmereniyam/">'.$e('«Технический отчёт по электроизмерениям»').'</a>',$link($nwd['report'][1],'электроизмерительная лаборатория ООО «Спецэнергоразвитие»','/services/elektroizmeritelnaya-laboratoriya/')) ?></p></div></div></div>
</section>
<section class="fn-section fn-cost pt-cost" aria-labelledby="fn-cost-title" data-fn-reveal><div class="container fn-cost__grid">
<div class="fn-cost__copy"><h2 id="fn-cost-title"><?= $e($nwd['costTitle']) ?></h2><div class="fn-price"><p class="fn-price__number"><?= $price ?></p><p class="fn-price__included"><?= $e($nwd['included']) ?></p></div><p><?= $e($nwd['costIntro']) ?></p><h3><?= $e($nwd['costListTitle']) ?></h3><ul class="fn-list"><?php foreach($nwd['costList'] as $item){echo '<li>'.$e($item).'</li>';} ?></ul><p><?= $e($nwd['costOutro']) ?></p></div>
<div class="fn-cost__form" id="raschet-stoimosti"><h3><?= $e($nwd['formTitle']) ?></h3><p><?= $e($nwd['formIntro']) ?></p>
<?php echo $this->get_function_in_theme('<insert name="show_form" module="feedback" site_id="27" template="power_tools" />'); ?>
</div></div></section>
<section class="fn-section fn-faq pt-faq container" aria-labelledby="fn-faq-title" data-fn-reveal><h2 id="fn-faq-title"><?= $e($nwd['faqTitle']) ?></h2><div class="fn-faq__items">
<?php foreach($nwd['faq'] as $pair){echo '<details><summary>'.$e($pair[0]).'</summary><p>'.$e($pair[1]).'</p></details>';} ?>
</div></section>
<?php
$crumbs=array('Главная'=>'/','Услуги'=>'/services/','Электроизмерительная лаборатория'=>'/services/elektroizmeritelnaya-laboratoriya/',$nwd['name']=>'/'.$nwd['path'].'/');$items=array();foreach($crumbs as $name=>$path){$items[]=array('@type'=>'ListItem','position'=>count($items)+1,'name'=>$name,'item'=>'https://nwdelectricity.ru'.$path);}
echo '<script type="application/ld+json">'.json_encode(array('@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$items),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP).'</script>';
