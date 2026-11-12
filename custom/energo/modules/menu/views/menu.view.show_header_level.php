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
    return !NwdServiceNavigation::isRoot($row) && NwdServiceNavigation::isVisible($row);
});
if (!$rows) {
    return true;
}

echo '<div class="sub-services__wrapper sub-services__wrapper-header nwd-service-dropdown">';
echo '<ul class="sub-services">';
foreach ($rows as $row) {
    $url = !empty($row['othurl']) ? $row['othurl'] : BASE_PATH_HREF . $row['link'];
    echo '<li class="sub-services__item">';
    echo '<a class="sub-services__link nwd-service-link" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . ($row['attributes'] ?? '')
        . (!empty($row['active']) ? ' aria-current="page"' : '') . '>';
    echo '<span class="nwd-service-link__label">' . $row['name'] . '</span>';
    echo '<span class="nwd-service-link__arrow" aria-hidden="true">↗</span>';
    echo '</a></li>';
}
echo '</ul></div>';
