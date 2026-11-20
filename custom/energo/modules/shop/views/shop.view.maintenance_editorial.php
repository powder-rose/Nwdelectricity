<?php
/** Maintenance landing page: CMS content, native DIAFAN forms and the shared site shell. */
if (!defined('DIAFAN')) { exit; }
require_once ABSOLUTE_PATH . Custom::path('themes/maintenance_service.php');
$maintenance = NwdMaintenanceService::content($this->htmleditor($result['text']));
$maintenanceTitle = preg_replace('~(электроустановок)~iu', '<span>$1</span>', $result['name'], 1);
?>
<div class="maintenance-landing js_shop_id js_shop shop shop_id">
    <section class="maintenance-intro">
        <div class="container">
            <div class="maintenance-hero">
                <div class="maintenance-hero__copy">
                    <p class="maintenance-eyebrow">Москва и Московская область</p>
                    <h1><?php echo $maintenanceTitle; ?></h1>
                    <div class="maintenance-hero__lead"><?php echo $maintenance['lead']; ?></div>
                    <div class="maintenance-hero__actions">
                        <a class="maintenance-button" href="#modal" data-fancybox data-src="#modal">Заявка на обслуживание</a>
                        <a class="maintenance-link" href="#maintenance-prices">Стоимость обслуживания</a>
                    </div>
                </div>
                <figure class="maintenance-hero__photo"><?php echo NwdMaintenanceService::photo(); ?></figure>
            </div>
            <nav class="maintenance-navigation" aria-label="Разделы страницы">
                <a href="#maintenance-scope">Состав работ</a>
                <a href="#maintenance-types">Виды обслуживания</a>
                <a href="#maintenance-categories">Категории сложности</a>
                <a href="#maintenance-prices">Стоимость</a>
            </nav>
            <div class="maintenance-opening" data-maintenance-reveal><?php echo $maintenance['intro']; ?></div>
        </div>
    </section>
    <div class="container maintenance-content"><?php echo $maintenance['html']; ?></div>
    <div class="container maintenance-cms-extra"><?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="4">'); ?></div>
    <?php if (!empty($result['rows'])) { ?>
    <div class="container"><ul class="sub-gl-cats js--tabContent"><?php echo $this->get('rows', 'shop', $result); ?></ul></div>
    <?php } ?>
    <section id="maintenance-cost" class="find-cost maintenance-contact">
        <div class="container">
            <div class="find-cost__row">
                <div class="find-cost__content">
                    <h2 class="section-title section-title--alt"><?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="2">'); ?></h2>
                    <?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="3">'); ?>
                    <p>Подробную информацию вы можете узнать у менеджеров «Спецэнергоразвитие», <br>позвонив в офис компании: <a href="tel:88002012069">8 800 201-20-69</a></p>
                </div>
                <div class="find-cost__form">
                    <?php echo $this->htmleditor('<insert name="show_form" module="feedback" site_id="32" template="t2" data-btn="Получить консультацию" />'); ?>
                </div>
            </div>
        </div>
    </section>
    <div class="container maintenance-cms-extra"><?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="5">'); ?></div>
    <?php if (!empty($nwdMaintenanceRelated)) { echo $this->htmleditor('<insert name="show_block_rel" module="shop" count="8" images="1">'); } ?>
</div>
<a class="home-callback" href="#modal" data-fancybox data-src="#modal" aria-label="Заказать звонок">
    <span class="home-callback__icon" aria-hidden="true"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 3.8 8.2 3l2.1 4.5-1.8 1.8a14.8 14.8 0 0 0 6.2 6.2l1.8-1.8 4.5 2.1-.8 3.2c-.2.8-1 1.4-1.8 1.3C10.6 19.6 4.4 13.4 3.7 5.6 3.6 4.8 4.2 4 5 3.8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
    <span class="home-callback__label">Заказать звонок</span>
</a>
