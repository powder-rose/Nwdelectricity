<?php
 
if(! defined("DIAFAN"))
{
	$path = __FILE__; $i = 0;
	while(! file_exists($path.'/includes/404.php'))
	{
		if($i == 10) exit; $i++;
		$path = dirname($path);
	}
	include $path.'/includes/404.php';
}
?><!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">

<insert name="show_include" file="head" />

<body>
    <!-- Rating Mail.ru counter -->
<script type="text/javascript">
var _tmr = window._tmr || (window._tmr = []);
_tmr.push({id: "3232969", type: "pageView", start: (new Date()).getTime()});
(function (d, w, id) {
  if (d.getElementById(id)) return;
  var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
  ts.src = "https://top-fwz1.mail.ru/js/code.js";
  var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
  if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
})(document, window, "topmailru-code");
</script><noscript><div>
<img src="https://top-fwz1.mail.ru/counter?id=3232969;js=na" style="border:0;position:absolute;left:-9999px;" alt="Top.Mail.Ru" />
</div></noscript>
<!-- //Rating Mail.ru counter -->
<insert name="show_include" file="header">


 
<div class="content page">
    <div id="breadcrumb"><div class="wrapper"><insert name="show_breadcrumb" current="true"></div></div>
    <div class="wrapper clearfix">
    
            
            
                <h1><insert name="show_h1"></h1>
                <insert name="show_module">
                <insert name="show_text">
            
        
     
    </div>
    <!--noindex-->
    <div class="map"><div class="wrapper text"><insert name="show_block" module="site" id="9"></div></div>			
    <div class="clients_wrapper"><div class="wrapper"><insert name="show_block" module="menu" id="5" template="clients"></div></div>
    <!--/noindex-->
</div>
<insert name="show_include" file="footer">

<insert name="show_include" file="counters">
<script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>
