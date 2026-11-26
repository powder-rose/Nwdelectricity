<?php if(!defined('DIAFAN')){http_response_code(404);exit;} ?>
<!doctype html><html lang="ru">
<insert name="show_include" file="head" />
<body class="page-inn page-editorial page-phase-neutral page-cables"><div class="page-wrapper">
<insert name="show_include" file="header" />
<main class="page-main"><nav class="cbl-breadcrumbs fn-breadcrumbs" aria-label="Хлебные крошки"><div class="container"><ol><li><a href="/">Главная</a></li><li><a href="/services/">Услуги</a></li><li><a href="/services/elektroizmeritelnaya-laboratoriya/">Электроизмерительная лаборатория</a></li><li><span aria-current="page">Испытания силовых кабельных линий</span></li></ol></div></nav><insert name="show_cables_content" /></main>
<insert name="show_include" file="footer" />
</div><insert name="show_include" file="modal" /><insert name="show_include" file="scripts" />
<script src="/custom/energo/js/cables.js?v=<?php echo filemtime(ABSOLUTE_PATH.'custom/energo/js/cables.js'); ?>"></script>
</body></html>
