<?php if (!defined('DIAFAN')) { exit; } ?>
<section class="nwd-blog-hero container" aria-labelledby="blog-title">
  <div class="nwd-blog-hero__copy">
    <span class="nwd-blog-eyebrow"><i aria-hidden="true"></i>Личный блог Николая Бойкова</span>
    <h1 id="blog-title">Электрохозяйство.<br><span>По существу</span><b aria-hidden="true">.</b></h1>
    <p class="nwd-blog-hero__lead">О безопасности, документах<br>и ответственности бизнеса.</p>
    <p class="nwd-blog-hero__description">Разбираю вопросы эксплуатации электроустановок и делюсь практикой. Понятным языком — о том, что важно руководителю и специалисту.</p>
    <div class="nwd-blog-hero__author" id="nikolay-boykov"><strong>Николай Бойков</strong><span>Основатель БОЙКОВ ГРУПП.<br>Руководитель «Спецэнергоразвития».</span></div>
    <a class="nwd-blog-text-link" href="#materials">Читать статьи <span aria-hidden="true">↘</span></a>
  </div>
  <figure class="nwd-blog-portrait">
    <div class="nwd-blog-portrait__frame" aria-hidden="true"></div>
    <span class="nwd-blog-portrait__label" aria-hidden="true">Практика. Опыт. Ответственность.</span>
    <img src="<?=NwdAuthorBlog::AUTHOR_IMAGE?>" width="1024" height="1536" alt="Николай Бойков, автор блога" fetchpriority="high" decoding="async">
    <figcaption><span>Николай Бойков</span><span>Автор блога <i aria-hidden="true">↗</i></span></figcaption>
  </figure>
</section>
<section class="nwd-blog-materials container" id="materials" aria-labelledby="materials-title">
  <div class="nwd-blog-section-heading"><div><span class="nwd-blog-eyebrow">Материалы и разборы</span><h2 id="materials-title">Опыт, которым делюсь<span>.</span></h2></div><p><?=NwdAuthorBlog::h(NwdAuthorBlog::countLabel($blog['total']))?></p></div>
  <div class="nwd-blog-toolbar">
    <nav class="nwd-blog-categories" aria-label="Темы статей">
      <a href="<?=NwdAuthorBlog::h(NwdAuthorBlog::query(array('category'=>'','page'=>1),$blog))?>" <?=$blog['category']===''?'aria-current="page"':''?>>Все темы</a>
      <?php foreach($blog['categories'] as $category): ?><a href="<?=NwdAuthorBlog::h(NwdAuthorBlog::query(array('category'=>$category,'page'=>1),$blog))?>" <?=$blog['category']===$category?'aria-current="page"':''?>><?=NwdAuthorBlog::h($category)?></a><?php endforeach ?>
    </nav>
    <form class="nwd-blog-search" action="/articles/#materials" method="get" role="search"><label for="blog-search" class="visually-hidden">Поиск по статьям</label><input type="search" id="blog-search" name="q" value="<?=NwdAuthorBlog::h($blog['search'])?>" maxlength="100" placeholder="Найти статью">
      <?php if($blog['category']!==''): ?><input type="hidden" name="category" value="<?=NwdAuthorBlog::h($blog['category'])?>"><?php endif ?><button type="submit" aria-label="Найти статью">↗</button></form>
  </div>
  <?php $items=$blog['articles'];if($items && $blog['page']===1 && $blog['search']==='' && $blog['category']===''): $first=array_shift($items);NwdAuthorBlog::card($first,true);endif ?>
  <?php if($items): ?><div class="nwd-blog-grid"><?php foreach($items as $number=>$item){NwdAuthorBlog::card($item,false,$number+2);} ?></div><?php endif ?>
  <?php if(!$blog['articles']): ?><div class="nwd-blog-empty"><span aria-hidden="true">↗</span><h3><?=$blog['search']!=='' || $blog['category']!==''?'По вашему запросу статей нет':'Здесь скоро появятся новые материалы'?></h3><p><?=$blog['search']!=='' || $blog['category']!==''?'Попробуйте другую тему или измените поисковый запрос.':'Возвращайтесь за разборами и практическими рекомендациями.'?></p><?php if($blog['search']!=='' || $blog['category']!==''): ?><a href="/articles/#materials">Показать все статьи</a><?php endif ?></div><?php endif ?>
  <?php if($blog['pages']>1): ?><nav class="nwd-blog-pagination" aria-label="Страницы блога"><?php if($blog['page']>1): ?><a href="<?=NwdAuthorBlog::h(NwdAuthorBlog::query(array('page'=>$blog['page']-1),$blog))?>">← Назад</a><?php endif ?><span>Страница <?=$blog['page']?> из <?=$blog['pages']?></span><?php if($blog['page']<$blog['pages']): ?><a href="<?=NwdAuthorBlog::h(NwdAuthorBlog::query(array('page'=>$blog['page']+1),$blog))?>">Далее →</a><?php endif ?></nav><?php endif ?>
</section>
<section class="nwd-blog-contact container"><div><span class="nwd-blog-eyebrow">От чтения — к решению</span><h2>Есть вопрос<br>по электрохозяйству<span>?</span></h2><p>Обсудим вашу задачу и подходящий формат обслуживания.</p></div><a href="#modal" data-fancybox data-src="#modal" class="nwd-blog-contact__button">Обсудить задачу <span aria-hidden="true">↗</span></a></section>
<?php NwdAuthorBlog::jsonld(array('@context'=>'https://schema.org','@type'=>'Blog','name'=>'Личный блог Николая Бойкова','url'=>'https://nwdelectricity.ru/articles/','author'=>array('@type'=>'Person','@id'=>'https://nwdelectricity.ru/articles/#nikolay-boykov','name'=>'Николай Бойков','image'=>'https://nwdelectricity.ru'.NwdAuthorBlog::AUTHOR_IMAGE))); ?>
