<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
$nwd = require ABSOLUTE_PATH.Custom::path('themes/rcd_content.php');
$e = function ($v) { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$link = function ($text, $anchor, $path) use ($e) { return str_replace($e($anchor), '<a href="'.$e($path).'">'.$e($anchor).'</a>', $e($text)); };
$mark = function ($text, $part) use ($e) { return str_replace($e($part),'<span class="fn-underline">'.$e($part).'</span>',$e($text)); };
$list = function ($items) use ($e) {
    echo '<ul class="fn-list">';
    foreach ($items as $item) {
        echo '<li>'.$e($item).'</li>';
    }
    echo '</ul>';
};
?>
<div class="fn-breadcrumbs container"><nav aria-label="Хлебные крошки"><ol>
<li><a href="/">Главная</a></li><li><a href="/services/">Услуги</a></li><li><a href="/services/elektroizmeritelnaya-laboratoriya/">Электроизмерительная лаборатория</a></li><li aria-current="page"><?= $e($nwd['name']) ?></li>
</ol></nav></div>
<section class="fn-hero" aria-labelledby="fn-title"><div class="container fn-hero__grid">
<div class="fn-hero__copy"><h1 id="fn-title">Проверка <span>УЗО</span></h1>
<?php $intro=explode('. ',$nwd['intro'],2); ?>
<p class="fn-hero__intro"><?= $e($intro[0]) ?>.</p>
<ul class="fn-hero__results"><li><?= str_replace($e('технический отчёт'),'<strong>'.$e('технический отчёт').'</strong>',$e($intro[1])) ?></li></ul>
<div class="fn-hero__credentials"><p class="fn-hero__geography"><?= str_replace($e('по всей России'),'<strong>'.$e('по всей России').'</strong>',$e($nwd['geography'])) ?></p>
<p class="fn-hero__registration"><?= $e($nwd['registration']) ?></p></div>
<div class="fn-hero__offer">
<div class="fn-price"><p class="fn-price__number"><?= $e($nwd['price']) ?></p><p class="fn-price__included"><?= $e($nwd['included']) ?></p></div>
<a class="fn-button" href="#raschet-stoimosti" data-fn-price><?= $e($nwd['cta']) ?><span aria-hidden="true">↗</span></a></div></div>
<figure class="fn-hero__photo"><img src="/custom/energo/images/rcd/proverka-uzo-elektrolaboratoriya.webp" width="1448" height="1086" alt="<?= $e($nwd['alt']) ?>" fetchpriority="high" decoding="async"></figure>
</div></section>
<section class="fn-section fn-checks rcd-checks container" aria-labelledby="fn-checks-title" data-fn-reveal>
<div class="rcd-checks__layout"><div class="rcd-checks__meaning">
<h2 id="fn-checks-title"><?= $e($nwd['checksTitle']) ?></h2>
<?php $checksIntro=explode('. ',$nwd['checksIntro'],2); ?>
<p class="rcd-checks__intro"><span class="rcd-checks__focus"><?= $e($checksIntro[0]) ?>.</span> <span class="rcd-checks__context"><?= $e($checksIntro[1]) ?></span></p>
</div><div class="rcd-checks__work"><p class="rcd-checks__label"><?= $e($nwd['checksLead']) ?></p><ul class="rcd-checks__parameters">
<?php foreach ($nwd['checks'] as $index=>$item) { $parts=explode(' ',$item,2);echo '<li'.($index===2 ? ' class="rcd-checks__measurement"' : '').'><strong>'.$e($parts[0]).'</strong> <span>'.$e($parts[1]).'</span></li>'; } ?>
</ul></div></div>
<p class="rcd-checks__related"><?= $link($nwd['checksOutro'],'проверку автоматических выключателей','/services/proverka-avtomaticheskikh-vyklyuchateley/') ?></p>
</section>
<section class="fn-section fn-when" aria-labelledby="fn-when-title" data-fn-reveal><div class="container fn-when__grid">
<header class="fn-when__head"><h2 id="fn-when-title"><?= $mark($nwd['whenTitle'],'УЗО') ?></h2><p><?= $e($nwd['whenIntro']) ?></p></header>
<ul class="fn-situations">
<?php
$icons=array('M5 20V7l7-4 7 4v13M9 20v-5h6v5M9 8h.01M15 8h.01M9 11h.01M15 11h.01','M4 7h7v5h9M4 17h7v-5M17 4l3 3-3 3M17 14l3 3-3 3','M6 5v-2M18 5v-2M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1ZM8 14l3 3 5-5','M12 3 22 20H2L12 3ZM12 9v5M12 17h.01');
foreach ($nwd['when'] as $i=>$item) {
    $parts=explode('. ',$item,2);
    echo '<li><svg class="fn-situation-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="'.$icons[$i].'"/></svg><strong>'.$e($parts[0]).'.</strong> <span>'.$e($parts[1]).'</span></li>';
}
?>
</ul><p class="fn-when__outro"><?= $link($nwd['whenOutro'],'приёмо-сдаточные и эксплуатационные испытания электроустановок','/services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya/') ?></p>
</div></section>
<section class="fn-section fn-process container" aria-labelledby="fn-process-title" data-fn-reveal>
<h2 id="fn-process-title"><?= $mark($nwd['processTitle'],'УЗО') ?></h2><ol class="fn-steps">
<?php foreach ($nwd['steps'] as $step) { $parts=explode('. ', $step, 2); echo '<li><h3>'.$e($parts[0]).'.</h3><p>'.$e($parts[1]).'</p></li>'; } ?>
</ol><div class="fn-report"><div class="fn-report__visual" aria-hidden="true"><svg viewBox="0 0 112 136" fill="none" focusable="false"><path d="M20 9h47l25 25v93H20V9Z" fill="#fff" stroke="#777f6b" stroke-width="1.5"/><path d="M67 9v25h25" stroke="#777f6b" stroke-width="1.5"/><path d="M35 52h29M35 62h42M35 72h36M35 101h22" stroke="#c6cbbd" stroke-width="2"/><rect x="60" y="85" width="33" height="33" rx="16.5" fill="#a9f04a"/><path d="m69 101 5 5 10-11" stroke="#171b15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="fn-report__body"><h3><?= $e($nwd['reportTitle']) ?></h3>
<p><?= $e($nwd['report'][0]) ?></p><p><?= str_replace($e('технический отчёт по электроизмерениям'),'<a href="/services/tekhnicheskiy-otchet-po-elektroizmereniyam/">'.$e('технический отчёт по электроизмерениям').'</a>',$link($nwd['report'][1],'электроизмерительная лаборатория ООО «Спецэнергоразвитие»','/services/elektroizmeritelnaya-laboratoriya/')) ?></p>
</div></div></section>
<section class="fn-section fn-cost" aria-labelledby="fn-cost-title" data-fn-reveal><div class="container fn-cost__grid">
<div class="fn-cost__copy"><h2 id="fn-cost-title"><?= $e($nwd['costTitle']) ?></h2>
<div class="fn-price"><p class="fn-price__number"><?= $e($nwd['price']) ?></p><p class="fn-price__included"><?= $e($nwd['included']) ?></p></div>
<p><?= $e($nwd['costIntro']) ?></p><h3><?= $e($nwd['costListTitle']) ?></h3><?php $list($nwd['costList']); ?><p><?= $e($nwd['costOutro']) ?></p></div>
<div class="fn-cost__form" id="raschet-stoimosti"><h3><?= $e($nwd['formTitle']) ?></h3><p><?= $e($nwd['formIntro']) ?></p>
<?php
// Theme-function output is not parsed recursively by DIAFAN. Expand the native form here.
echo $this->get_function_in_theme('<insert name="show_form" module="feedback" site_id="27" template="rcd" />');
?>
</div></div></section>
<section class="fn-section fn-faq container" aria-labelledby="fn-faq-title" data-fn-reveal>
<h2 id="fn-faq-title"><?= $e($nwd['faqTitle']) ?></h2><div class="fn-faq__items">
<?php foreach ($nwd['faq'] as $pair) { echo '<details><summary>'.$e($pair[0]).'</summary><p>'.$e($pair[1]).'</p></details>'; } ?>
</div></section>
<?php
$crumbs=array('Главная'=>'/','Услуги'=>'/services/','Электроизмерительная лаборатория'=>'/services/elektroizmeritelnaya-laboratoriya/',$nwd['name']=>'/'.$nwd['path'].'/');
$items=array();foreach ($crumbs as $name=>$path) { $items[]=array('@type'=>'ListItem','position'=>count($items)+1,'name'=>$name,'item'=>'https://nwdelectricity.ru'.$path); }
echo '<script type="application/ld+json">'.json_encode(array('@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$items),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP).'</script>';
?>
