<?php
/**
 * Шаблон блока баннеров
 * 
 * Шаблонный тег <insert name="show_block" module="bs" [count="all|количество"]
 * [cat_id="категория"] [id="номер_баннера"] [template="шаблон"]>:
 * блок баннеров
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2016 OOO «Диафан» (http://www.diafan.ru/)
 */

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

if (empty($result))
{
	return false;
}               



echo '<div class="js--promo-slider promo">';
	foreach ($result as $row)
	{
		echo '<div class="promo__item">';
		if (! empty($row['image']))
		{

			echo '<img class="promo__thumbnail" src="' . BASE_PATH . USERFILES . '/bs/' . $row['image'] . '" alt="" width="1920" height="700">';

			if(!empty($row["text"]))
			{
				echo '
				<div class="promo__wrapper">
					<div class="container">
						<div class="promo__content">';

							echo $row["text"];

							echo '<div class="promo__links">';
								echo '<a class="button promo__link" href="/ceny/">Наши цены</a>';
								if (!empty($row['link'])) {
									echo '<a class="button promo__link" href="' . $row['link'] . '">Страница услуги</a>';
								}
							echo '</div>';
				echo '
						</div>
					</div>
				</div>';
			}
		}
		echo '</div>';
	}
echo '</div>';