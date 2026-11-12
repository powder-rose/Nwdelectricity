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

if (empty($result["rows"][$result["parent_id"]]))
{
	return true;
}


echo '<ul>';
	// начало уровня меню
	foreach ($result["rows"][$result["parent_id"]] as $row)
	{
		echo '<li>';

			if (
				// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
				(!$row["active"] || $result["current_link"])

				// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
				&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
			) {
				if ($row["othurl"]) {
					echo '<a href="' . $row["othurl"] . '"' . $row["attributes"] . '>';
				} else {
					echo '<a href="' . BASE_PATH_HREF . $row["link"] . '"' . $row["attributes"] . '>';
				}
			}

			//вывод изображения
			// if (! empty($row["img"]))
			// {
			// 	echo '<img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"]
			// 	.'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"> ';
			// }

			// название пункта меню
			if (!empty($row["name"])) {
				echo $row["name"];
			}

			if (
				// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
				(!$row["active"] || $result["current_link"])

				// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
				&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
			) {
				echo '</a>';
			}

		echo '</li>';
	}
echo '</ul>';