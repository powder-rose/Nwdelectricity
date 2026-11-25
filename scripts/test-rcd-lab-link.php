<?php
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
define('DIAFAN',true);require __DIR__.'/rcd-lab-link.php';
$count=0;function linkCheck($ok) { global $count;if (!$ok) { throw new RuntimeException('Laboratory backlink preservation failed'); }$count++; }
$source='<h2>Приемо-сдаточные испытания</h2><ul><li class="square">Срабатывание УЗО;</li><li>Сопротивление изоляции проводки;</li></ul>';
$expected=str_replace('Срабатывание УЗО','<a href="/services/proverka-uzo/">Срабатывание УЗО</a>',$source);
linkCheck(nwdRcdLabLink($source)===$expected);
linkCheck(nwdRcdLabLink($expected)===$expected);
$encoded=htmlspecialchars($source,ENT_QUOTES,'UTF-8');
linkCheck(nwdRcdLabLink($encoded)===htmlspecialchars($expected,ENT_QUOTES,'UTF-8'));
linkCheck(nwdRcdLabLink(htmlspecialchars($expected,ENT_QUOTES,'UTF-8'))===htmlspecialchars($expected,ENT_QUOTES,'UTF-8'));
foreach (array('<p>Другой текст.</p>',$source.$source,str_replace('Срабатывание УЗО','<a href="/old/">Срабатывание УЗО</a>',$source),'<p>Срабатывание УЗО</p>') as $invalid) {
    $refused=false;try { nwdRcdLabLink($invalid); } catch (RuntimeException $error) { $refused=true; }linkCheck($refused);
}
echo 'RCD_LAB_LINK_OK='.$count."\n";
