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

if (empty($result["rows"]))
{
	return false;
}



// if (!empty($result["name"]))
// {
// 	echo '<div class="block_header">'.$result["name"].'</div>';
// }


require_once dirname(__DIR__) . '/service_navigation.php';

$nwdHasArticles=false;
echo '<ul class="main-menu main-menu__page-header">';
	foreach ($result["rows"][$result["parent_id"]] as $row)
	{
		if (!NwdServiceNavigation::isVisible($row)) {
			continue;
		}
		$isServices = NwdServiceNavigation::isRoot($row);
        if (NwdServiceNavigation::path($row)==='articles') { $nwdHasArticles=true; $row['name']='Статьи'; }

		if ($isServices) {
			echo '<li class="main-menu__item main-menu__item--has-child nwd-service-parent">';
		}
		elseif ($row["active"]) {
			echo '<li class="main-menu__item">';
		}
		elseif ($row["active_child"]) {
			echo '<li class="main-menu__item main-menu__item--has-child">';
		}
		elseif ($row["children"]) {
			echo '<li class="main-menu__item main-menu__item--has-child">';
		}else{
			echo '<li class="main-menu__item">';
		}

			if ($isServices || (
				// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
				(!$row["active"] || $result["current_link"])

				// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
				&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
			)) {
				if ($isServices) {
					echo '<a class="main-menu__link" href="' . NwdServiceNavigation::url() . '">';
				} elseif ($row["othurl"]) {
					echo '<a class="main-menu__link" href="' . $row["othurl"] . '"' . $row["attributes"] . '>';
				} else {
					echo '<a class="main-menu__link" href="' . BASE_PATH_HREF . $row["link"] . '"' . $row["attributes"] . '>';
				}
			}

				//вывод изображения
				// if (!empty($row["img"])) {
				// 	echo '<img src="' . $row["img"]["src"] . '" width="' . $row["img"]["width"] . '" height="' . $row["img"]["height"]
				// 		. '" alt="' . $row["img"]["alt"] . '" title="' . $row["img"]["title"] . '"> ';
				// }

				// название пункта меню
				if (!empty($row["name"])) {
					echo $row["name"];
				}

		if ($isServices || (
			// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
			(!$row["active"] || $result["current_link"])

			// влючен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
			&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
		)) {
			echo '</a>';
		}

		// описание пункта меню
		if (!empty($row["text"])) {
			echo $row["text"];
		}

		if ($isServices || $result["show_all_level"] || $row["active_child"] || $row["active"]) {
			// вывод вложенного уровня меню
			$menu_data = $result;
			$menu_data["parent_id"] = $row["id"];
			$menu_data["level"]++;

			if (empty($result['attributes']['count_level']) || $result['attributes']['count_level'] >= $menu_data["level"])
				echo $this->get('show_header_level', 'menu', $menu_data);  //вызывает сама себя, для вывод последующих уровней вложенности с классом class="menu_item_2 и т.д.
		}

		echo '</li>';
	}
if (!$nwdHasArticles) {
    $nwdBlogCurrent=(bool)preg_match('~^/articles(?:/|$)~',(string)parse_url($_SERVER['REQUEST_URI'] ?? '',PHP_URL_PATH));
    echo '<li class="main-menu__item nwd-blog-menu-item"><a class="main-menu__link" href="/articles/"'.($nwdBlogCurrent?' aria-current="page"':'').'>Статьи</a></li>';
}
echo '</ul>';

