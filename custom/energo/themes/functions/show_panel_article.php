<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
echo '<link rel="stylesheet" href="/redaktsiya/assets/reading.css">';
echo $this->diafan->_site->text;
