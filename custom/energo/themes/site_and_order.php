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
require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
$nwdAboutPage = NwdEditorialPages::isAbout($this->diafan);
?>
<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">

<insert name="show_include" file="head" />

<body class="page-inn<?php if ($nwdAboutPage) { echo ' page-about page-editorial'; } ?>">
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
	<div class="page-wrapper">

		<insert name="show_include" file="header" />

		<main class="page-main">

			<div class="custom-bg">

				<insert name="show_breadcrumb" current="true" />

				<div class="container"<?php if (!$nwdAboutPage) { echo ' style="padding-bottom: 50px;"'; } ?>>
					<?php echo $nwdAboutPage ? '<insert name="show_about_content" />' : '<insert name="show_text" />'; ?>
				</div>

				<insert name="show_block" module="site" id="15" />

				<!--noindex-->
				<div class="container">

					<insert name="show_block" module="site" id="9" />

					<?php if (!$nwdAboutPage) { echo '<insert name="show_block" module="menu" id="5" template="clients" />'; } ?>

				</div>
				<!--/noindex-->

			</div>

		</main>

		<insert name="show_include" file="footer" />

	</div>

<?php
if ($nwdAboutPage) {
	echo '   <a class="home-callback" href="#modal" data-fancybox data-src="#modal" aria-label="Заказать звонок">
      <span class="home-callback__icon" aria-hidden="true">
         <svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 3.8 8.2 3l2.1 4.5-1.8 1.8a14.8 14.8 0 0 0 6.2 6.2l1.8-1.8 4.5 2.1-.8 3.2c-.2.8-1 1.4-1.8 1.3C10.6 19.6 4.4 13.4 3.7 5.6 3.6 4.8 4.2 4 5 3.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="home-callback__label">Заказать звонок <span aria-hidden="true">↗</span></span>
   </a>

';
}
?>
	<insert name="show_include" file="modal" />


	<insert name="show_include" file="scripts" />
<?php if ($nwdAboutPage) { echo '<script src="/custom/energo/js/editorial-pages.js" defer></script>'; } ?>
<script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
</body>

</html>