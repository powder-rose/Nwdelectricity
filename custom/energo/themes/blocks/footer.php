<?php
if (!defined('DIAFAN')) {
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


<footer class="page-footer" itemscope itemtype="http://schema.org/Organization">
	<div class="page-footer__main">
		<div class="container">
			<div class="page-footer__row">

				<?php
				if ($this->diafan->_site->id == 1) {
					echo '<span class="logo page-footer__logo">';
				} else {
					echo '<a href="/" class="logo page-footer__logo">';
				}
				?>
				<img class="logo__icon" src="/custom/energo/images/dist/logo_white.svg" alt="" itemprop="image">
				<p class="logo__description">Обслуживание электрооборудования</p>
				<?php
				if ($this->diafan->_site->id == 1) {
					echo '</span>';
				} else {
					echo '</a>';
				}
				?>

				<div style="display:none">
					<span itemprop="name">Спецэнергоразвитие</span>
				</div>

				<insert name="show_block" module="menu" id="4" template="footer" />

				<div class="page-footer__contacts footer-contacts">
					<div class="contact-phone footer-contacts__item footer-contacts__item--phone">
						<p class="contact-phone__number">
							<a href="tel:+78002012069" rel="nofollow">
								<span itemprop="telephone">
									<insert name=" show_block" module="site" id="1">
								</span>
							</a>
						</p>
						<p class="contact-phone__request-service">
							<a data-fancybox data-src="#modal" href="javascript:;" rel="nofollow">Заявка на обслуживание</a>
						</p>
					</div>
					<a class="footer-contacts__item footer-contacts__item--mail" href="mailto:<insert name=" show_block" module="site" id="2" template="nohtml">" rel="nofollow">
						<span itemprop="email">
							<insert name="show_block" module="site" id="2">
						</span>
					</a>
					<span class="footer-contacts__item footer-contacts__item--location">
						<insert name="show_block" module="site" id="3">
					</span>
				</div>

			</div>
		</div>
	</div>
	<div class="copyright">
		<div class="container copyright__row">
			<p>© 2010-<?php echo date('Y'); ?> Copyright ООО «Спецэнергоразвитие»</p>
			<p><a href="/privacy/">Политика конфиденциальности</a></p>

		</div>
	</div>
<script charset="utf-8" type="text/javascript" src="https://cp.callback-free.com/api/js/form-builder.js" data-key="41m9pVGF"></script>
</footer>