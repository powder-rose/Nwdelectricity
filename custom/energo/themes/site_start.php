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

<body class="page-home">
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

         <div class="fscreen fscreen--mainBg">
            <div class="container">
               <div class="fscreen__wrap fscreen__wrap--mainBg">
                  <div class="fscreen__content">

                     <insert name="show_dynamic" module="site" id="4">

                  </div>
               </div>
            </div>
         </div>

         <div class="custom-bg">

            <div class="container">

               <section class="group-content group-content--technical-maintenance">
                  <div class="group-content__row">
                     <div class="group-content__text content">
                        <h1 class="section-title">
                           <insert name="show_h1" />
                        </h1>
                        <div class="spoiler-content">

                           <insert name="show_text" />

                        </div>
                     </div>
                     <div class="group-content__form">
                        <insert name="show_form" module="feedback" site_id="31" template="t1" />
                     </div>
                  </div>
               </section>

               <insert name="show_block" module="site" id="5" />

               <insert name="show_block" module="site" id="8" />

               <insert name="show_block" module="site" id="10" />

               <insert name="show_block" module="site" id="11" />

               <insert name="show_block" module="site" id="12" />

            </div>

            <insert name="show_block" module="site" id="13" />

            <div class="container">

               <insert name="show_block" module="site" id="14" />

            </div>

            <insert name="show_block" module="site" id="15" />

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