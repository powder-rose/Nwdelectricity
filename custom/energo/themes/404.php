<?php

/**
 * Ошибка 404. Страница не найдена
 *
 * Шаблон оформления страниц 404 (Не найдено)
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (!defined('DIAFAN')) {
	$path = __FILE__;
	while (!file_exists($path . '/includes/404.php')) {
		$parent = dirname($path);
		if ($parent == $path) exit;
		$path = $parent;
	}
	include $path . '/includes/404.php';
}
?>
<!DOCTYPE html>
<html lang="ru" prefix="og: http://ogp.me/ns#">

<insert name="show_include" file="head" />

<body class="page-404">
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
				<div class="container">

					<div class="page-not-found">
						<h1 class="page-title">Похоже что-то пошло не так...</h1>
						<div class="page-not-found__description">
							4
							<div class="page-not-found__icon">
								<img class="page-not-found__decor" src="/custom/energo/images/ico-power-line.png" alt="">
								<img src="/custom/energo/images/ico-power-socket.svg" alt="">
							</div>
							4
						</div>
						<p class="page-not-found__text">К сожалению мы не можем найти страницу которую вы ищите <br>Обновите страницу или попробуйте позже</p>
						<div class="page-not-found__links">
							<a class="page-not-found__link" href="/">
								<svg class="page-not-found__link-icon" width="36" height="34" viewBox="0 0 36 34" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M35.2535 16.7271L19.9101 1.39186L18.8816 0.363427C18.6473 0.130646 18.3304 0 18.0001 0C17.6698 0 17.3529 0.130646 17.1185 0.363427L0.746686 16.7271C0.506573 16.9662 0.316807 17.2511 0.18859 17.5648C0.0603737 17.8785 -0.00369539 18.2147 0.000164591 18.5536C0.016048 19.9513 1.17951 21.0671 2.57725 21.0671H4.26487V34H31.7353V21.0671H33.4586C34.1376 21.0671 34.777 20.8011 35.2574 20.3206C35.494 20.0848 35.6815 19.8044 35.8089 19.4956C35.9364 19.1869 36.0013 18.8559 36 18.5219C36 17.8468 35.7339 17.2075 35.2535 16.7271ZM20.2238 31.141H15.7764V23.0406H20.2238V31.141ZM28.8763 18.2082V31.141H22.7651V22.0876C22.7651 21.2101 22.0543 20.4993 21.1768 20.4993H14.8234C13.9458 20.4993 13.235 21.2101 13.235 22.0876V31.141H7.12389V18.2082H3.31186L18.004 3.52815L18.9213 4.4454L32.6922 18.2082H28.8763Z" fill="black"></path>
								</svg>
								Вернуться на главную</a>
							<a class="page-not-found__link" href="/services/">Перейти к услугам</a>
						</div>
					</div>

					<!--noindex-->
					<div class="container">

						<insert name="show_block" module="menu" id="5" template="clients" />

					</div>
					<!--/noindex-->
				</div>
			</div>
		</main>

		<insert name="show_include" file="footer" />

	</div>

	<insert name="show_include" file="modal" />

	<insert name="show_include" file="scripts" />
<script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
</body>

</html>