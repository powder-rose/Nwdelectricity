<?php
/** Exercise the complete public-response gate without network, database or form submissions. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
// Use the real native parser and shared page blocks with the existing isolated adapters.
$argv=array(__FILE__,'--html');ob_start();require __DIR__.'/test-cables-shell.php';$html=ob_get_clean();
if(strpos($html,'<body')===false){throw new RuntimeException('Native shell fixture failed');}
define('NWD_INS_PUBLIC_TEST',true);require __DIR__.'/check-cables-public.php';
$counter='<div><img src="/tracking-pixel.gif" alt=""></div>';
$withCounter=str_replace('</head>',$counter.'</head>',$html);
// Demonstrate the production failure: HTML4 repair loses explicit body attributes.
$dom=new DOMDocument;$prev=libxml_use_internal_errors(true);$dom->loadHTML($withCounter,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($prev);
if(strpos($dom->getElementsByTagName('body')->item(0)->getAttribute('class'),'page-cables')!==false){throw new RuntimeException('Tracking-pixel regression fixture no longer reproduces');}
$cases=array(
    array('native desktop',$html,true),
    array('libxml 2.7.8 heading whitespace',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1 id="cbl-title">Испытания<br> силовых<br><span>кабельных линий</span></h1>',$html,1),true),
    array('heading line break whitespace',preg_replace('~<h1\b[^>]*>.*?</h1>~s',"<h1>Испытания\n<br>\tсиловых<br><span>кабельных линий</span></h1>",$html,1),true),
    array('heading nonbreaking spaces',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1>Испытания&nbsp;силовых&nbsp;кабельных&nbsp;линий</h1>',$html,1),true),
    array('joined words without line break',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1>Испытания силовых<span>кабельных линий</span></h1>',$html,1),false),
    array('changed heading word',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1>Измерение<br>напряжения<br><span>заземляющих устройств</span></h1>',$html,1),false),
    array('changed heading order',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1>Измерение<br>заземляющих устройств<br><span>сопротивления</span></h1>',$html,1),false),
    array('duplicate heading',preg_replace('~</h1>~','</h1><h1>Испытания силовых кабельных линий</h1>',$html,1),false),
    array('head tracking pixel',$withCounter,true),
    array('head no-script counter',str_replace('</head>','<noscript>'.$counter.'</noscript></head>',$html),true),
    array('client script body literal',str_replace('</head>','<script>var markup="<body class=example>";</script></head>',$html),true),
    array('comment body literal',str_replace('</head>','<!-- <body class="example"> --></head>',$html),true),
    array('missing laboratory class',str_replace('page-cables','page-old',$withCounter),false),
    array('partial class',str_replace('page-cables','page-cables-old',$withCounter),false),
    array('diagnostic body color',str_replace('<body class=','<body style="color:red" class=',$withCounter),false),
    array('duplicate body',str_replace('</body>','<body class="page-cables"></body></body>',$html),false),
    array('duplicate main',str_replace('</main>','</main><main></main>',$html),false),
    array('missing body with comment imitation',preg_replace('~<body\b[^>]*>~','<!-- <body class="page-cables"> -->',$html,1),false),
    array('missing body with script imitation',preg_replace('~<body\b[^>]*>~','<script>var markup="<body class=page-cables>";</script>',$html,1),false),
    array('CMS error',$html.'[ERROR#4]',false),
    array('raw PHP',$html.'<?php echo 1; ?>',false),
    array('wrong H1',preg_replace('~<h1\b[^>]*>.*?</h1>~s','<h1>Wrong</h1>',$html,1),false),
    array('missing section',preg_replace('~<section\b[^>]*>.*?</section>~s','',$html,1),false),
    array('no form',str_replace('data-fn-form','data-absent-form',$html),false),
    array('no consent',str_replace('name="nwd_consent"','name="absent"',$html),false),
    array('missing FAQ',preg_replace('~<details\b[^>]*>.*?</details>~s','',$html,1),false),
    array('noindex',str_replace('</head>','<meta name="robots" content="noindex"></head>',$html),false),
    array('wrong canonical',str_replace('rel="canonical"','rel="alternate"',$html),false),
    array('missing structured data',str_replace('application/ld+json','application/json',$html),false),
);
$checked=0;
foreach($cases as $case){
    $accepted=true;
    try {nwdCablesValidatePublicResponse($case[1],$copy);} catch(RuntimeException $error){$accepted=false;}
    if($accepted!==$case[2]){throw new RuntimeException('Public gate failed: '.$case[0]);}
    $checked++;
}
echo 'CABLES_PUBLIC_BEHAVIOR_OK='.$checked."\n";
