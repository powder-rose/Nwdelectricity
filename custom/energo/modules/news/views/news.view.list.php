<?php
/**
 * Шаблон список новостей
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

echo '<section class="news">
	<div class="container">';

	echo '<h1 class="page-title">' . $this->htmleditor('<insert name="show_h1">') . '</h1>';

	//вывод списка новостей
	if(! empty($result["rows"]))
	{
		echo '<div class="news__list">';

		foreach ($result['rows'] as $row) {
			echo '<a href="' . BASE_PATH_HREF . $row["link"] . '" class="news__item">';

			if (!empty($row["img"])) {
				$img = $row["img"][0];
				echo '<div class="news__thumbnail-wrapper">';
				echo '<img class="news__thumbnail" src="' . $img["src"] . '" alt="' . $img["alt"] . '" title="' . $img["title"] . '">';
				echo '</div> ';
			} else {
				echo '<div class="news__thumbnail-wrapper">';
				echo '<img class="news__thumbnail" src="/custom/energo/images/pic-news.jpg">';
				echo '</div> ';
			}
			echo '<p class="news__title">' . $row["name"] . '</p>';
			echo '</a>';
		}

		echo '</div>';
	}

	//вывод постраничная навигация в конце списка новостей
	if(! empty($result["paginator"]))
	{
		echo $result["paginator"];
	}


	echo '</div>';
echo '</section>';
