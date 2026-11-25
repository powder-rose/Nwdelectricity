<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }

require_once __DIR__.'/document-request.php';

function nwdFeedbackText($value) {
    return html_entity_decode(strip_tags(preg_replace('~<br\s*/?>(?:\r?\n)?~i', "\n", (string)$value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function nwdFeedbackSource() {
    $source = getenv('HTTP_REFERER') ?: '';
    $url = parse_url($source);
    if (!$url || !isset($url['host']) || !in_array(strtolower($url['host']), array('nwdelectricity.ru','www.nwdelectricity.ru'), true)) {
        return 'https://nwdelectricity.ru/';
    }
    return 'https://nwdelectricity.ru'.($url['path'] ?? '/');
}

function nwdFeedbackLead($id, $siteId) {
    $row = DB::query_fetch_array('SELECT id,created,url FROM {feedback} WHERE id=%d', $id);
    $row['form'] = nwdFeedbackText(DB::query_result('SELECT [name] FROM {site} WHERE id=%d', $siteId)) ?: 'Обратная связь';
    $lang = (int)_LANG;
    $row['fields'] = DB::query_fetch_all("SELECT p.name$lang label,p.type,CASE WHEN p.type IN ('select','multiple','radio') THEN COALESCE(o.name$lang,v.value) WHEN p.type='checkbox' THEN IF(v.value='1','Да','Нет') ELSE v.value END value FROM {feedback_param_element} v JOIN {feedback_param} p ON p.id=v.param_id LEFT JOIN {feedback_param_select} o ON o.param_id=v.param_id AND o.id=v.value AND o.trash='0' WHERE v.element_id=%d AND v.trash='0' ORDER BY p.sort,v.id", $id);
    foreach ($row['fields'] as &$field) {
        $field['label'] = nwdFeedbackText($field['label']);
        $field['value'] = nwdFeedbackText($field['value']);
    }
    unset($field);
    $document=nwdDocumentRequestContext($row['url']);
    if ($document) {
        $row['form']='Запрос документа';
        $row['fields'][]=array('label'=>'Документ','type'=>'text','value'=>$document['title']);
        $row['url']=$document['url'];
    }
    require_once __DIR__.'/phase-neutral.php';
    require_once __DIR__.'/rcd.php';
    require_once __DIR__.'/power-tools.php';
    return nwdPowerToolsLead(nwdRcdLead(nwdPhaseNeutralLead($row)));
}

function nwdFeedbackEscape($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nwdFeedbackEmail(array $lead) {
    $rows = '';
    foreach ($lead['fields'] as $field) {
        $value = nl2br(nwdFeedbackEscape($field['value']));
        if ($field['type']==='phone') {
            $number=preg_replace('/[^0-9+]/','',$field['value']);
            if ($number!=='') { $value='<a href="tel:'.nwdFeedbackEscape($number).'" style="color:#171815;text-decoration:underline">'.$value.'</a>'; }
        } elseif ($field['type']==='email' && filter_var($field['value'], FILTER_VALIDATE_EMAIL)) {
            $value='<a href="mailto:'.nwdFeedbackEscape($field['value']).'" style="color:#171815;text-decoration:underline">'.$value.'</a>';
        }
        $rows.='<tr><td style="padding:12px 0 4px;color:#64665e;font-size:13px;line-height:20px">'.nwdFeedbackEscape($field['label']).'</td></tr>'
            .'<tr><td style="padding:0 0 16px;color:#171815;font-size:17px;line-height:26px;overflow-wrap:anywhere">'.($value ?: '—').'</td></tr>';
    }
    $date=(new DateTimeImmutable('@'.$lead['created']))->setTimezone(new DateTimeZone('Europe/Moscow'))->format('d.m.Y · H:i').' МСК';
    ob_start();
    require __DIR__.'/views/feedback.mail.php';
    return ob_get_clean();
}

function nwdFeedbackCrm(array $lead) {
    if (getenv('NWD_LOCAL') === '1') { return; }
    $file=ABSOLUTE_PATH.'custom/energo/themes/functions/btr.php';
    if (!is_file($file)) { return; }
    $person=array('NAME'=>'Клиент','EMAIL'=>array(),'PHONE'=>array());
    $comments=array();
    foreach ($lead['fields'] as $field) {
        if ($field['type']==='phone') { $person['PHONE'][]=array('VALUE'=>$field['value'],'VALUE_TYPE'=>'WORK'); }
        elseif ($field['type']==='email') { $person['EMAIL'][]=array('VALUE'=>$field['value'],'VALUE_TYPE'=>'WORK'); }
        elseif (preg_match('/имя|фио|фамил/ui',$field['label'])) { $person['NAME']=$field['value']; }
        $comments[]=$field['label'].': '.$field['value'];
    }
    $person+=array('TITLE'=>$lead['form'].' · Заявка №'.$lead['id'].' с сайта nwdelectricity.ru',
        'COMMENTS'=>implode("\n",$comments), 'SOURCE_ID'=>'WEB', 'ASSIGNED_BY_ID'=>24,'OPENED'=>'Y');
    try {
        if (!defined('NWD_ACCEPTED_FEEDBACK')) { define('NWD_ACCEPTED_FEEDBACK', true); }
        require_once $file;
        $result=sendToBitrix24('crm.lead.add',array('fields'=>$person));
        if ($result['httpCode']!==200 || empty($result['response']['result'])) {
            error_log('NWD feedback: CRM did not accept lead #'.(int)$lead['id']);
        }
    } catch (Throwable $error) { error_log('NWD feedback: CRM unavailable for lead #'.(int)$lead['id']); }
}
