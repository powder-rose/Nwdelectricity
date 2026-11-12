<?php
/**
 * Как "Ответственный за электрохозяйство"
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

if(! empty($result["error"]))
{
	echo '<p>'.$result["error"].'</p>';
	return;
}

echo '
<div class="fscreen">
	<div class="container">
		<div class="fscreen__wrap fscreen__wrap--img">
			<div class="fscreen__content">
				<h1 class="fscreen__heading">
					' . $result["name"] . '
				</h1>
				'. $this->htmleditor('<insert name="show_dynamic" module="site" id="5">') .'
			</div>';
			if (!empty($result["img"]))
			{
				echo '<div class="fscreen__img">';
					if (!empty($result["img"]))
					{
						$img = $result["img"][0];
						echo '<img class="br-20" src="'.BASE_PATH.$img["link"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';
					}
				echo '</div>';
			}
echo '
		</div>
	</div>
</div>';

echo '<div class="container js_shop_id js_shop shop shop_id">';

	echo '<div class="single-article">';
		// echo '<h1 class="page-title">' . $result["name"] . '</h1>';
		echo '<div class="single-article__content">';
			// if (!empty($result["img"]))
			// {
			// 	$img = $result["img"][0];
			// 	echo '<img class="single-article__thumbnail--left single-article__thumbnail--radius single-article__thumbnail" src="'.BASE_PATH.$img["link"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';
			// }
			echo $this->htmleditor($result['text']);

		echo '</div>';
	echo '</div>';
	
echo '</div>';

echo '<div class="container">';
	echo $this->htmleditor('<insert name="show_dynamic" module="site" id="4">');
echo '</div>';


//вывод списка товаров
if (!empty($result["rows"]))
{
echo '<div class="container">';
	echo '<ul class="sub-gl-cats js--tabContent">';
		echo $this->get('rows', 'shop', $result);
	echo '</ul>';
echo '</div>';
}



echo '
<section class="find-cost">
	<div class="container">
		<div class="find-cost__row">
			<div class="find-cost__content">
				<h2 class="section-title section-title--alt">' . $this->htmleditor('<insert name="show_dynamic" module="site" id="2">') . '</h2>
				'. $this->htmleditor('<insert name="show_dynamic" module="site" id="3">') . '
				<p>Подробную информацию вы можете узнать у менеджеров «Спецэнергоразвитие», <br/>позвонив в офис компании: <a href="tel:88002012069">8 800 201-20-69</a></p>
			</div>
			<div class="find-cost__form">
				' . $this->htmleditor('<insert name="show_form" module="feedback" site_id="32" template="t2" data-btn="Получить консультацию" />') . '
			</div>
		</div>
	</div>
</section>';


// echo '<div class="container">';
// 	echo '<div class="simple-text">' . $this->htmleditor('<insert name="show_dynamic" module="site" id="5">') . '</div>';
// echo '</div>';






