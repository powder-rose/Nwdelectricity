<?php if (!defined('DIAFAN')) { http_response_code(404); exit; } ?>
<!doctype html><html lang="ru">
<insert name="show_include" file="head" />
<link rel="stylesheet" href="/custom/energo/css/rcd-service.css?v=<?= filemtime(ABSOLUTE_PATH.'custom/energo/css/rcd-service.css') ?>">
<body class="page-inn page-editorial page-phase-neutral page-rcd"><div class="page-wrapper">
<insert name="show_include" file="header" />
<main class="page-main"><insert name="show_rcd_content" /></main>
<insert name="show_include" file="footer" />
</div>
<insert name="show_include" file="modal" /><insert name="show_include" file="scripts" />
<script src="/custom/energo/js/rcd-service.js?v=rcd"></script>
</body></html>
