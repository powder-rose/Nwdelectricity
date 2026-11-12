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

// начало уровня меню
echo '<div class="sub-services__wrapper sub-services__wrapper-header">';
	echo '<ul class="sub-services">';
		foreach ($result["rows"][$result["parent_id"]] as $row)
		{
			if ( $row["link"] != 'services/' ) {
				echo '<li class="sub-services__item">';

					if (
						// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
						(!$row["active"] || $result["current_link"])

						// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
						&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
					)
					{
					if ($row["othurl"])
					{
						echo '<a class="sub-services__link" href="'.$row["othurl"].'"'.$row["attributes"].'>';
					}
					else
					{
						echo '<a class="sub-services__link" href="'.BASE_PATH_HREF.$row["link"].'"'.$row["attributes"].'>';
					}
					}

						//вывод изображения
						// if (! empty($row["img"]))
						// {
						// 	echo '<img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"]
						// 	.'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"> ';
						// }

						// название пункта меню
						if (! empty($row["name"]))
						{
								echo $row["name"];
						}
				
					if (
						// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
						(!$row["active"] || $result["current_link"])

						// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
						&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
					)
					{
					echo '</a>';
					}

					// описание пункта меню
					// if (! empty($row["text"]))
					// {
					// 		echo $row["text"];
					// }

					// if ($result["show_all_level"] || $row["active_child"] || $row["active"])
					// {
					// // вывод вложенного уровня меню
					// $menu_data = $result;
					// $menu_data["parent_id"] = $row["id"];
					// $menu_data["level"]++;

					// if (empty($result['attributes']['count_level']) || $result['attributes']['count_level'] >= $menu_data["level"])
					// 	echo $this->get('show_level_2', 'menu', $menu_data);  //вызывает сама себя, для вывод последующих уровней вложенности с классом class="menu_item_2 и т.д.
					// }

				echo '</li>';
			}
		}
	echo '</ul>';
	foreach ($result["rows"][$result["parent_id"]] as $row)
	{

		if ( $row["link"] == 'services/' )
		{
			if (
				// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
				(!$row["active"] || $result["current_link"])

				// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
				&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
			) {
				if ($row["othurl"]) {
					echo '<a class="sub-services__more" href="' . $row["othurl"] . '"' . $row["attributes"] . '>';
				} else {
					echo '<a class="sub-services__more" href="' . BASE_PATH_HREF . $row["link"] . '"' . $row["attributes"] . '>';
				}
			}
				// название пункта меню
				if (!empty($row["name"])) {
					echo '<span class="sub-services__more-label">' . $row["name"] . '</span>';
				}
			echo '</a>';
		}
	}
echo '</div>';