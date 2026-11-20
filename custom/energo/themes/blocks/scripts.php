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
<link rel="stylesheet" href="/custom/energo/css/lead-forms.css?v=consent">
<script src="/custom/energo/js/lead-forms.js?v=consent"></script>


   
<!-- Yandex.Metrika counter -->

<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=114341229', 'ym');

    ym(114341229, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>

<noscript><div><img src="https://mc.yandex.ru/watch/114341229" style="position:absolute; left:-9999px;" alt="" /></div></noscript>

<!-- /Yandex.Metrika counter -->

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