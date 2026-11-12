<?php
/**
 * Кастомизированные данные: Описание на главной
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

// вывод содержимого настройки шаблона
// $this->diafan->_useradmin->get($this->result, 'value', $this->id, 'config', 0, 'text') - стандартный вызов значения, никакие переменные не меняются
// $this->diafan->_useradmin->get - функция быстрого редактирования выводимого значения
echo '<div class="custom_text">'.$this->diafan->_useradmin->get($this->result, 'value', $this->id, 'config', 0, 'text').'</div>';

?>
