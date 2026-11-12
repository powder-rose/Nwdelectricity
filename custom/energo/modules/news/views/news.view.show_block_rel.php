<?php
/**
 * Шаблон блока похожих новостей
 * 
 * Шаблонный тег <insert name="show_block_rel" module="news" [count="количество"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих новостей
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

if(empty($result["rows"])) return false;

	echo '
	<div class="container">
		<section class="news news--recommend">';
						
	echo '<h2 class="section-title">Похожие статьи</h2>';

	echo '<div class="news__list">';

		foreach ($result['rows'] as $row)
		{
			echo '<a href="' . BASE_PATH_HREF . $row["link"] . '" class="news__item">';
				//вывод изображений товара
				if (!empty($row["img"]))
				{
					$img = $row["img"][0];
					echo '<div class="news__thumbnail-wrapper">';
						echo '<img class="news__thumbnail" src="' . $img["src"] . '" alt="' . $img["alt"] . '" title="' . $img["title"] . '">';
					echo '</div> ';
				} else {
					echo '<div class="news__thumbnail-wrapper">';
						echo '<img class="news__thumbnail" src="/custom/energo/images/pic-news--recommend.jpg">';
					echo '</div> ';
				}
				echo '<p class="news__title">' . $row["name"] . '</p>';
			echo '</a>';
		}

	echo '</div>';

echo '
		</section>
	</div>';

