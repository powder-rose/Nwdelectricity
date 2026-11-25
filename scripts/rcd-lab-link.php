<?php
/** Preserve the laboratory's copy and markup while linking its existing UZO list item. */
if (!defined('DIAFAN')) { http_response_code(404);exit; }

/** Resolve the route before choosing a native table; services may be shop categories. */
function nwdRcdLaboratoryTable($module, $type) {
    $tables=array('site:element'=>'site','shop:cat'=>'shop_category','shop:element'=>'shop');
    $key=$module.':'.$type;
    if (!isset($tables[$key])) { throw new RuntimeException('Unsupported laboratory route type; no content replaced'); }
    return $tables[$key];
}

function nwdRcdLaboratoryBackupTable(array $saved) {
    $table=$saved['table'] ?? 'site'; // Compatibility with the first release's rollback records.
    if (!in_array($table,array('site','shop_category','shop'),true)) { throw new RuntimeException('Invalid laboratory backup table'); }
    return $table;
}

function nwdRcdLocalLaboratoryOption($requested, $isLocal) {
    if ($requested && !$isLocal) { throw new RuntimeException('Skipping a missing laboratory is allowed only with NWD_LOCAL=1'); }
    return $requested && $isLocal;
}

function nwdRcdLaboratory($lang, $allowMissing) {
    $path='services/elektroizmeritelnaya-laboratoriya';
    $routes=panelRows('SELECT module_name,element_type,element_id FROM '.panelTable('rewrite').' WHERE rewrite IN (?,?,?,?) FOR UPDATE',array($path,$path.'/','/'.$path,'/'.$path.'/'));
    if (!$routes) {
        if ($allowMissing) { return null; }
        throw new RuntimeException('Laboratory route not found');
    }
    if (count($routes)!==1) { throw new RuntimeException('Laboratory route is ambiguous; no content replaced'); }
    $route=$routes[0];$table=nwdRcdLaboratoryTable($route['module_name'],$route['element_type']);
    $row=panelRow('SELECT id,text'.(int)$lang.',timeedit FROM '.panelTable($table)." WHERE id=? AND trash='0' FOR UPDATE",array((int)$route['element_id']));
    if (!$row) {
        if ($allowMissing) { return null; }
        throw new RuntimeException('Laboratory route target not found');
    }
    $row['table']=$table;
    return $row;
}

function nwdRcdLabLink($source) {
    $decoded=html_entity_decode($source,ENT_QUOTES | ENT_HTML5,'UTF-8');
    $path='/services/proverka-uzo/';
    if (preg_match('~<a\b[^>]*href=["\x27](?:https://nwdelectricity\.ru)?'.preg_quote($path,'~').'["\x27][^>]*>[^<]*(?:УЗО)[^<]*</a>~u',$decoded)) { return $source; }
    preg_match_all('~<li\b[^>]*>.*?</li>~isu',$decoded,$items);
    $found=0;
    foreach ($items[0] as $item) {
        $text=trim(preg_replace('/\s+/u',' ',strip_tags($item)));
        if ($text==='Срабатывание УЗО;' || $text==='Срабатывание УЗО') {
            if (stripos($item,'<a')!==false) { throw new RuntimeException('Laboratory UZO item already has a different link'); }
            $found++;
        }
    }
    $needle='Срабатывание УЗО';
    if ($found!==1 || substr_count($source,$needle)!==1) { throw new RuntimeException('Existing laboratory UZO list item not found uniquely; no content replaced'); }
    $anchor='<a href="'.$path.'">'.$needle.'</a>';
    if (strpos($source,'<li')===false) { $anchor=htmlspecialchars($anchor,ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8'); }
    return str_replace($needle,$anchor,$source);
}
