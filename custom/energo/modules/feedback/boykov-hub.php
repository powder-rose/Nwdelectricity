<?php
// Loaded by the existing DIAFAN feedback handler, inside its transaction.
if (!defined('DIAFAN') && PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function nwdHubPayload(array $lead, $requestId = '') {
    $payload = array(
        'eventId' => preg_match('/^[a-f0-9]{32}$/D',(string)$requestId) ? 'request-'.$requestId : 'feedback-'.(int)$lead['id'],
        'createdAt' => gmdate('Y-m-d\TH:i:s.000\Z', (int)$lead['created']),
        'name' => '', 'phone' => '', 'email' => '',
        'service' => isset($lead['form']) ? (string)$lead['form'] : 'Обратная связь',
        'message' => '', 'pageUrl' => '',
        'form' => isset($lead['form']) ? (string)$lead['form'] : 'Обратная связь',
        'fields' => array()
    );
    foreach ($lead['fields'] as $field) {
        $label = trim((string)$field['label']);
        $value = trim((string)$field['value']);
        $type = isset($field['type']) ? $field['type'] : '';
        $payload['fields'][] = array('label' => $label !== '' ? $label : 'Поле формы', 'value' => $value);
        if ($payload['phone'] === '' && ($type === 'phone' || preg_match('/телефон|phone/ui', $label))) $payload['phone'] = $value;
        if ($payload['email'] === '' && ($type === 'email' || preg_match('/e.?mail|почт/ui', $label))) $payload['email'] = $value;
        if ($payload['name'] === '' && preg_match('/имя|фио|фамил/ui', $label)) $payload['name'] = $value;
        if (preg_match('/комментар|сообщени|задач|описани/ui', $label) && $value !== '') $payload['message'] .= ($payload['message'] === '' ? '' : "\n").$value;
    }
    if ($requestId !== '') $payload['fields'][] = array('label'=>'Номер заявки на сайте','value'=>(string)$lead['id']);
    $url = isset($lead['url']) ? parse_url($lead['url']) : false;
    if ($url && isset($url['host']) && in_array(strtolower($url['host']), array('nwdelectricity.ru','www.nwdelectricity.ru'), true)) {
        $payload['pageUrl'] = 'https://nwdelectricity.ru'.(isset($url['path']) ? $url['path'] : '/');
    }
    return $payload;
}

function nwdHubEncode(array $payload) {
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) throw new RuntimeException('HUB_PAYLOAD_ENCODING_FAILED');
    return $json;
}

function nwdHubEnqueue(array $lead, $requestId = '') {
    $payload = nwdHubPayload($lead, $requestId);
    $json = nwdHubEncode($payload);
    $hash = hash('sha256', $json);
    // No network request belongs in the public form's database transaction.
    DB::query("INSERT IGNORE INTO {boykov_lead_outbox} (event_id,payload,payload_hash,queued_at) VALUES ('%s','%s','%s',%d)", $payload['eventId'], $json, $hash, time());
    $saved = DB::query_fetch_array("SELECT payload_hash FROM {boykov_lead_outbox} WHERE event_id='%s'", $payload['eventId']);
    if (!$saved || !hash_equals($saved['payload_hash'], $hash)) throw new RuntimeException('HUB_OUTBOX_NOT_SAVED');
}

function nwdHubRequest($endpoint, $token, array $payload) {
    $url = parse_url($endpoint);
    if (!$url || $url['scheme'] !== 'https' || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])) throw new RuntimeException('HUB_ENDPOINT_INVALID');
    $curl = curl_init($endpoint);
    curl_setopt_array($curl, array(
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => nwdHubEncode($payload),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Authorization: Bearer '.$token),
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2
    ));
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return array('status' => $status, 'body' => is_string($body) ? json_decode($body, true) : null);
}
