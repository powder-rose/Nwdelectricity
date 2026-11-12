<?php


if (!defined('DIAFAN')) {
	$path = __FILE__;
	$i = 0;
	while (!file_exists($path . '/includes/404.php')) {
		if ($i == 10) exit;
		$i++;
		$path = dirname($path);
	}
	include $path . '/includes/404.php';
}

if (empty($result["rows"])) {
	return false;
}


echo '<ul class="global-cats js--tabList">';
	foreach ($result["rows"][$result["parent_id"]] as $row) {

		echo '<li class="global-cats__item">';

			echo '<a class="global-cats__link" data-tab="#tab' . $row["id"] . '" href="javascript:;" rel="nofollow">' . $row["name"] . '</a>';

		echo '</li>';
	}
echo '</ul>';
foreach ($result["rows"][$result["parent_id"]] as $row) {

	echo '<ul id="tab'. $row["id"] .'" class="sub-gl-cats js--tabContent">';

	if (
		$result["show_all_level"] || $row["active_child"] || $row["active"]
	) {
		// вывод вложенного уровня меню
		$menu_data = $result;
		$menu_data["parent_id"] = $row["id"];
		$menu_data["level"]++;

		if (empty($result['attributes']['count_level']) || $result['attributes']['count_level'] >= $menu_data["level"])
			echo $this->get('show_service_level', 'menu', $menu_data);  //вызывает сама себя, для вывод последующих уровней вложенности с классом class="menu_item_2 и т.д.
	}

	echo '</ul>';
}
