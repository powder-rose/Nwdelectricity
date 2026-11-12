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


<div class="modal-overlay">

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

