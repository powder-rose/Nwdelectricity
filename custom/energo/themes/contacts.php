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

	<div class="page-wrapper">

		<insert name="show_include" file="header" />

		<main class="page-main">

			<div class="custom-bg">

				<insert name="show_breadcrumb" current="true" />

				<div class="container">
					<div class="contacts-block">
						<h1 class="page-title">
							<insert name="show_h1" />
						</h1>
						<div class="contacts-block__row">
							<div class="contacts-block__form">
								<insert name="show_form" module="feedback" site_id="166" template="t1" data-btn="Получить консультацию" />
							</div>
							<div class="contacts-block__map">
								<!--<section id="map" class="map" style="background-image: url('https://yandex.ru/maps/?um=constructor%3Ad5f287842b667313c795e7cec5d4faa8ef8b19e74cefb65926421b779017880e&source=constructorLink');"></section>-->
								<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ad5f287842b667313c795e7cec5d4faa8ef8b19e74cefb65926421b779017880e&amp;width=750&amp;height=582&amp;lang=ru_RU&amp;scroll=true"></script>
							</div>
						</div>
						<div class="contacts-block__detail">
							<div class="contacts-block__content">
								<div class="contacts-block__content-wrapper">
									<ul class="contacts-block__list">
										<li class="contacts-block__item">
											<div class="contact-phone">
												<p class="contact-phone__number">
													<a href="tel:+78002012069">
														<insert name="show_block" module="site" id="1">
													</a>
												</p>
												<p class="contact-phone__request-service">
													<a data-fancybox data-src="#modal" href="javascript:;" rel="nofollow">Заявка на обслуживание</a>
												</p>
											</div>
										</li>
										<li class="contacts-block__item">
											<div class="working-hours">
												<p class="working-hours__time">
													<insert name="show_block" module="site" id="4">
												</p>
												<p class="working-hours__orders-time">
													Заказы выполняются <br>
													<mark class="mark">Без выходных</mark>
												</p>
											</div>
										</li>
										<li class="contacts-block__item contacts-block__item--location">
											<span class="footer-contacts__item footer-contacts__item--location">
												<insert name="show_block" module="site" id="3" template="nohtml">
											</span>
										</li>
										<li class="contacts-block__item">
											<a class="footer-contacts__item footer-contacts__item--mail" href="mailto:<insert name=" show_block" module="site" id="2" template="nohtml">">
												<insert name="show_block" module="site" id="2">
											</a>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<insert name="show_text" />

					</div>
				</div>

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