<?php
/** Only expose links to public native routes; drafts and missing future pages stay plain text. */
if (!defined('DIAFAN')) { http_response_code(404);exit; }
function nwdLaboratoryActivePaths(array $paths) {
    if (!$paths) { return array(); }
    $lang=(int)_LANG;$args=array_map(function($p){return trim($p,'/');},$paths);
    $sql="SELECT r.rewrite FROM {rewrite} r
        LEFT JOIN {site} s ON r.module_name='site' AND r.element_type='element' AND s.id=r.element_id
        LEFT JOIN {shop_category} c ON r.module_name='shop' AND r.element_type='cat' AND c.id=r.element_id
        LEFT JOIN {shop} e ON r.module_name='shop' AND r.element_type='element' AND e.id=r.element_id
        WHERE TRIM(BOTH '/' FROM r.rewrite) IN (".implode(',',array_fill(0,count($args),"'%s'")).") AND
        ((s.id IS NOT NULL AND s.trash='0' AND s.act$lang='1' AND s.access='0') OR
         (c.id IS NOT NULL AND c.trash='0' AND c.act$lang='1' AND c.access='0') OR
         (e.id IS NOT NULL AND e.trash='0' AND e.act$lang='1' AND e.access='0'))";
    $rows=DB::query_fetch_all($sql,...$args);$active=array();
    foreach($rows as $row) { $active['/'.trim($row['rewrite'],'/').'/']=true; }
    return $active;
}
