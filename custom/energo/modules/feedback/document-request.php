<?php
if (!defined('DIAFAN') && !defined('NWD_PANEL')) { http_response_code(404); exit; }

/** The source fragment stores public document context, never personal contact data. */
function nwdDocumentRequestSource($articlePath, $index, $title) {
    return 'https://nwdelectricity.ru'.$articlePath.'#'.http_build_query(array('nwd-document'=>(int)$index,'title'=>$title),'','&',PHP_QUERY_RFC3986);
}
function nwdDocumentRequestContext($source) {
    $url=parse_url(html_entity_decode((string)$source,ENT_QUOTES | ENT_HTML5,'UTF-8'));
    if (!$url || ($url['scheme'] ?? '')!=='https' || ($url['host'] ?? '')!=='nwdelectricity.ru'
        || !preg_match('~^/articles/[^/]+/$~D',$url['path'] ?? '')) { return null; }
    parse_str($url['fragment'] ?? '',$fragment);
    $index=$fragment['nwd-document'] ?? '';$title=$fragment['title'] ?? '';
    if (!is_scalar($index) || !ctype_digit((string)$index) || (int)$index<1 || (int)$index>1000
        || !is_string($title) || trim($title)==='' || !mb_check_encoding($title,'UTF-8') || mb_strlen($title,'UTF-8')>160) { return null; }
    return array('index'=>(int)$index,'title'=>$title,'url'=>'https://nwdelectricity.ru'.$url['path'].'#nwd-document-'.(int)$index);
}
function nwdDocumentRequestFields(array $params) {
    $fields=array();
    foreach ($params as $param) {
        $type=$param['type'];
        if (($type==='phone' || $type==='email') && !isset($fields[$type])) { $fields[$type]=$param; }
        if ($type==='text' && (!isset($fields['name']) || preg_match('/имя|фио|фамил/ui',$param['name']))) { $fields['name']=$param; }
    }
    return $fields;
}
function nwdDocumentRequestValidate(array &$params, array $post, array &$errors) {
    $fields=nwdDocumentRequestFields($params);
    if (count($fields)!==3) { $errors[0]='Форма временно недоступна. Пожалуйста, свяжитесь с нами по телефону.';return null; }
    $params=array_values($fields);
    foreach ($fields as $type=>$param) {
        $key='p'.$param['id'];$value=$post[$key] ?? null;
        $valid=is_string($value) && mb_check_encoding($value,'UTF-8') && !preg_match('/[\x00-\x1f\x7f]/',$value) && trim($value)!=='';
        if ($valid && $type==='name') { $valid=mb_strlen(trim($value),'UTF-8')<=100; }
        if ($valid && $type==='email') { $valid=strlen($value)<=254 && filter_var(trim($value),FILTER_VALIDATE_EMAIL); }
        if ($valid && $type==='phone') {
            $digits=preg_replace('/\D/','',$value);
            $valid=strlen($value)<=40 && preg_match('/^\+?[0-9 ()\-]+$/D',trim($value)) && strlen($digits)>=10 && strlen($digits)<=15;
        }
        if (!$valid) { $errors[$key]=array('name'=>'Укажите имя (не более 100 символов).','phone'=>'Укажите корректный телефон.','email'=>'Укажите корректный email.')[$type]; }
    }
    $article=$post['nwd_document_article'] ?? '';$index=$post['nwd_document_index'] ?? '';
    if (!is_string($article) || !preg_match('/^[1-9][0-9]{0,9}$/D',$article) || !is_string($index) || !ctype_digit($index) || (int)$index<1 || (int)$index>1000) {
        $errors[0]='Откройте форму из врезки документа в статье.';return null;
    }
    if ($errors) { return null; }
    // Derive the title and source from a visible published article, never from the submitted label or Referer.
    $lang=(int)_LANG;
    $row=DB::query_fetch_array("SELECT a.slug,a.payload FROM {panel_article} a JOIN {site} s ON s.id=a.site_id WHERE a.site_id=%d AND a.status='published' AND a.deleted=0 AND s.trash='0' AND s.act$lang='1' LIMIT 1",(int)$article);
    if (!$row) { $errors[0]='Этот документ сейчас недоступен. Обновите статью.';return null; }
    if (!defined('NWD_PANEL')) { define('NWD_PANEL',true); }
    require_once ABSOLUTE_PATH.'redaktsiya/lib/db.php';
    require_once ABSOLUTE_PATH.'redaktsiya/lib/content.php';
    $payload=json_decode($row['payload'],true);
    if (!is_array($payload) || !is_string($payload['content'] ?? null)) { $errors[0]='Не удалось прочитать документ. Обновите статью.';return null; }
    $doc=panelUtf8Document(panelRestoreUtf8Html($payload['content']));$documents=array();
    foreach ($doc->getElementsByTagName('*') as $node) { if (panelIsDocumentNode($node) && !panelInsideDocument($node)) { $documents[]=$node; } }
    if (!isset($documents[(int)$index-1])) { $errors[0]='Этот документ больше не доступен. Обновите статью.';return null; }
    return array('source'=>nwdDocumentRequestSource('/articles/'.rawurlencode($row['slug']).'/',(int)$index,panelDocumentTitle($documents[(int)$index-1])));
}
