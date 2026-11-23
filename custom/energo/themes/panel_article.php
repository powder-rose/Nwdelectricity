<?php if (!defined('DIAFAN')) { http_response_code(404); exit; } ?>
<!doctype html><html lang="ru">
<insert name="show_include" file="head" />
<body class="page-inn page-editorial nwd-blog-page"><div class="page-wrapper">
<insert name="show_include" file="header" />
<main class="page-main nwd-blog-main"><insert name="show_panel_article" /></main>
<insert name="show_include" file="footer" />
</div><insert name="show_include" file="modal" /><insert name="show_include" file="scripts" />
<div class="modal-overlay" data-nosnippet><insert name="show_form" module="feedback" site_id="27" template="document_request" /></div>
</body></html>
