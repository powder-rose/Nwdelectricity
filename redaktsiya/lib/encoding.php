<?php
if (!defined('NWD_PANEL')) { http_response_code(404); exit; }

/** Explicit byte encoding works consistently in the web and hosting CLI libxml builds. */
function panelUtf8Document($html) {
    if (!is_string($html) || !mb_check_encoding($html,'UTF-8')) { panelFail('Текст статьи должен быть в кодировке UTF-8.'); }
    $doc=new DOMDocument('1.0','UTF-8');
    $prefix='<?xml encoding="UTF-8"?><!doctype html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
    $ok=$doc->loadHTML($prefix.$html.'</body></html>',LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    if (!$ok || !$doc->getElementsByTagName('body')->length) { panelFail('Не удалось прочитать текст статьи.'); }
    $doc->encoding='UTF-8';
    return $doc;
}

/** Repair only exact, reversible UTF-8-as-Latin-1 corruption with a Cyrillic gain. */
function panelRestoreUtf8Text($text, $minimumGain = 2) {
    if (!is_string($text) || !preg_match('/[\x{00C2}-\x{00F4}][\x{0080}-\x{00BF}]/u',$text)) { return $text; }
    $original=$text; $best=$text;
    preg_match_all('/[А-Яа-яЁё]/u',$text,$letters); $score=count($letters[0]);
    for ($pass=0;$pass<3;$pass++) {
        // No substitution, IGNORE or transliteration: the round trip must be exact.
        $bytes=@iconv('UTF-8','ISO-8859-1',$text);
        if ($bytes===false || !mb_check_encoding($bytes,'UTF-8') || iconv('ISO-8859-1','UTF-8',$bytes)!==$text) {
            // HTML entities may have produced healthy NBSP, degree signs or punctuation
            // inside a corrupt text node. Decode only exact complete UTF-8 byte runs.
            $pattern='~(?:[\x{00C2}-\x{00DF}][\x{0080}-\x{00BF}]|[\x{00E0}-\x{00EF}][\x{0080}-\x{00BF}]{2}|[\x{00F0}-\x{00F4}][\x{0080}-\x{00BF}]{3})+~u';
            $bytes=preg_replace_callback($pattern,function($match) {
                $candidate=iconv('UTF-8','ISO-8859-1',$match[0]);
                return $candidate!==false && mb_check_encoding($candidate,'UTF-8') && iconv('ISO-8859-1','UTF-8',$candidate)===$match[0] ? $candidate : $match[0];
            },$text);
        }
        if ($bytes===null || $bytes===$text) { break; }
        $text=$bytes;
        preg_match_all('/[А-Яа-яЁё]/u',$text,$letters); $next=count($letters[0]);
        if ($next>=$score+$minimumGain) { $best=$text; $score=$next; }
    }
    return $best;
}

function panelRestoreUtf8Html($html) {
    $plain=html_entity_decode(strip_tags($html),ENT_QUOTES | ENT_HTML5,'UTF-8');
    preg_match_all('/[ÐÑÃÂ]/u',$plain,$bad);
    // Do not reinterpret healthy articles or intentional examples of broken encoding.
    if (count($bad[0])<6 || preg_match('/[А-Яа-яЁё]/u',$plain)) { return $html; }
    $doc=panelUtf8Document($html); $changed=false;
    $walk=function($node) use (&$walk,&$changed) {
        if ($node instanceof DOMText) {
            $restored=panelRestoreUtf8Text($node->nodeValue,0);
            if ($restored!==$node->nodeValue) { $node->nodeValue=$restored; $changed=true; }
        } elseif ($node instanceof DOMElement) {
            foreach (array('alt','title','aria-label') as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $value=$node->getAttribute($attribute);$restored=panelRestoreUtf8Text($value,0);
                    if ($value!==$restored) { $node->setAttribute($attribute,$restored);$changed=true; }
                }
            }
        }
        foreach ($node->childNodes as $child) { $walk($child); }
    };
    $body=$doc->getElementsByTagName('body')->item(0);$walk($body);
    if (!$changed) { return $html; }
    $result='';foreach($body->childNodes as $node) { $result.=$doc->saveHTML($node); }
    return $result;
}
