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

                     <figure class="home-hero-visual" aria-hidden="true">
                        <img src="/custom/energo/images/home-hero-expanded.png" width="1672" height="941" alt="" fetchpriority="high" decoding="async">
                     </figure>

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

            </div>
            <!--/noindex-->

         </div>

      </main>

      <insert name="show_include" file="footer" />

   </div>

   <insert name="show_include" file="modal" />

   <a class="home-callback" href="#modal" data-fancybox data-src="#modal" aria-label="Заказать звонок">
      <span class="home-callback__icon" aria-hidden="true">
         <svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 3.8 8.2 3l2.1 4.5-1.8 1.8a14.8 14.8 0 0 0 6.2 6.2l1.8-1.8 4.5 2.1-.8 3.2c-.2.8-1 1.4-1.8 1.3C10.6 19.6 4.4 13.4 3.7 5.6 3.6 4.8 4.2 4 5 3.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="home-callback__label">Заказать звонок <span aria-hidden="true">↗</span></span>
   </a>

   <insert name="show_include" file="scripts" />
   <script src="/custom/energo/js/home-editorial.js" defer></script>
   <script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
</body>

</html>
