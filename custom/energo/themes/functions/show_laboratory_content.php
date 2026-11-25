<?php
if (!defined('DIAFAN')) { http_response_code(404);exit; }
$nwd=require ABSOLUTE_PATH.Custom::path('themes/laboratory_content.php');
require_once ABSOLUTE_PATH.Custom::path('themes/laboratory_links.php');
$paths=array();foreach($nwd['groups'] as $g){foreach($g['links'] as $l){$paths[]=$l[1];}}foreach($nwd['useful'] as $l){$paths[]=$l[1];}
$active=nwdLaboratoryActivePaths(array_unique($paths));
$e=function($v){return htmlspecialchars($v,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');};
$link=function($text,$anchor,$path)use($e,$active){return str_replace($e($anchor),isset($active[$path])?'<a href="'.$e($path).'">'.$e($anchor).'</a>':$e($anchor),$e($text));};
$heading=function($v)use($e){return str_replace(array('Электроизмер','электроизмер'),array('Электро<wbr>измер','электро<wbr>измер'),$e($v));};
$pdf='/custom/energo/documents/svidetelstvo-elektrolaboratorii-specenergorazvitie-10198.pdf';
?>
<section class="etl-hero" aria-labelledby="etl-title"><div class="container etl-hero__grid">
<div class="etl-hero__copy"><h1 id="etl-title"><?= str_replace('лаборатория','<span>лаборатория</span>',$heading($nwd['name'])) ?></h1>
<p class="etl-hero__intro"><?php $intro=explode('. ',$nwd['intro'],2); ?><span class="etl-hero__lead"><?= $e($intro[0]) ?>.</span> <?= $e($intro[1]) ?></p>
<p class="etl-geography"><span aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" focusable="false"><path d="M4 16 16 4M4 4h12v12"/></svg></span><?= $e($nwd['geography']) ?></p>
<div class="etl-hero__offer"><div><p class="etl-price-label"><?= $e($nwd['priceLabel']) ?></p><p class="etl-price"><?= $e($nwd['price']) ?></p></div>
<p class="etl-registration"><?= $e($nwd['registration']) ?></p></div>
<a class="fn-button" href="#raschet-stoimosti" data-fn-price><?= $e($nwd['cta']) ?><span aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" focusable="false"><path d="M4 16 16 4M4 4h12v12"/></svg></span></a></div>
<figure class="etl-hero__photo"><img src="/custom/energo/images/laboratory/elektroizmeritelnaya-laboratoriya-specenergorazvitie.webp" width="1448" height="1086" alt="<?= $e($nwd['alt']) ?>" fetchpriority="high" decoding="async"></figure>
</div></section>
<section class="etl-section etl-catalog container" aria-labelledby="etl-catalog-title"><h2 id="etl-catalog-title"><?= str_replace('лаборатории','<span class="etl-title-accent">лаборатории</span>',$heading($nwd['catalogTitle'])) ?></h2><p class="etl-section-intro"><?= $e($nwd['catalogIntro']) ?></p>
<div class="etl-catalog__columns"><?php foreach(array(array(0,1),array(2,3)) as $column){echo '<div class="etl-catalog__column">';foreach($column as $i){$g=$nwd['groups'][$i];echo '<div class="etl-catalog__group"><div class="etl-catalog__group-head"><h3>'.$e($g['title']).'</h3></div><ul>';foreach($g['links'] as $item){echo '<li>'.(isset($active[$item[1]])?'<a href="'.$e($item[1]).'">'.$e($item[0]).'</a>':'<span>'.$e($item[0]).'</span>').'</li>';}echo '</ul></div>';}echo '</div>';} ?></div>
<p class="etl-catalog__outro"><?= $e($nwd['catalogOutro']) ?></p></section>
<section class="etl-section etl-when" aria-labelledby="etl-when-title"><div class="container">
<div class="etl-when__head"><h2 id="etl-when-title"><?= str_replace('необходимы','<span class="etl-when__highlight">необходимы</span>',$heading($nwd['whenTitle'])) ?></h2><p class="etl-when__intro"><?php $whenIntro=explode('. ',$nwd['whenIntro'],2); ?><span><?= $e($whenIntro[0]) ?>.</span> <span><?= $e($whenIntro[1]) ?></span></p></div>
<div class="etl-when__cases"><p><?= $e($nwd['whenLead']) ?></p><ul><?php foreach($nwd['whenItems'] as $i=>$item){$text=$i===0?$link($item,'приёмо-сдаточные испытания','/services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya/'):$e($item);$parts=explode('. ',$text,2);echo '<li><strong>'.$parts[0].'.</strong> <span>'.($parts[1]??'').'</span></li>';} ?></ul></div>
<p class="etl-when__outro"><?= $e($nwd['whenOutro']) ?></p><p class="etl-legal"><?= $e($nwd['legal']) ?></p></div></section>
<section class="etl-section etl-certificate container" aria-labelledby="etl-certificate-title"><div class="etl-certificate__copy"><h2 id="etl-certificate-title"><?= $heading($nwd['certificateTitle']) ?></h2>
<?php foreach($nwd['certificate'] as $i=>$paragraph){echo '<p>'.($i===1?str_replace('28 видов','<strong>28 видов</strong>',$e($paragraph)):$e($paragraph)).'</p>';} ?>
<a class="etl-document-link" href="<?= $pdf ?>" target="_blank" rel="noopener"><?= $e($nwd['certificateLink']) ?><span aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" focusable="false"><path d="M4 16 16 4M4 4h12v12"/></svg></span></a></div>
<a class="etl-certificate__preview" href="<?= $pdf ?>" target="_blank" rel="noopener"><img src="/custom/energo/images/laboratory/svidetelstvo-10198.webp" width="720" height="1018" loading="lazy" decoding="async" alt="<?= $e($nwd['certificateAlt']) ?>"><span aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" focusable="false"><path d="M4 16 16 4M4 4h12v12"/></svg></span></a></section>
<section class="etl-section etl-process" aria-labelledby="etl-process-title"><div class="container"><h2 id="etl-process-title"><?= $heading($nwd['processTitle']) ?></h2><p class="etl-section-intro"><?= $e($nwd['processIntro']) ?></p>
<ol class="etl-steps"><?php foreach($nwd['steps'] as $i=>$step){echo '<li><div class="etl-step-top"><span class="etl-step-number" aria-hidden="true">0'.($i+1).'</span><svg class="etl-step-art" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 180 112" aria-hidden="true" focusable="false"><use href="/custom/energo/images/laboratory/workflow.svg#step-'.($i+1).'"></use></svg></div><h3>'.$e($step[0]).'</h3><p>'.$e($step[1]).'</p></li>';} ?></ol>
<div class="etl-report"><h3><?= $e($nwd['reportTitle']) ?></h3><div><?php foreach($nwd['report'] as $p){echo '<p>'.$e($p).'</p>';} ?><p><?= $link($nwd['reportLink'],$nwd['reportLink'],'/services/tekhnicheskiy-otchet-po-elektroizmereniyam/') ?></p></div></div></div></section>
<section class="etl-section etl-cost" aria-labelledby="etl-cost-title"><div class="container etl-cost__grid"><div class="etl-cost__copy"><h2 id="etl-cost-title"><?= $heading($nwd['costTitle']) ?></h2><div class="etl-cost__price"><p class="etl-price"><?= $e($nwd['price']) ?></p><p><?= $e($nwd['included']) ?></p></div><p><?= $e($nwd['costIntro']) ?></p><h3><?= $heading($nwd['factorsTitle']) ?></h3>
<ul class="etl-factors"><?php $icons=array('switchboard','tester','building','access','location','report');foreach($nwd['factors'] as $i=>$factor){echo '<li><svg viewBox="0 0 96 72" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><use href="/custom/energo/images/laboratory/details.svg#'.$icons[$i].'"></use></svg><span>'.$e($factor).'</span></li>';} ?></ul><p class="etl-cost__outro"><?= $e($nwd['costOutro']) ?></p></div>
<div class="fn-cost__form" id="raschet-stoimosti"><h3><?= $heading($nwd['formTitle']) ?></h3><p><?= $e($nwd['formIntro']) ?></p><?= $this->get_function_in_theme('<insert name="show_form" module="feedback" site_id="27" template="laboratory" />') ?></div></div></section>
<section class="etl-section etl-faq container" aria-labelledby="etl-faq-title"><h2 id="etl-faq-title"><?= $heading($nwd['faqTitle']) ?></h2><div class="etl-faq__items"><?php foreach($nwd['faq'] as $i=>$item){echo '<details><summary><span>'.$e($item[0]).'</span></summary><p>'.($i===2?$link($item[1],'технический отчёт','/services/tekhnicheskiy-otchet-po-elektroizmereniyam/'):$e($item[1])).'</p></details>';} ?></div>
<?php $ready=true;foreach($nwd['useful'] as $item){if(!isset($active[$item[1]])){$ready=false;}}if($ready){echo '<div class="etl-useful"><h3>'.$e($nwd['usefulTitle']).'</h3><ul>';foreach($nwd['useful'] as $item){echo '<li><a href="'.$e($item[1]).'">'.$e($item[0]).'</a></li>';}echo '</ul></div>';} ?></section>
<?php
$url='https://nwdelectricity.ru/'.$nwd['path'].'/';
$schema=array('@context'=>'https://schema.org','@graph'=>array(
array('@type'=>'Organization','@id'=>'https://nwdelectricity.ru/#organization','name'=>'ООО «Спецэнергоразвитие»','url'=>'https://nwdelectricity.ru/','telephone'=>'+78002012069','email'=>'office@nwdelectricity.ru'),
array('@type'=>'Service','name'=>$nwd['name'],'url'=>$url,'description'=>$nwd['intro'],'provider'=>array('@id'=>'https://nwdelectricity.ru/#organization'),'areaServed'=>array('@type'=>'Country','name'=>'Россия')),
array('@type'=>'BreadcrumbList','itemListElement'=>array(array('@type'=>'ListItem','position'=>1,'name'=>'Главная','item'=>'https://nwdelectricity.ru/'),array('@type'=>'ListItem','position'=>2,'name'=>'Услуги','item'=>'https://nwdelectricity.ru/services/'),array('@type'=>'ListItem','position'=>3,'name'=>$nwd['name'],'item'=>$url)))));
echo '<script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script>';
