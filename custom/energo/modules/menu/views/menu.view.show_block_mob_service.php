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


$i = 2;
echo '<ul>';
	foreach ($result["rows"][$result["parent_id"]] as $row) {

		$i++;

		echo '<li>';

			echo '<a href="#mm-'. $i .'"' . $row["attributes"] . '>';

				// название пункта меню
				if (!empty($row["name"])) {
					echo $row["name"];
				}

		echo '</a>';



		if (
			$result["show_all_level"] || $row["active_child"] || $row["active"]
		) {
			// вывод вложенного уровня меню
			$menu_data = $result;
			$menu_data["parent_id"] = $row["id"];
			$menu_data["level"]++;

			if (empty($result['attributes']['count_level']) || $result['attributes']['count_level'] >= $menu_data["level"])
			echo $this->get('show_mob_service_level', 'menu', $menu_data);  //вызывает сама себя, для вывод последующих уровней вложенности с классом class="menu_item_2 и т.д.
		}

		echo '</li>';	
	}
echo '</ul>';
