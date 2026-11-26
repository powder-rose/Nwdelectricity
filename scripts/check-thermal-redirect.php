<?php
/** Validate headers from curl without following a redirect or making another request. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$headers=file_get_contents($argv[1]??'');$expected=$argv[2]??'';
if(!$headers || !preg_match_all('~^HTTP/\S+\s+(\d{3})\b~mi',$headers,$codes) || end($codes[1])!=='301' || !preg_match_all('/^Location:\s*(.*?)\r?$/mi',$headers,$locations) || trim(end($locations[1]))!==$expected){fwrite(STDERR,"THERMAL_REDIRECT_FAILED=Expected one direct 301 to the primary URL\n");exit(1);}
echo "THERMAL_PUBLIC_REDIRECT_OK\n";
