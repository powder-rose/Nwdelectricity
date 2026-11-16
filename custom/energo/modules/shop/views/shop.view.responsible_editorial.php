<?php
/** The responsible-electrician service uses the approved site design and original CMS content. */
if (!defined('DIAFAN')) { exit; }
require_once ABSOLUTE_PATH . Custom::path('themes/responsible_service.php');
$nwdService = NwdResponsibleService::content($this->htmleditor($result['text']));
$nwdServiceHero = NwdResponsibleService::hero($this->htmleditor('<insert name="show_dynamic" module="site" id="5">'));
?>
<section class="service-intro">
    <div class="container">
        <div class="service-hero<?php if (!$nwdService['photo']) { echo ' service-hero--text-only'; } ?>">
            <div class="service-hero__content">
                <h1 class="service-hero__title"><?php echo $result['name']; ?></h1>
                <div class="service-hero__intro"><?php echo $nwdServiceHero['intro']; ?></div>
                <div class="service-hero__actions"><?php echo $nwdServiceHero['actions']; ?></div>
            </div>
            <?php if ($nwdService['photo']) { echo '<figure class="service-hero__photo">' . $nwdService['photo'] . '</figure>'; } ?>
        </div>
        <?php if ($nwdServiceHero['benefits']) { echo '<ul class="service-highlights">' . $nwdServiceHero['benefits'] . '</ul>'; } ?>
        <?php if (trim($nwdServiceHero['extra']) !== '') { echo '<div class="service-cms-extra">' . $nwdServiceHero['extra'] . '</div>'; } ?>
    </div>
</section>
<div class="container service-content js_shop_id js_shop shop shop_id">
    <?php echo $nwdService['html']; ?>
</div>
<div class="container service-cms-extra">
    <?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="4">'); ?>
</div>
<?php if (!empty($result['rows'])) { ?>
<div class="container">
    <ul class="sub-gl-cats js--tabContent"><?php echo $this->get('rows', 'shop', $result); ?></ul>
</div>
<?php } ?>
<section class="find-cost">
    <div class="container">
        <div class="find-cost__row">
            <div class="find-cost__content">
                <h2 class="section-title section-title--alt"><?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="2">'); ?></h2>
                <?php echo $this->htmleditor('<insert name="show_dynamic" module="site" id="3">'); ?>
                <p>Подробную информацию вы можете узнать у менеджеров «Спецэнергоразвитие», <br/>позвонив в офис компании: <a href="tel:88002012069">8 800 201-20-69</a></p>
            </div>
            <div class="find-cost__form">
                <?php echo $this->htmleditor('<insert name="show_form" module="feedback" site_id="32" template="t2" data-btn="Получить консультацию" />'); ?>
            </div>
        </div>
    </div>
</section>
