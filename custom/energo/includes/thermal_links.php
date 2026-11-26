<?php
/** Exact internal URL and thermal tariff replacements shared by migration and public articles. */
if(!defined('DIAFAN')){http_response_code(404);exit;}
function nwdThermalOldPath(){return 'services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya';}
function nwdThermalInternalUrl($value,$copy) {
    $u=html_entity_decode($value,ENT_QUOTES | ENT_HTML5,'UTF-8');$parts=parse_url($u);
    if($parts===false || (isset($parts['host']) && !in_array(strtolower($parts['host']),array('nwdelectricity.ru','www.nwdelectricity.ru'),true)) || isset($parts['user']) || (isset($parts['scheme']) && !in_array(strtolower($parts['scheme']),array('http','https'),true))){return $value;}
    if(trim($parts['path']??'','/')!==nwdThermalOldPath()){return $value;}
    return '/'.$copy['path'].'/'.(isset($parts['query'])?'?'.$parts['query']:'').(isset($parts['fragment'])?'#'.$parts['fragment']:'');
}
function nwdThermalMarkup($source,array $copy,$depth=0) {
    if(!is_string($source) || $source===''){return $source;}
    // DIAFAN text fields can contain either raw markup or one HTML-escaped layer.
    if(strpos($source,'<a')===false && strpos($source,'<tr')===false && $depth<2 && preg_match('/&lt;(?:a\b|tr\b|table\b|p\b|div\b)/i',$source)){
        $decoded=html_entity_decode($source,ENT_QUOTES | ENT_HTML5,'UTF-8');$changed=nwdThermalMarkup($decoded,$copy,$depth+1);
        return $changed===$decoded?$source:htmlspecialchars($changed,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');
    }
    $source=preg_replace_callback('~<a\b[^>]*>.*?</a>~isu',function($m)use($copy){
        return preg_replace_callback('~\bhref\s*=\s*(["\x27])(.*?)\1~isu',function($a)use($copy){$next=nwdThermalInternalUrl($a[2],$copy);return $next===$a[2]?$a[0]:'href='.$a[1].htmlspecialchars($next,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8').$a[1];},$m[0]);
    },$source);
    return preg_replace_callback('~<tr\b[^>]*>.*?</tr>~isu',function($m){
        $row=$m[0];$label=html_entity_decode(preg_replace('~<[^>]+>~',' ',$row),ENT_QUOTES | ENT_HTML5,'UTF-8');
        if(!preg_match('/тепловизионн[а-я]*\s+(?:обследован|контрол)/ui',$label)){return $row;}
        return preg_replace_callback('~(<td\b[^>]*>)(.*?)(</td>)~isu',function($cell){
            $text=trim(html_entity_decode(strip_tags($cell[2]),ENT_QUOTES | ENT_HTML5,'UTF-8'));
            if(preg_match('/^(?:от[\s\p{Z}]+)?11[\s\p{Z}]*000(?:[,.]00)?[\s\p{Z}]*(?:₽|руб\.?|р\.?)?$/ui',$text)){return $cell[1].'от 4 900 ₽'.$cell[3];}
            return $cell[0];
        },$row);
    },$source);
}
