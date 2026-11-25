<?php
/** Add only the catalogue labels and link explicitly requested by the brief. */
if (!defined('DIAFAN')) { http_response_code(404);exit; }
require_once __DIR__.'/rcd-lab-link.php';
function nwdPowerToolsLabLink($source) {
    $decoded=html_entity_decode($source,ENT_QUOTES | ENT_HTML5,'UTF-8');
    $path='/services/ispytanie-elektroinstrumenta/';$title='Испытание переносного электроинструмента';
    if (preg_match('~<a\b[^>]*href=["\x27](?:https://nwdelectricity\.ru)?'.preg_quote($path,'~').'["\x27][^>]*>\s*'.preg_quote($title,'~').'\s*</a>~u',$decoded)) { return $source; }
    if (strpos($decoded,$path)!==false || trim(strip_tags($decoded))==='') { throw new RuntimeException('Laboratory catalogue link requires review; no text replaced'); }
    $li='<li><a href="'.$path.'">'.$title.'</a></li>';
    $group='Кабельные линии и диагностика';$heading='Услуги электроизмерительной лаборатории';
    $encoded=strpos($source,'<')===false && strpos($source,'&lt;')!==false;
    $encode=function($value)use($encoded){return $encoded ? htmlspecialchars($value,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8') : $value;};
    preg_match_all('~<h3\b[^>]*>\s*'.preg_quote($group,'~').'\s*</h3>\s*<ul\b[^>]*>.*?</ul>~su',$decoded,$groups);
    if (count($groups[0])===1) {
        $old=$groups[0][0];$new=substr($old,0,-5).$li.'</ul>';$needle=$encode($old);
        if (substr_count($source,$needle)!==1) { throw new RuntimeException('Laboratory catalogue encoding requires review'); }
        return str_replace($needle,$encode($new),$source);
    }
    if (strpos($decoded,$group)!==false || strpos($decoded,$heading)!==false) { throw new RuntimeException('Existing laboratory catalogue structure requires review'); }
    $section='<section class="nwd-lab-services" aria-labelledby="nwd-lab-tools-title"><h2 id="nwd-lab-tools-title">'.$heading.'</h2><h3>'.$group.'</h3><ul>'.$li.'</ul></section>';
    return $source."\n".$encode($section);
}
