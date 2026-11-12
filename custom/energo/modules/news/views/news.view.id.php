<?php
/**
 * Шаблон страницы новости
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

echo '
<div class="news news_id">
	<div class="container">';

	echo '<div class="single-article">';
		echo '<h1 class="page-title">'.$result["name"].'</h1>';
	echo '</div>';

	echo '</div>';

	echo '<div class="container">';
		echo '<div class="maintenance">';
			echo $this->htmleditor('<insert name="show_dynamic" module="site" id="4">');
			echo '<div class="maintenance__img">';
				//изображения новости
				if (!empty($result["img"])) {
					$img = $result["img"][0];
					echo '<img src="' . BASE_PATH . $img["link"] . '" alt="' . $img["alt"] . '" title="' . $img["title"] . '">';
				}
			echo '</div>';
		echo '</div>';
		echo '<div class="textBlock">';
			//вывод основного текста новости
			echo $this->htmleditor($result['text']);
		echo '</div>';

	// echo '
	// <section class="find-cost">
	//	<div class="container">
	//		<div class="find-cost__row">
	//			<div class="find-cost__content">
	//				<h2 class="section-title section-title--alt">' . $this->htmleditor('<insert name="show_dynamic" module="site" id="2">') . '</h2>
	//				' . $this->htmleditor('<insert name="show_dynamic" module="site" id="3">') . '
	//				<p>Подробную информацию вы можете узнать у менеджеров «Спецэнергоразвитие», <br/>позвонив в офис компании: <a href="tel:88002012069">8 800 201-20-69</a></p>
	//			</div>
	//			<div class="find-cost__form">
	//				' . $this->htmleditor('<insert name="show_form" module="feedback" site_id="32" template="t2" data-btn="Получить консультацию" />') . '
	//			</div>
	//		</div>
	//	</div>
	// </section>';

	echo '<div class="container">';
		echo '<div class="simple-text">' . $this->htmleditor('<insert name="show_dynamic" module="site" id="5">') . '</div>';
	echo '</div>';

	echo $this->htmleditor('<insert name="show_block_rel" module="news" count="8" images="1">');

echo '</div>';






