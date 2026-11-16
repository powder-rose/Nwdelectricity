<?php
/** Keep the original CMS price list; add presentation markup only. */
if (!defined('DIAFAN')) { exit; }

require_once ABSOLUTE_PATH . Custom::path('themes/editorial_pages.php');
$nwdPriceText = $this->get_function_in_theme('<insert name="show_text">');
echo NwdEditorialPages::isPrices($this->diafan) ? NwdEditorialPages::priceContent($nwdPriceText) : $nwdPriceText;
