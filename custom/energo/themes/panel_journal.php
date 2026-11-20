<?php if (!defined('DIAFAN')) { http_response_code(404); exit; } ?>
<!doctype html><html lang="ru">
<insert name="show_include" file="head" />
<body class="page-inn page-editorial nwd-blog-page"><div class="page-wrapper">
<insert name="show_include" file="header" />
<main class="page-main nwd-blog-main"><insert name="show_panel_journal" /></main>
<insert name="show_include" file="footer" />
</div>
<a class="home-callback nwd-blog-list-callback" href="#modal" data-fancybox data-src="#modal" aria-label="Заказать звонок">
      <span class="home-callback__icon" aria-hidden="true">
         <svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 3.8 8.2 3l2.1 4.5-1.8 1.8a14.8 14.8 0 0 0 6.2 6.2l1.8-1.8 4.5 2.1-.8 3.2c-.2.8-1 1.4-1.8 1.3C10.6 19.6 4.4 13.4 3.7 5.6 3.6 4.8 4.2 4 5 3.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="home-callback__label">Заказать звонок <span aria-hidden="true">↗</span></span>
   </a>
<insert name="show_include" file="modal" /><insert name="show_include" file="scripts" />
</body></html>
