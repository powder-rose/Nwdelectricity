<?php
/**
 * Настройки шаблона сайта
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2020 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Site_admin_theme_custom
 */
class Site_admin_theme_custom extends Diafan
{
	/**
	 * @var array поля для редактирования
	 */
	public $variables = array (
		'phone' => array(
			'type' => 'phone',
			'name' => 'Телефон'
		),
		'mail' => array(
			'type' => 'email',
			'name' => 'E-mail'
		),
		'contacts' => array(
			'type' => 'editor',
			'name' => 'Контакты',
			'help' => "Адрес организации."
		),
		'title_soc' => array(
			'type' => 'title',
			'name' => 'Соц.сети:'
		),
		'instagram' => array(
			'type' => 'url',
			'name' => 'instagram'
		),
		'title_mes' => array(
			'type' => 'title',
			'name' => 'Мессенджеры:'
		),
	);
}
