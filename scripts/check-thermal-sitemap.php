<?php
/** Read downloaded native DIAFAN sitemap XML without network or external entity expansion. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
function nwdThermalSitemap($file) {
    $raw=file_get_contents($file);if(!$raw || stripos($raw,'<!DOCTYPE')!==false){throw new RuntimeException('Empty or unsafe sitemap');}
    $doc=new DOMDocument;$old=libxml_use_internal_errors(true);$ok=$doc->loadXML($raw,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($old);
    if(!$ok || !in_array($doc->documentElement->localName,array('urlset','sitemapindex'),true)){throw new RuntimeException('Unexpected sitemap XML');}
    return $doc;
}
try {
    if(($argv[1]??'')==='--children'){
        $doc=nwdThermalSitemap($argv[2]??'');if($doc->documentElement->localName==='sitemapindex'){
            $locs=$doc->getElementsByTagName('loc');if($locs->length>100){throw new RuntimeException('Too many sitemap parts');}
            foreach($locs as $loc){$url=trim($loc->textContent);if(!preg_match('~^https://nwdelectricity\.ru/map/sitemap/[1-9][0-9]*/$~D',$url)){throw new RuntimeException('Unexpected sitemap part URL');}echo $url."\n";}
        }exit;
    }
    $main='https://nwdelectricity.ru/services/teplovizionnoe-obsledovanie-elektrooborudovaniya/';$old='/services/teplovizionnyy-kontrol-sostoyaniya-elektrooborudovaniya/';$count=0;
    foreach(array_slice($argv,1) as $file){$doc=nwdThermalSitemap($file);if($doc->documentElement->localName!=='urlset'){continue;}foreach($doc->getElementsByTagName('loc') as $loc){$url=trim($loc->textContent);if(strpos($url,$old)!==false){throw new RuntimeException('Duplicate thermal URL is still in sitemap');}if($url===$main){$count++;}}}
    if($count!==1){throw new RuntimeException('Primary thermal URL must occur exactly once in sitemap');}
    echo "THERMAL_PUBLIC_SITEMAP_OK\n";
}catch(Throwable $e){fwrite(STDERR,'THERMAL_SITEMAP_FAILED='.$e->getMessage()."\n");exit(1);}
