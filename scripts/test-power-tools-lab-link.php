<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
define('DIAFAN',true);require __DIR__.'/power-tools-lab-link.php';
$count=0;function toolsLinkCheck($ok){global $count;if(!$ok){throw new RuntimeException('Portable-tools catalogue preservation failed');}$count++;}
$source='<h2>Лаборатория</h2><p>Исходное описание.</p><ul><li><a href="/services/proverka-uzo/">Срабатывание УЗО</a>;</li></ul>';
$after=nwdPowerToolsLabLink($source);
toolsLinkCheck(strpos($after,$source)===0);
toolsLinkCheck(substr_count($after,'/services/ispytanie-elektroinstrumenta/')===1);
toolsLinkCheck(strpos($after,'Кабельные линии и диагностика')!==false);
toolsLinkCheck(nwdPowerToolsLabLink($after)===$after);
$encoded=htmlspecialchars($source,ENT_QUOTES,'UTF-8');$afterEncoded=nwdPowerToolsLabLink($encoded);
toolsLinkCheck(strpos($afterEncoded,$encoded)===0 && strpos($afterEncoded,'&lt;section')!==false);
toolsLinkCheck(nwdPowerToolsLabLink($afterEncoded)===$afterEncoded);
$group='<h2>Услуги электроизмерительной лаборатории</h2><h3>Кабельные линии и диагностика</h3><ul><li>Другая услуга</li></ul>';
$expanded=nwdPowerToolsLabLink($group);
toolsLinkCheck(substr_count($expanded,'<h3>')===1 && strpos($expanded,'Другая услуга')!==false && substr_count($expanded,'<li>')===2);
foreach(array('', '<h2>Услуги электроизмерительной лаборатории</h2>', '<a href="/services/ispytanie-elektroinstrumenta/">Старая подпись</a>') as $bad){$refused=false;try{nwdPowerToolsLabLink($bad);}catch(RuntimeException $e){$refused=true;}toolsLinkCheck($refused);}
echo 'POWER_TOOLS_LAB_LINK_OK='.$count."\n";
