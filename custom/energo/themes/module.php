<?php

if (!defined("DIAFAN")) {
	$path = __FILE__;
	$i = 0;
	while (!file_exists($path . '/includes/404.php')) {
		if ($i == 10) exit;
		$i++;
		$path = dirname($path);
	}
	include $path . '/includes/404.php';
}
?>
<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">

<insert name="show_include" file="head" />

<body class="page-inn">
   <!-- Rating Mail.ru counter -->
   <script type="text/javascript">
   var _tmr = window._tmr || (window._tmr = []);
   _tmr.push({
      id: "3232969",
      type: "pageView",
      start: (new Date()).getTime()
   });
   (function(d, w, id) {
      if (d.getElementById(id)) return;
      var ts = d.createElement("script");
      ts.type = "text/javascript";
      ts.async = true;
      ts.id = id;
      ts.src = "https://top-fwz1.mail.ru/js/code.js";
      var f = function() {
         var s = d.getElementsByTagName("script")[0];
         s.parentNode.insertBefore(ts, s);
      };
      if (w.opera == "[object Opera]") {
         d.addEventListener("DOMContentLoaded", f, false);
      } else {
         f();
      }
   })(document, window, "topmailru-code");
   </script><noscript>
      <div>
         <img src="https://top-fwz1.mail.ru/counter?id=3232969;js=na" style="border:0;position:absolute;left:-9999px;"
            alt="Top.Mail.Ru" />
      </div>
   </noscript>
   <!-- //Rating Mail.ru counter -->
   <div class="page-wrapper">

      <insert name="show_include" file="header" />

      <main class="page-main">

         <div class="custom-bg">

            <insert name="show_breadcrumb" current="true" />


            <insert name="show_module">


               <!--noindex-->
               <div class="container">

                  <insert name="show_block" module="site" id="9" />

                  <insert name="show_block" module="menu" id="5" template="clients" />

               </div>
               <!--/noindex-->

         </div>

      </main>

      <insert name="show_include" file="footer" />

   </div>

   <insert name="show_include" file="modal" />

   <insert name="show_include" file="scripts" />
   <script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
</body>

</html>