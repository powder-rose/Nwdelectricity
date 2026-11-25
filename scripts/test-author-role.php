<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
define('DIAFAN',true);require dirname(__DIR__).'/redaktsiya/lib/author-role.php';
$checks=0;function roleCheck($ok,$name){global $checks;if(!$ok){throw new RuntimeException($name);}$checks++;}
$cases=array(
 'Руководитель «Спецэнергоразвития»'=>NWD_AUTHOR_ROLE,
 'руководитель «Спецэнергоразвития»'=>NWD_AUTHOR_ROLE,
 'Руководитель "Спецэнергоразвития"'=>NWD_AUTHOR_ROLE,
 'Руководитель ООО «Спецэнергоразвития»'=>NWD_AUTHOR_ROLE,
 'Руководитель «Спецэнергоразвитие»'=>NWD_AUTHOR_ROLE,
 'Руководитель&nbsp;«Спецэнергоразвития»'=>NWD_AUTHOR_ROLE,
 'Руководитель &laquo;Спецэнергоразвития&raquo;'=>'Руководитель ООО &laquo;Спецэнергоразвитие&raquo;',
 'Руководитель &amp;laquo;Спецэнергоразвития&amp;raquo;'=>'Руководитель ООО &amp;laquo;Спецэнергоразвитие&amp;raquo;',
 'Руководитель &#171;Спецэнергоразвития&#187;'=>'Руководитель ООО &laquo;Спецэнергоразвитие&raquo;',
 'Контакты Спецэнергоразвития'=>'Контакты Спецэнергоразвития',
 'Руководитель другой компании'=>'Руководитель другой компании',
 NWD_AUTHOR_ROLE=>NWD_AUTHOR_ROLE
);
foreach($cases as $old=>$expected){$new=nwdReplaceAuthorRole($old);roleCheck($new===$expected,'literal and entity replacement');roleCheck(nwdReplaceAuthorRole($new)===$new,'idempotent replacement');}
$payload=(object)array('seoTitle'=>'Блог Николая Бойкова — Руководитель «Спецэнергоразвития»','content'=>'<p>Руководитель «Спецэнергоразвития».</p>','status'=>'scheduled','scheduledAt'=>'2026-11-26T07:00:00.000Z','slug'=>'test','options'=>(object)array(),'blocks'=>array((object)array('alt'=>'Руководитель «Спецэнергоразвития»')));
$encoded=json_encode($payload);$after=nwdAuthorRolePayload($encoded);$decoded=json_decode($after);
roleCheck($decoded->seoTitle==='Блог Николая Бойкова — '.NWD_AUTHOR_ROLE,'escaped JSON SEO title');
roleCheck($decoded->content==='<p>'.NWD_AUTHOR_ROLE.'.</p>','article content');
roleCheck($decoded->blocks[0]->alt===NWD_AUTHOR_ROLE,'nested image alt');
roleCheck($decoded->status==='scheduled' && $decoded->scheduledAt===$payload->scheduledAt && $decoded->slug==='test','schedule and route preserved');
roleCheck($decoded->options instanceof stdClass,'empty object stays an object');
$plain="{\n  \"title\": \"Другая статья\", \"count\": 3\n}";
roleCheck(nwdAuthorRolePayload($plain)===$plain,'unchanged JSON bytes preserved');
$invalid=false;try{nwdAuthorRolePayload('{broken');}catch(JsonException $e){$invalid=true;}
roleCheck($invalid,'invalid payload is rejected before updates');
echo 'AUTHOR_ROLE_TEXT_OK='.$checks."\n";
