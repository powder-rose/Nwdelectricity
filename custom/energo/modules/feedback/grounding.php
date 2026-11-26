<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }

function nwdGroundingCopy() {
    static $copy;
    if (!$copy) { $copy=require ABSOLUTE_PATH.'custom/energo/themes/grounding_content.php'; }
    return $copy;
}

function nwdGroundingFields(array $params) {
    $fields=array();
    foreach ($params as $param) {
        $type=$param['type'];
        if (in_array($type,array('phone','email','textarea'),true) && !isset($fields[$type])) { $fields[$type]=$param; }
        if ($type==='text' && (!isset($fields['name']) || preg_match('/имя|фио|фамил/ui',$param['name']))) { $fields['name']=$param; }
    }
    return $fields;
}

/** Restricts this mode to the four existing native fields. No global form configuration changes. */
function nwdGroundingValidate(array &$params, array &$post, array &$errors) {
    $copy=nwdGroundingCopy();$fields=nwdGroundingFields($params);
    if (count($fields)!==4) { $errors[0]=$copy['errors']['failure'];return null; }
    require_once ABSOLUTE_PATH.'custom/energo/themes/laboratory_links.php';
    $active=nwdLaboratoryActivePaths(array('/'.$copy['path'].'/'));
    if (empty($active['/'.$copy['path'].'/'])) { $errors[0]=$copy['errors']['failure'];return null; }
    foreach ($fields as $type=>&$param) {
        $key='p'.$param['id'];$value=$post[$key] ?? '';
        $valid=is_string($value) && mb_check_encoding($value,'UTF-8') && !preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/',$value);
        if ($valid) {
            $value=trim($value);
            $valid=mb_strlen($value,'UTF-8')<=($type==='textarea' ? 4000 : ($type==='name' ? 100 : 254));
        }
        if ($type==='phone') {
            $valid=$valid && strlen($value)<=40 && preg_match('/^\+?[0-9 ()\-]+$/D',$value) && strlen(preg_replace('/\D/','',$value))>=10 && strlen(preg_replace('/\D/','',$value))<=15;
        } elseif ($type==='email' && $value!=='') { $valid=$valid && filter_var($value,FILTER_VALIDATE_EMAIL); }
        if (!$valid) { $errors[$key]=$type==='phone' ? ($value==='' ? $copy['errors']['emptyPhone'] : $copy['errors']['phone']) : ($type==='email' ? $copy['errors']['email'] : $copy['errors']['failure']); }
        $param['required']=$type==='phone';
        if ($valid) { $post[$key]=$value; }
    }
    unset($param);
    $params=array_values($fields);
    if ($errors) { return null; }
    // Attribution is bounded, allowlisted and stored in the native message field, not in a URL.
    $attribution=$post['nwd_attribution'] ?? '{}';
    if (!is_string($attribution) || strlen($attribution)>4096) { $errors[0]=$copy['errors']['failure'];return null; }
    $values=json_decode($attribution,true);
    if (!is_array($values)) { $errors[0]=$copy['errors']['failure'];return null; }
    $lines=array();
    foreach (array('utm_source','utm_medium','utm_campaign','utm_content','utm_term','yclid','gclid') as $key) {
        if (!isset($values[$key])) { continue; }
        $v=$values[$key];
        if (!is_string($v) || !mb_check_encoding($v,'UTF-8') || mb_strlen($v,'UTF-8')>256 || preg_match('/[\x00-\x1f\x7f]/',$v)) { $errors[0]=$copy['errors']['failure'];return null; }
        if ($v!=='') { $lines[]=$key.': '.$v; }
    }
    if ($lines) { $post['p'.$fields['textarea']['id']].="\n\nРекламный источник:\n".implode("\n",$lines); }
    return array('source'=>'https://nwdelectricity.ru/'.$copy['path'].'/');
}

function nwdGroundingLead(array $lead) {
    if (html_entity_decode($lead['url'],ENT_QUOTES,'UTF-8')!=='https://nwdelectricity.ru/services/proverka-soprotivleniya-zazemliteley/') { return $lead; }
    $lead['form']='Измерение сопротивления заземляющих устройств';
    foreach ($lead['fields'] as &$field) { if ($field['type']==='textarea') { $field['label']='Объект и объём работ'; } }
    unset($field);
    $lead['fields'][]=array('label'=>'Услуга','type'=>'text','value'=>'Измерение сопротивления заземляющих устройств');
    return $lead;
}
