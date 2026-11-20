<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }
require_once __DIR__.'/encoding.php';
require_once __DIR__.'/documents.php';

function panelImageId($url) {
    return preg_match('~^/redaktsiya/image\.php\?id=([a-f0-9]{32})$~D', $url, $match) ? $match[1] : '';
}

function panelCleanNode(DOMNode $node, array &$images, $depth = 0) {
    if ($depth > 50) { panelFail('Слишком много вложенных элементов в тексте.'); }
    if ($node instanceof DOMText) { return panelHtml($node->nodeValue); }
    if (!($node instanceof DOMElement)) { return ''; }
    $tag = strtolower($node->tagName);
    if ($tag==='section' && $node->getAttribute('data-nwd-document')==='1') { return panelCleanDocument($node,$images,$depth); }
    if (in_array($tag,array('script','style','iframe','svg','object','template','form','math'),true)) { return ''; }
    if ($tag === 'h1') { $tag = 'h2'; }
    if ($tag === 'br' || $tag === 'hr') { return '<'.$tag.'>'; }
    if ($tag === 'img') {
        $id = panelImageId($node->getAttribute('src'));
        if (!$id) { return ''; }
        $images[$id] = true;
        return '<img src="/redaktsiya/image.php?id='.$id.'" alt="'.panelHtml($node->getAttribute('alt')).'" loading="lazy">';
    }
    $inside = '';
    foreach ($node->childNodes as $child) { $inside .= panelCleanNode($child,$images,$depth+1); }
    if (!in_array($tag, array('p','strong','b','em','i','s','code','pre','h2','h3','ul','ol','li','blockquote','a','table','thead','tbody','tfoot','tr','th','td'), true)) { return $inside; }
    $attrs = '';
    if ($tag === 'a') {
        $href = trim($node->getAttribute('href'));
        if (!preg_match('~^(?:https?://[^/]|mailto:|tel:|/(?!/)|#)~i',$href) || preg_match('~[\x00-\x20\x7f\\\\]~',$href)) { return $inside; }
        $attrs = ' href="'.panelHtml($href).'"';
        if ($node->getAttribute('target') === '_blank') { $attrs .= ' target="_blank" rel="noopener noreferrer"'; }
    }
    if ($tag === 'td' || $tag === 'th') {
        foreach (array('colspan','rowspan') as $attr) {
            $n=(int)$node->getAttribute($attr);
            if ($n>1 && $n<=50) { $attrs.=' '.$attr.'="'.$n.'"'; }
        }
    }
    if ($tag === 'ol' && ctype_digit($node->getAttribute('start'))) { $attrs.=' start="'.(int)$node->getAttribute('start').'"'; }
    $html='<'.$tag.$attrs.'>'.$inside.'</'.$tag.'>';
    return $tag==='table' ? '<div class="article-table-scroll">'.$html.'</div>' : $html;
}

function panelForm(array $raw, array &$images, $preview = false) {
    $form=array();
    foreach (array('title'=>240,'slug'=>72,'category'=>80,'seoTitle'=>240,'seoDescription'=>500,'imageAlt'=>500,'image'=>200,'status'=>12,'content'=>300000) as $key=>$max) {
        if (!isset($raw[$key]) || !is_string($raw[$key]) || mb_strlen($raw[$key],'UTF-8')>$max) { panelFail('Проверьте поле «'.$key.'».'); }
        $form[$key]=trim($raw[$key]);
    }
    if (!in_array($form['status'],array('draft','published','scheduled'),true)) { panelFail('Неизвестный статус.'); }
    $form['scheduledAt'] = null;
    if ($form['status'] === 'scheduled') {
        $stamp = panelScheduleInstant($raw['scheduledAt'] ?? null);
        if (!$stamp && !$preview) { panelFail('Выберите дату и время публикации по Москве.'); }
        $form['scheduledAt'] = $stamp ? gmdate('c',$stamp) : null;
    }
    if ($form['slug'] !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$form['slug'])) { panelFail('Проверьте адрес статьи.'); }
    $images=array();
    if ($form['image'] !== '') {
        $id=panelImageId($form['image']);
        if (!$id) { panelFail('Выберите загруженную обложку.'); }
        $images[$id]=true;
    }
    $doc=panelUtf8Document($form['content']);
    $html='';
    foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $node) { $html.=panelCleanNode($node,$images); }
    $form['content']=$html;
    if (!$preview && in_array($form['status'],array('published','scheduled'),true)) {
        if ($form['title']==='' || trim(html_entity_decode(strip_tags($html),ENT_QUOTES,'UTF-8')," \t\r\n\xc2\xa0")==='' || $form['seoTitle']==='' || $form['seoDescription']==='') { panelFail('Заполните заголовок, текст, SEO Title и SEO Description.'); }
        if ($form['image'] && !$form['imageAlt']) { panelFail('Добавьте описание обложки (Alt).'); }
    }
    return $form;
}

function panelSlug($title) {
    $ru=preg_split('//u','абвгдеёжзийклмнопрстуфхцчшщъыьэюя',-1,PREG_SPLIT_NO_EMPTY);
    $en=explode(' ','a b v g d e e zh z i y k l m n o p r s t u f h c ch sh shch  y  e yu ya');
    $value=strtr(mb_strtolower($title,'UTF-8'),array_combine($ru,$en));
    return rtrim(substr(trim(preg_replace('/[^a-z0-9]+/','-',$value),'-'),0,72),'-') ?: 'statya';
}

function panelArticleHtml(array $form, $date) {
    $html='<article class="nwd-reading"><header class="nwd-reading-head">';
    $html.='<div class="nwd-reading-meta"><a href="/articles/">Статьи</a>';
    if ($form['category']!=='') { $html.='<span>'.panelHtml($form['category']).'</span>'; }
    if ($date) {
        $day=(new DateTimeImmutable('@'.(int)$date))->setTimezone(new DateTimeZone('Europe/Moscow'));
        $html.='<time datetime="'.$day->format('Y-m-d').'">'.$day->format('d.m.Y').'</time>';
    }
    $html.='</div><h1>'.panelHtml($form['title'] ?: 'Статья без названия').'</h1></header>';
    if ($form['image']) { $html.='<figure><img src="'.panelHtml($form['image']).'" alt="'.panelHtml($form['imageAlt']).'"></figure>'; }
    return $html.'<div class="nwd-prose">'.panelRenderDocuments($form['content']).'</div></article>';
}
