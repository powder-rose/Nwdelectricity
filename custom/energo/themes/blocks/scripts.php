<?php
if (! defined('DIAFAN'))
{
	$path = __FILE__; $i = 0;
	while(! file_exists($path.'/includes/404.php'))
	{
		if($i == 10) exit; $i++;
		$path = dirname($path);
	}
	include $path.'/includes/404.php';
}
?>

<?php
	$this->diafan->_site->js_view[] = BASE_PATH . Custom::path('js/scripts.js');
	$this->diafan->_site->js_view[] = BASE_PATH . Custom::path('js/main.js');
?>

<insert name="show_js">


   <!-- Yandex.Metrika counter -->
   <script type="text/javascript">
   // <![CDATA[
   (function(d, w, c) {
      (w[c] = w[c] || []).push(function() {
         try {
            w.yaCounter44744422 = new Ya.Metrika({
               id: 44744422,
               clickmap: true,
               trackLinks: true,
               accurateTrackBounce: true,
               webvisor: true
            });
         } catch (e) {}
      });
      var n = d.getElementsByTagName("script")[0],
         s = d.createElement("script"),
         f = function() {
            n.parentNode.insertBefore(s, n);
         };
      s.type = "text/javascript";
      s.async = true;
      s.src = "https://mc.yandex.ru/metrika/watch.js";
      if (w.opera == "[object Opera]") {
         d.addEventListener("DOMContentLoaded", f, false);
      } else {
         f();
      }
   })(document, window, "yandex_metrika_callbacks");
   // ]]>
   </script>
   <noscript>
      <div><img src="https://mc.yandex.ru/watch/44744422" style="position:absolute; left:-9999px;" alt="" /></div>
   </noscript><!-- /Yandex.Metrika counter -->

   <!-- Global site tag (gtag.js) - Google Analytics -->
   <script async src="https://www.googletagmanager.com/gtag/js?id=UA-59045479-7"></script>
   <script>
   window.dataLayer = window.dataLayer || [];

   function gtag() {
      dataLayer.push(arguments);
   }
   gtag('js', new Date());

   gtag('config', 'UA-59045479-7');
   </script>