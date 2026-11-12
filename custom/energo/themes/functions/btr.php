<?php
// Конфигурация
define('BITRIX24_WEBHOOK_URL', 'https://b24-ko2ze3.bitrix24.ru/rest/24/6i2nlp93y8yae4w9/');
// Функция для отправки данных в Битрикс24
function sendToBitrix24($method, $data) {
$url = BITRIX24_WEBHOOK_URL . $method;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
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

var_dump($_POST);


// Обработка POST-запроса с формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';
$form_type = $_POST['form_type'] ?? '';
$email = $_POST['email'] ?? '';
// Подготовка данных для отправки
$leadData = [
'fields' => [
'TITLE' => $form_type . 'Заявка с сайта nwdelectricity.ru',
'NAME' => $name,
'EMAIL' => [['VALUE' => $email, 'VALUE_TYPE' => 'WORK']],
'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'WORK']],
'COMMENTS' => $message,
'SOURCE_ID' => 'WEB', // Источник - сайт
'ASSIGNED_BY_ID' => 24, // ID ответственного
'OPENED' => 'Y'
]
];

if(isset($_POST['form_type'])){
// Отправка лида в Битрикс24
$result = sendToBitrix24('crm.lead.add', $leadData);
if ($result['httpCode'] === 200 && isset($result['response']['result'])) {
echo json_encode(['success' => true, 'leadId' => $result['response']['result']]);
} else {
echo json_encode(['success' => false, 'error' => $result['response']['error']]);
}
}
}
?>