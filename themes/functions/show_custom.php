<?php
/**
 * Шаблонный тег: выводит кастомизированные данные шаблона.
 *
 * @param array $attributes атрибуты шаблонного тега
 * value - имя поля, содержащего кастомизированные данные
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
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
$this->diafan->attributes($attributes, 'value', 'useradmin');
$attributes["value"] = preg_replace('/[^a-z_\-0-9]+/', '', $attributes["value"]);
$attributes["useradmin"] = ($attributes["useradmin"]=='no'?'no':'');
if (! $attributes["value"]) {
	return;
}
switch ($attributes["value"])
{
	case 'logo':
		$this->result = $this->diafan->_images->get('medium', 0, 'custom', 'element', 0, $this->diafan->_('Логотип', false), false, false);
		break;

	default:
		$this->result = $this->diafan->configmodules($attributes["value"], 'custom');
		$this->id = DB::query_result("SELECT id FROM {config} WHERE module_name='custom' AND name='%s' LIMIT 1", $attributes["value"]);
		break;
}

if (empty($this->result)) { return; }

if(! Custom::exists('themes/custom/'.$attributes["value"].'.php'))
{
	echo ($attributes["useradmin"]=='no'?$this->diafan->_tpl->htmleditor($this->result):$this->diafan->_useradmin->get($this->result, 'value', $this->id, 'config', 0, 'text'));
	return;
}
$inc = file_get_contents(Custom::path('themes/custom/'.$attributes["value"].'.php'));
echo $this->get_function_in_theme($inc, true);
