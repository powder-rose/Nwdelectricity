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

$rows = array_filter(NwdServiceNavigation::headerRows($result), function ($row) {
    return NwdServiceNavigation::isHeaderService($row);
});
if (!$rows) {
    return true;
}

echo '<ul>';
foreach ($rows as $row) {
    $url = !empty($row['othurl']) ? $row['othurl'] : BASE_PATH_HREF . $row['link'];
    echo '<li class="nwd-service-mobile-item">';
    echo '<a class="nwd-service-mobile-link" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . (!empty($row['active']) ? ' aria-current="page"' : '') . '>' . $row['name'] . '</a>';
    echo '</li>';
}
echo '</ul>';
