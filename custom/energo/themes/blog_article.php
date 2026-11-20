<?php if (!defined('DIAFAN')) { exit; }
$form=$article['form'];$body=NwdAuthorBlog::body($form['content']);$cover=NwdAuthorBlog::image($form['image']);$coverSize=$cover?NwdAuthorBlog::imageSize($cover):array();
?>
<article class="nwd-blog-article container">
  <nav class="nwd-blog-breadcrumb" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/articles/">Блог Николая Бойкова</a></nav>
  <header class="nwd-blog-article__head"><div class="nwd-blog-meta"><a href="<?=NwdAuthorBlog::h(NwdAuthorBlog::query(array('category'=>$article['category']),array()))?>"><?=NwdAuthorBlog::h($article['category'] ?: 'Практика')?></a><time datetime="<?=NwdAuthorBlog::h(NwdAuthorBlog::date($article['published_at'],'iso'))?>"><?=NwdAuthorBlog::h(NwdAuthorBlog::date($article['published_at']))?></time><span><?=NwdAuthorBlog::reading($form['content'])?> мин. чтения</span></div>
    <h1><?=NwdAuthorBlog::h($form['title'])?></h1><p class="nwd-blog-byline">Николай Бойков<span>Авторский блог об электрохозяйстве</span></p>
  </header>
  <div class="nwd-blog-reading-layout">
    <div class="nwd-blog-reading-main">
    <?php if($cover): ?><figure class="nwd-blog-article__cover"><img src="<?=NwdAuthorBlog::h($cover)?>" alt="<?=NwdAuthorBlog::h($form['imageAlt'])?>" <?php if($coverSize): ?>width="<?=$coverSize['width']?>" height="<?=$coverSize['height']?>"<?php endif ?> fetchpriority="high" decoding="async"></figure><?php endif ?>
    <div class="nwd-blog-prose"><?=$body['html']?></div>
    </div>
    <aside class="nwd-blog-reading-side" aria-label="Автор и содержание">
      <div class="nwd-blog-mini-author"><div class="nwd-blog-mini-author__image"><img src="<?=NwdAuthorBlog::AUTHOR_IMAGE?>" alt="" width="300" height="450" loading="lazy" decoding="async"></div><span class="nwd-blog-eyebrow">Автор материала</span><a href="/articles/#nikolay-boykov">Николай Бойков</a><p>Основатель БОЙКОВ ГРУПП.<br>Руководитель «Спецэнергоразвития».</p></div>
      <?php if($body['toc']): ?><details class="nwd-blog-toc" open><summary>В этой статье <span aria-hidden="true">↘</span></summary><nav aria-label="Содержание статьи"><ol><?php foreach($body['toc'] as $heading): ?><li><a href="#<?=NwdAuthorBlog::h($heading['id'])?>"><?=NwdAuthorBlog::h($heading['text'])?></a></li><?php endforeach ?></ol></nav></details><?php endif ?>
      <a class="nwd-blog-side-cta" href="#modal" data-fancybox data-src="#modal">Задать вопрос по объекту <span aria-hidden="true">↗</span></a>
    </aside>
  </div>
  <footer class="nwd-blog-article__footer"><a href="/articles/">← Все статьи Николая Бойкова</a><a href="/kontakty/">Связаться с компанией ↗</a></footer>
</article>
<?php $related=NwdAuthorBlog::related($article['id'],$article['category']);if($related): ?><section class="nwd-blog-related container" aria-labelledby="related-title"><div class="nwd-blog-section-heading"><div><span class="nwd-blog-eyebrow">Продолжить чтение</span><h2 id="related-title">Ещё по теме<span>.</span></h2></div><a class="nwd-blog-text-link" href="/articles/">Все материалы ↗</a></div><div class="nwd-blog-grid"><?php foreach($related as $i=>$item){NwdAuthorBlog::card($item,false,$i+1);} ?></div></section><?php endif ?>
<?php NwdAuthorBlog::jsonld(array('@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$form['title'],'mainEntityOfPage'=>'https://nwdelectricity.ru'.NwdAuthorBlog::url($article['slug']),'datePublished'=>gmdate('c',(int)$article['published_at']),'dateModified'=>gmdate('c',(int)$article['updated_at']),'author'=>array('@type'=>'Person','@id'=>'https://nwdelectricity.ru/articles/#nikolay-boykov','name'=>'Николай Бойков','url'=>'https://nwdelectricity.ru/articles/'),'publisher'=>array('@type'=>'Organization','name'=>'Спецэнергоразвитие','url'=>'https://nwdelectricity.ru/'))); ?>
<link rel="stylesheet" href="/redaktsiya/assets/documents.css?v=documents-4c09bfe3090b">
<script type="module" src="/redaktsiya/assets/documents.js?v=documents-4c09bfe3090b"></script>
