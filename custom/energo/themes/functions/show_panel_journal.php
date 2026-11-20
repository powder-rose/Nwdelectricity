<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
require_once ABSOLUTE_PATH.Custom::path('themes/author_blog.php');
try {
    $blog=NwdAuthorBlog::listing($_GET);
    include ABSOLUTE_PATH.Custom::path('themes/blog_index.php');
} catch (Throwable $e) {
    error_log('NWD author blog: '.get_class($e).' '.(int)$e->getCode());
    http_response_code(503);
    echo '<section class="container nwd-blog-empty"><h1>Блог Николая Бойкова</h1><p>Не удалось загрузить статьи. Пожалуйста, попробуйте обновить страницу.</p></section>';
}
