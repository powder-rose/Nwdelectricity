<?php
/** Presentation only: use the same rendered CMS text and menu as the existing page. */
if (!defined('DIAFAN')) { exit; }
require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
$nwdServicesText = $this->get_function_in_theme('<insert name="show_text">');
if (NwdEditorialPages::isServices($this->diafan)) {
    require_once ABSOLUTE_PATH . Custom::path('themes/services_directory.php');
    echo NwdServicesDirectory::render($nwdServicesText);
} else {
    echo $nwdServicesText;
}
