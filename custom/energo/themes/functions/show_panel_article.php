<?php
if (!defined('DIAFAN')) { http_response_code(404); exit; }
require_once ABSOLUTE_PATH.Custom::path('themes/author_blog.php');
try {
    $article=NwdAuthorBlog::article((int)$this->diafan->_site->id);
    if (!$article) { http_response_code(404); echo '<section class="container nwd-blog-empty"><h1>Статья не найдена</h1><a href="/articles/">Все статьи</a></section>';return; }
    include ABSOLUTE_PATH.Custom::path('themes/blog_article.php');
} catch (Throwable $e) {
    error_log('NWD author article: '.get_class($e).' '.(int)$e->getCode());
    http_response_code(503);
    echo '<section class="container nwd-blog-empty"><h1>Не удалось загрузить статью</h1><p>Попробуйте обновить страницу.</p></section>';
}
