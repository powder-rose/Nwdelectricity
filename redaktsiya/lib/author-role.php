<?php
if (!defined('DIAFAN') && !defined('NWD_PANEL')) { http_response_code(404);exit; }

const NWD_AUTHOR_ROLE='Руководитель ООО «Спецэнергоразвитие»';

/** Replace only the author's role, preserving the surrounding copy and HTML encoding level. */
function nwdReplaceAuthorRole($text) {
    $space='(?:\s|\x{00a0}|&(?:amp;){0,2}(?:nbsp|\#160|\#x0*a0);)+';
    $quote='&(?:amp;){0,2}(?:laquo|raquo|quot|\#171|\#187|\#34|\#x0*(?:ab|bb|22));';
    $pattern='~(?<![\p{L}\p{N}])руководитель'.$space.'(?:ООО'.$space.')?(?<open>«|"|'.$quote.')\s*Спецэнергоразвити[яе]\s*(?<close>»|"|'.$quote.')~iu';
    $result=preg_replace_callback($pattern,function($m) {
        $depth=substr_count($m['open'],'amp;');
        $open=strpos($m['open'],'&')===0 ? '&'.str_repeat('amp;',$depth).'laquo;' : '«';
        $close=strpos($m['close'],'&')===0 ? '&'.str_repeat('amp;',substr_count($m['close'],'amp;')).'raquo;' : '»';
        return 'Руководитель ООО '.$open.'Спецэнергоразвитие'.$close;
    },$text);
    if ($result===null) { throw new RuntimeException('Author role text is not valid UTF-8'); }
    return $result;
}

function nwdAuthorRoleValue($value) {
    if (is_string($value)) { return nwdReplaceAuthorRole($value); }
    if (is_array($value)) { foreach ($value as &$item) { $item=nwdAuthorRoleValue($item); }unset($item); }
    elseif ($value instanceof stdClass) { foreach ($value as $key=>$item) { $value->$key=nwdAuthorRoleValue($item); } }
    return $value;
}

function nwdAuthorRolePayload($json) {
    $value=json_decode($json,false,512,JSON_THROW_ON_ERROR);
    $before=json_encode($value,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $after=json_encode(nwdAuthorRoleValue($value),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    return $after===$before ? $json : $after;
}
