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


<header class="page-header">
	<div class="container">
		<div class="page-header__row">

			<?php
			if ($this->diafan->_site->id == 1) {
				echo '<span class="logo logo--header">';
			} else {
				echo '<a href="/" class="logo logo--header">';
			}
			?>
			<img class="logo__icon" src="/custom/energo/images/pic-logo.png" alt="" />
			<img class="logo__iconDesc" src="/custom/energo/images/dist/logo.svg" alt="" />
			<p class="logo__description logo__description--header">Обслуживание электрооборудования</p>
			<?php
			if ($this->diafan->_site->id == 1) {
				echo '</span>';
			} else {
				echo '</a>';
			}
			?>

			<nav class="navigation">
				<div class="navigation__inner">

					<insert name="show_block" module="menu" id="1" template="header" />

					<insert name="show_block" module="menu" id="1" template="mob" />

					<!-- <a class="portal-link" href="/portal-postavschikov/" title="Мы на портале поставщиков">
						<span class="visually-hidden">Перейти в портал</span>
					</a> -->

					<div class="contact-phone contact-phone__page-header js-contact-phone">
						<p class="contact-phone__number contact-phone__number--header">
							<a href="tel:+78002012069" rel="nofollow">
								<insert name="show_block" module="site" id="1">
							</a>
						</p>
						<p class="contact-phone__mail contact-phone__mail--header">
							<a href="mailto:<insert name=" show_block" module="site" id="2" template="nohtml">" rel="nofollow">
								<insert name=" show_block" module="site" id="2">
							</a>
						</p>
					</div>

				</div>
			</nav>
			<div class="header-data-mob">
				<!-- <a class="portal-link" href="/portal-postavschikov/" rel="nofollow">
					<span class="visually-hidden">Перейти в портал</span>
				</a> -->
				<a class="mob-mail" href="mailto:<insert name=" show_block" module="site" id="2" template="nohtml">" rel="nofollow">
					<span class="visually-hidden">Написать письмо</span>
					<svg class="mob-mail__icon" width="15" height="10" viewBox="0 0 15 10" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0.306958 0.811323C2.31284 2.36103 5.83254 5.08743 6.86702 5.93753C7.00589 6.05227 7.15484 6.1106 7.30947 6.1106C7.46379 6.1106 7.61248 6.05281 7.75104 5.93863C8.78641 5.08769 12.3061 2.36103 14.312 0.811323C14.4369 0.715025 14.4559 0.54576 14.3548 0.42803C14.1211 0.155983 13.7726 0 13.3991 0H1.21981C0.846351 0 0.497841 0.155984 0.26414 0.428057C0.163032 0.54576 0.182072 0.715025 0.306958 0.811323Z" fill="#0063A7"></path>
						<path d="M14.4386 1.6509C14.3307 1.60506 14.2037 1.62105 14.1139 1.69105C11.8894 3.41135 9.05036 5.61642 8.15712 6.35069C7.65578 6.76354 6.95998 6.76354 6.45747 6.35014C5.50537 5.56757 2.3172 3.09506 0.501313 1.69102C0.41091 1.62103 0.283655 1.60559 0.17661 1.65087C0.0689941 1.69647 0 1.79467 0 1.90289V8.8883C0 9.50109 0.546215 9.99938 1.21794 9.99938H13.3973C14.069 9.99938 14.6152 9.50109 14.6152 8.8883V1.90289C14.6152 1.79467 14.5462 1.69621 14.4386 1.6509Z" fill="#0063A7"></path>
					</svg>
				</a>
				<button class="page-header__menu-trigger burger">
					<span class="visually-hidden">Открыть меню</span>
					<span class="burger__line"></span>
				</button>
				<button class="page-header__close">
					<span class="visually-hidden">Закрыть меню</span>
					<span class="page-header__close-icon"></span>
				</button>
				<a class="header-data-mob__phone" href="tel:+78002012069" rel="nofollow">
					<insert name=" show_block" module="site" id="1">
				</a>
			</div>
		</div>
	</div>
</header>