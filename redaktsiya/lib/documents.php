<?php
if(!defined('NWD_PANEL')){http_response_code(404);exit;}

function panelLegacyDocumentTitle($text){
    if(mb_strlen($text,'UTF-8')<180 || !preg_match('/УТВЕРЖДАЮ/iu',$text)){return '';}
    if(preg_match('/ДОЛЖНОСТНАЯ\s+ИНСТРУКЦИЯ/iu',$text)){return 'Должностная инструкция';}
    if(preg_match('/(?:ПРИКАЗ|ПОЛОЖЕНИЕ|ПРОТОКОЛ)\s*(?:№|N|ОБ|О\s)/iu',$text)){return 'Образец документа';}
    return '';
}
function panelIsDocumentNode($node){
    return $node instanceof DOMElement && (($node->tagName==='section' && $node->getAttribute('data-nwd-document')==='1') || ($node->tagName==='blockquote' && panelLegacyDocumentTitle($node->textContent)!==''));
}
function panelInsideDocument($node){
    for($p=$node->parentNode;$p;$p=$p->parentNode){if(panelIsDocumentNode($p)){return true;}}
    return false;
}
function panelDocumentBodyNode(DOMElement $node){
    if($node->getAttribute('class')==='nwd-document'){
        $xpath=new DOMXPath($node->ownerDocument);
        $body=$xpath->query('./details/*[@data-nwd-document-body="1"]',$node);
        if($body && $body->length){return $body->item(0);}
    }
    return $node;
}
function panelCleanDocument(DOMElement $node,array &$images,$depth){
    $inside='';$body=panelDocumentBodyNode($node);
    foreach($body->childNodes as $child){$inside.=panelCleanNode($child,$images,$depth+1);}
    if(panelInsideDocument($node)){return $inside;}
    if(trim(html_entity_decode(strip_tags($inside),ENT_QUOTES,'UTF-8')," \t\r\n\xc2\xa0")==='' && strpos($inside,'<img ')===false){return '';}
    $title=mb_substr(trim($node->getAttribute('data-title')),0,160,'UTF-8');
    if($title===''){$title='Образец документа';}
    return '<section data-nwd-document="1" data-title="'.panelHtml($title).'">'.$inside.'</section>';
}
function panelRenderDocuments($html){
    if(strpos($html,'data-nwd-document')===false && stripos($html,'<blockquote')===false){return $html;}
    $doc=panelUtf8Document($html);$nodes=array();
    foreach($doc->getElementsByTagName('*') as $node){if(panelIsDocumentNode($node) && !panelInsideDocument($node)){$nodes[]=$node;}}
    if(!$nodes){return $html;}
    foreach($nodes as $index=>$node){
        $title=$node->getAttribute('data-title') ?: panelLegacyDocumentTitle($node->textContent);
        $title=mb_substr(trim($title),0,160,'UTF-8') ?: 'Образец документа';
        $body=panelDocumentBodyNode($node);$inside='';foreach($body->childNodes as $child){$inside.=$doc->saveHTML($child);}
        $id='nwd-document-title-'.($index+1);$label=panelHtml($title);
        $card='<section class="nwd-document" data-nwd-document="1" data-title="'.$label.'" aria-labelledby="'.$id.'">'
            .'<header class="nwd-document__head"><span class="nwd-document__icon" aria-hidden="true"></span><div><span class="nwd-document__eyebrow">Образец документа</span><p class="nwd-document__title" id="'.$id.'">'.$label.'</p></div>'
            .'<button type="button" class="nwd-document__copy" data-document-copy hidden>Скопировать образец</button></header>'
            .'<details class="nwd-document__details"><summary><span class="nwd-document__show">Открыть образец</span><span class="nwd-document__hide">Свернуть образец</span><i aria-hidden="true">+</i></summary>'
            .'<div class="nwd-document__body" data-nwd-document-body="1">'.$inside.'</div></details><p class="nwd-document__status" role="status" aria-live="polite"></p></section>';
        $rendered=panelUtf8Document($card)->getElementsByTagName('body')->item(0)->firstChild;
        $node->parentNode->replaceChild($doc->importNode($rendered,true),$node);
    }
    $result='';foreach($doc->getElementsByTagName('body')->item(0)->childNodes as $node){$result.=$doc->saveHTML($node);}
    return $result;
}
