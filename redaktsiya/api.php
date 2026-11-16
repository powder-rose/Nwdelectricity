<?php
define('NWD_PANEL',true);
foreach (array('db','auth','content','publication','articles','media') as $file) { require __DIR__.'/lib/'.$file.'.php'; }
panelHeaders();
header('Content-Type: application/json; charset=UTF-8');
try {
    panelDb();
    if (!panelAuthorized()) { panelFail('Войдите в панель.',401); }
    $method=$_SERVER['REQUEST_METHOD'];
    if ($method!=='GET') { panelCsrf($_SERVER['HTTP_X_NWD_CSRF'] ?? ''); }
    session_write_close();
    $resource=$_GET['resource'] ?? '';
    $id=$_GET['id'] ?? '';
    $input=array();
    if ($method!=='GET' && $resource!=='article-images' && $method!=='DELETE') {
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '','application/json')!==0) { panelFail('Ожидается JSON.',415); }
        $body=file_get_contents('php://input',false,null,0,1800001);
        if (strlen($body)>1800000) { panelFail('Текст слишком большой.',413); }
        $input=json_decode($body,true);
        if (!is_array($input)) { panelFail('Не удалось прочитать поля статьи.'); }
    }
    if ($resource==='article-images' && $method==='POST') { $result=panelUpload(); }
    elseif ($resource!=='articles') { panelFail('Адрес не найден.',404); }
    elseif ($id==='preview' && $method==='POST') {
        $images=array();$form=panelForm($input,$images,true);
        $result=array('html'=>panelArticleHtml($form,time()));
    } elseif ($id==='' && $method==='GET') { $result=panelArticleList($_GET); }
    elseif ($id==='' && $method==='POST') { $result=array('ok'=>true,'article'=>panelSave($input)); }
    elseif (!is_string($id) || !preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/D',$id)) { panelFail('Статья не найдена.',404); }
    elseif ($method==='GET') { $result=array('article'=>panelRecord(panelLoad($id))); }
    elseif ($method==='PUT') { $result=array('ok'=>true,'article'=>panelSave($input,$id)); }
    elseif ($method==='DELETE') { panelSave(array('revision'=>$_GET['revision'] ?? 0),$id,true);$result=array('ok'=>true); }
    else { panelFail('Метод недоступен.',405); }
    echo panelJson($result);
} catch (Throwable $e) { echo panelJson(array('error'=>panelErrorResponse($e))); }
