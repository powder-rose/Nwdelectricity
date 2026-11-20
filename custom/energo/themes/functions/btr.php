<?php
// Called only after the feedback action accepts and commits a request.
if (!defined('NWD_ACCEPTED_FEEDBACK')) { http_response_code(404); exit; }

// Конфигурация
define('BITRIX24_WEBHOOK_URL', 'https://b24-ko2ze3.bitrix24.ru/rest/24/6i2nlp93y8yae4w9/');
// Функция для отправки данных в Битрикс24
function sendToBitrix24($method, $data) {
$url = BITRIX24_WEBHOOK_URL . $method;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
curl_setopt($ch, CURLOPT_TIMEOUT, 7);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
'Content-Type: application/json',
'Content-Length: ' . strlen(json_encode($data))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
return [
'httpCode' => $httpCode,
'response' => json_decode($response, true)
];
}
