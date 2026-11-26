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
// Standalone service shells share this block; the older editorial shells already have a callback.
require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
$nwdCallbackPath = (string) $this->diafan->_site->rewrite;
if ($nwdCallbackPath === '') { $nwdCallbackPath = $this->diafan->_route->link((int) $this->diafan->_site->id); }
$nwdCallbackPath = trim((string) parse_url($nwdCallbackPath, PHP_URL_PATH), '/');
if (NwdEditorialPages::isEditorial($this->diafan) && in_array($nwdCallbackPath, array(
    'services/izmerenie-petli-faza-nul',
    'services/proverka-uzo',
    'services/ispytanie-elektroinstrumenta',
    'services/elektroizmeritelnaya-laboratoriya',
    'services/zamer-soprotivleniya-izolyatsii',
    'services/proverka-soprotivleniya-zazemliteley',
    'services/proverka-avtomaticheskikh-vyklyuchateley',
    'services/ispytanie-silovykh-kabelnykh-liniy-napryazheniem',
    'services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya',
    'services/tekhnicheskiy-otchet-po-elektroizmereniyam',
    'services/zamer-metallosvyazi',
    'services/proverka-sistem-molniezaschity'
), true)) {
    echo '<insert name="show_include" file="callback" />';
}
?> 


<div class="modal-overlay" data-nosnippet>

	<insert name="show_form" module="feedback" site_id="27" template="popup" />

	<div id="gratitude" class="modal gratitude">
		<div class="gratitude__wrapper">
			<div class="modal__content">
				<p class="gratitude__title">Спасибо за сообщение</p>
				<p class="gratitude__description">мы свяжемся с Вами в ближайшее время.</p>
			</div>
		</div>
	</div>

</div>

