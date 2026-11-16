<?php
if (!defined('DIAFAN')) { exit; }

require_once ABSOLUTE_PATH . Custom::path('themes/home_hero.php');
$nwdHeroContent = $this->get_function_in_theme('<insert name="show_dynamic" module="site" id="4">');
echo NwdHomeHero::present($nwdHeroContent);
