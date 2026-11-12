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

require_once dirname(__DIR__) . '/service_navigation.php';

$rows = array_filter($result['rows'][$result['parent_id']] ?? array(), function ($row) {
    return NwdServiceNavigation::isFeatured($row);
});
if (!$rows) {
    return true;
}

echo '<ul>';
foreach ($rows as $row) {
    echo '<li class="nwd-service-mobile-item">';
    echo '<a class="nwd-service-mobile-link" href="' . NwdServiceNavigation::url() . '"'
        . (!empty($row['active']) ? ' aria-current="page"' : '') . '>' . $row['name'] . '</a>';
    echo '</li>';
}
echo '</ul>';
