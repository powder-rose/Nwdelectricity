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
?> 


<head>

	<insert name="show_head">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
	if (http_response_code() < 400 && $this->diafan->_site->id && !$this->diafan->_users->useradmin) {
		$page = '/'.trim((string)$this->diafan->_site->rewrite, '/');
		echo '<script defer src="/redaktsiya/assets/traffic.js" data-page="'.htmlspecialchars($page, ENT_QUOTES, 'UTF-8').'"></script>';
	}
	?>

	<link rel="shortcut icon" href="<insert name="path">favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/svg+xml" sizes="any" href="<insert name="path">favicon.svg">
	<insert name="show_css" files="style.css">
	<?php
	require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
	echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/site-photo-tone.css'), ENT_QUOTES, 'UTF-8') . '?nwd-tone=soft">';
	if ((int) $this->diafan->_site->id === 1) {
		echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/home-editorial.css'), ENT_QUOTES, 'UTF-8') . '">';
		echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/home-polish.css'), ENT_QUOTES, 'UTF-8') . '">';
		echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/home-hero.css'), ENT_QUOTES, 'UTF-8') . '?v=photo-refined">';
	} elseif (NwdEditorialPages::isEditorial($this->diafan)) {
		echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/editorial-pages.css'), ENT_QUOTES, 'UTF-8') . '">';
		$editorialStyle = NwdEditorialPages::isAbout($this->diafan) ? 'about-editorial.css' : 'prices-editorial.css';
		if (NwdEditorialPages::isContacts($this->diafan)) { $editorialStyle = 'contacts-editorial.css'; }
		if (NwdEditorialPages::isResponsible($this->diafan)) { $editorialStyle = 'responsible-editorial.css'; }
		if (NwdEditorialPages::isBlog($this->diafan)) { $editorialStyle = 'author-blog.css'; }
		if (NwdEditorialPages::isMaintenance($this->diafan)) { $editorialStyle = 'maintenance-editorial.css'; }
		if (NwdEditorialPages::isPhaseNeutral($this->diafan) || NwdEditorialPages::isRcd($this->diafan) || NwdEditorialPages::isPowerTools($this->diafan) || NwdEditorialPages::isLaboratory($this->diafan) || NwdEditorialPages::isInsulation($this->diafan)) { $editorialStyle = 'phase-neutral.css'; }
		if (NwdEditorialPages::isServices($this->diafan)) { $editorialStyle = 'services-directory.css'; }
		echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/' . $editorialStyle), ENT_QUOTES, 'UTF-8') . (in_array($editorialStyle,array('author-blog.css','phase-neutral.css'),true) ? '?v=' . filemtime(ABSOLUTE_PATH . Custom::path('css/' . $editorialStyle)) : '') . '">';
        if (NwdEditorialPages::isPowerTools($this->diafan)) { echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/power-tools.css'), ENT_QUOTES, 'UTF-8') . '?v=' . filemtime(ABSOLUTE_PATH . Custom::path('css/power-tools.css')) . '">'; }
        if (NwdEditorialPages::isLaboratory($this->diafan)) { echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/laboratory.css'), ENT_QUOTES, 'UTF-8') . '?v=' . filemtime(ABSOLUTE_PATH . Custom::path('css/laboratory.css')) . '">'; }
        if (NwdEditorialPages::isInsulation($this->diafan)) { echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/insulation.css'), ENT_QUOTES, 'UTF-8') . '?v=' . filemtime(ABSOLUTE_PATH . Custom::path('css/insulation.css')) . '">'; }
	}
	echo '<link rel="stylesheet" href="' . htmlspecialchars(BASE_PATH . Custom::path('css/service-navigation.css'), ENT_QUOTES, 'UTF-8') . '">';
	?>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src='https://vk.com/js/api/openapi.js?169',t.onload=function(){VK.Retargeting.Init("VK-RTRG-1857888-7AIPy"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.com/rtrg?p=VK-RTRG-1857888-7AIPy" style="position:fixed; left:-999px;" alt=""/></noscript>
</head>

