<?php
/** One exact legacy service URL; all other requests keep native DIAFAN routing. */
if(!defined('DIAFAN')){http_response_code(404);exit;}
function nwdThermalRedirectTarget($request,$base) {
    if(!is_string($request) || preg_match('/[\x00-\x20\x7f]/',$request)){return null;}
    $path=parse_url($request,PHP_URL_PATH);
    if(!in_array($path,array('/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya/','/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya'),true)){return null;}
    $host=parse_url($base,PHP_URL_HOST);
    if(!in_array($host,array('localhost','127.0.0.1','[::1]'),true)){$base='https://nwdelectricity.ru/';}
    $query=parse_url($request,PHP_URL_QUERY);
    return rtrim($base,'/').'/services/teplovizionnoe-obsledovanie-elektrooborudovaniya/'.($query!==null && $query!==false && $query!==''?'?'.$query:'');
}
