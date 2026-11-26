<?php
/** Validate a downloaded public response without initiating requests or sending leads. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/breakers-html.php';
function nwdBreakersValidatePublicResponse($html,array $copy) {
    if(!is_string($html) || $html===''){throw new RuntimeException('Empty response');}
    if(preg_match('/\[ERROR#\d+\]/',$html)){throw new RuntimeException('DIAFAN reported a page rendering error');}
    // Count declared elements, excluding comments and raw text in client scripts.
    $markup=preg_replace('~<!--.*?-->|<(script|style|textarea|title)\b[^>]*>.*?</\1\s*>~is','',$html);
    foreach(array('html','head','body','main') as $tag){if(preg_match_all('/<'. $tag .'\b/i',$markup)!==1){throw new RuntimeException('Duplicated or missing page shell: '.$tag);}}
    if(strpos($html,'<?')!==false){throw new RuntimeException('Unexpanded PHP reached the public response');}
    $dom=new DOMDocument;$prev=libxml_use_internal_errors(true);$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prev);$x=new DOMXPath($dom);
    $text=function($path)use($x){$node=$x->query($path)->item(0);return $node ? trim($node->textContent):'';};
    $assert=function($ok,$message){if(!$ok){throw new RuntimeException($message);}};
    // libxml's HTML4 parser may create an implicit body for a tracking pixel in
    // the shared head, then discard attributes of the explicit HTML5 body tag.
    // Read that single declared tag separately; all content checks still use
    // the complete response below, including shell counts and rendered sections.
    if(!preg_match('~<body\b(?:"[^"]*"|\'[^\']*\'|[^\'">])*>~i',$markup,$bodyTag)){throw new RuntimeException('Missing declared body');}
    $bodyDoc=new DOMDocument;$prev=libxml_use_internal_errors(true);
    $bodyDoc->loadHTML('<html><head><meta charset="utf-8"></head>'.$bodyTag[0].'</body></html>',LIBXML_NONET);
    libxml_clear_errors();libxml_use_internal_errors($prev);$body=$bodyDoc->getElementsByTagName('body')->item(0);
    $assert($body && in_array('page-breakers',preg_split('/\s+/',trim($body->getAttribute('class'))),true),'Breakers route presentation is not active');
    $assert(!$body->hasAttribute('style'),'Unexpected body inline style');
    $assert($x->query('//header[contains(@class,"page-header")]')->length===1 && $x->query('//footer[contains(@class,"page-footer")]')->length===1,'Shared header or footer duplicated');
    $assert($x->query('//h1')->length===1 && nwdBreakersHeadingText($x->query('//h1')->item(0))===$copy['name'],'Unexpected H1');
    $assert($x->query('//main//section')->length===6 && $x->query('//main//h2')->length===5,'Unexpected section structure');
    $assert(html_entity_decode($text('//title'),ENT_QUOTES,'UTF-8')===$copy['seoTitle'],'Unexpected SEO title');
    $assert($x->query('//meta[@name="description" and @content="'.$copy['description'].'"]')->length===1,'Unexpected meta description');
    $url='https://nwdelectricity.ru/'.$copy['path'].'/';
    $assert($x->query('//link[@rel="canonical" and @href="'.$url.'"]')->length===1,'Unexpected canonical');
    foreach($x->query('//meta[@name="robots"]') as $node){$assert(stripos($node->getAttribute('content'),'noindex')===false,'Page is noindex');}
    $assert($x->query('//form[@data-fn-form]')->length===1,'Inline native form missing');
    $assert($x->query('//form[@data-fn-form]//input[@name="nwd_consent" and @required and not(@checked)]')->length===1,'Consent guard missing');
    $assert($x->query('//main//details')->length===4,'FAQ missing');
    foreach(array('/services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya/','/services/elektroizmeritelnaya-laboratoriya/','/services/tekhnicheskiy-otchet-po-elektroizmereniyam/') as $path){$assert($x->query('//main//a[@href="'.$path.'"]')->length>=1,'Brief link missing: '.$path);}
    $assert(strpos($html,'<insert ')===false,'Native inserts were not expanded');
    $assert($x->query('//main//p[@class="bkr-price"]')->length===2,'Two prices missing');
    $assert($x->query('//nav[@aria-label="Хлебные крошки"]//a')->length===3,'Three breadcrumb links missing');
    $assert($x->query('//img[contains(@src,"proverka-avtomaticheskikh-vyklyuchateley.webp")]')->length===1,'Hero image missing');
    $main=$x->query('//main')->item(0)->textContent;
    $assert(!preg_match('/121\s*(?:руб|₽)|трехкратного номинального|трёхкратного номинального|1 раз в 3 года|бесплатный выезд/ui',$main),'Obsolete service content');
    $service=false;foreach($x->query('//script[@type="application/ld+json"]') as $node){$data=json_decode($node->textContent,true);foreach($data['@graph']??array() as $row){if(($row['@type']??'')==='Service' && ($row['url']??'')===$url){$service=true;}}}
    $assert($service,'Service structured data missing');
}
if(defined('NWD_INS_PUBLIC_TEST')){return;}
define('DIAFAN',true);$copy=require dirname(__DIR__).'/custom/energo/themes/breakers_content.php';
try {
    nwdBreakersValidatePublicResponse(file_get_contents($argv[1] ?? ''),$copy);
    echo "BREAKERS_PUBLIC_OK\n";
} catch(Throwable $e){fwrite(STDERR,'BREAKERS_PUBLIC_FAILED='.$e->getMessage()."\n");exit(1);}
