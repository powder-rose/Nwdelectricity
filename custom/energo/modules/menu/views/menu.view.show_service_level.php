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
foreach ($result["rows"][$result["parent_id"]] as $row)
{

	echo '<li class="sub-gl-cats__item">';

		if (
			// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
			(!$row["active"] || $result["current_link"])

			// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
			&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
		)
		{
		if ($row["othurl"])
		{
			echo '<a class="sub-gl-cats__link" href="'.$row["othurl"].'"'.$row["attributes"].'>';
		}
		else
		{
			echo '<a class="sub-gl-cats__link" href="'.BASE_PATH_HREF.$row["link"].'"'.$row["attributes"].'>';
		}
		}

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

	echo '</li>';
}

