<?php
/** Keep the native mobile selector on the same responsive laboratory layout. */
if (!defined('DIAFAN')) { http_response_code(404);exit; }
echo $this->get_function_in_theme(file_get_contents(ABSOLUTE_PATH.Custom::path('themes/laboratory.php')),true);
?>
