<?php
/** Render the original CMS text, changing only the About-page photograph. */
if (!defined('DIAFAN')) { exit; }

require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
$nwdAboutText = $this->get_function_in_theme('<insert name="show_text">');
echo NwdEditorialPages::isAbout($this->diafan) ? NwdEditorialPages::aboutContent($nwdAboutText) : $nwdAboutText;
