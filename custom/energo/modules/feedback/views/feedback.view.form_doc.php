<?php
/**
 * Шаблон формы добавления сообщения в обратной связи
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    7.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2021 OOO «Диафан» (http://www.diafan.ru/)
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



// if(! empty($result["text"]))
// {
// 	echo '<section class="_text">'.$result["text"].'</section>';
// 	return;
// }

// echo '<div class="tmForm">';

	// if(! empty($result["attributes"]["title"]))
	// {
	// 	if (!empty($result["attributes"]["desc"])) {
	// 		echo '<p class="tmForm__title">' . $result["attributes"]["title"] . '</p>';
	// 	}else{
	// 		echo '<p class="tmForm__title tmForm__title--single">' . $result["attributes"]["title"] . '</p>';
	// 	}
	// }else{
	// 	if (!empty($result["attributes"]["desc"])) {
	// 		echo '<p class="tmForm__title">Оставить заявку</p>';
	// 	} else {
	// 		echo '<p class="tmForm__title tmForm__title--single">Оставить заявку</p>';
	// 	}
	// }

	// if(! empty($result["attributes"]["desc"]))
	// {
	// 	echo '<p class="tmForm__desc">'.$result["attributes"]["desc"].'</p>';
	// }

echo
'<form method="POST" enctype="multipart/form-data" action="" class="tmForm__from ajax">
	<input type="hidden" name="module" value="feedback">
	<input type="hidden" name="action" value="add">
	<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
	<input type="hidden" name="site_id" value="'.$result["site_id"].'">
	<input type="hidden" name="add_c" value="4l">
	<input type="hidden" name="tmpcode" value="'.md5(mt_rand(0, 9999)).'">';

	//заголовок блока
	// if (! empty($result["name"]))
	// {
	// 	echo '<header class="block-d__name">'.$result["name"].'</header>';
	// }

	$required = false;
	if(! empty($result["rows"]))
	{
		foreach ($result["rows"] as $row) //вывод полей из конструктора форм
		{
			if($row["required"])
			{
				$required = true;
			}
			// echo '<div class="field-d feedback_form_param'.$row["id"].'">';

			switch ($row["type"])
			{
				// case 'title':
				// 	echo '<div class="field-d__title">'.$row["name"].':</div>';
				// 	break;

				case 'text':
					echo '
					<div class="tmForm__inputWrap">
						<input type="text" class="tmForm__input" name="p'.$row["id"].'" value="" placeholder="'.$row["name"].($row["required"] ? '' : '').'">
					</div>';
					break;

				case "email":
					echo '
					<div class="tmForm__inputWrap">
						<input type="email" class="tmForm__input" name="p'.$row["id"].'" value="" placeholder="'.$row["name"].($row["required"] ? '' : '').'">
					</div>';
					break;

				// case 'date':
				// case 'datetime':
				// 	$timecalendar  = true;
				// 	echo '<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>
				// 		<input type="text" name="p'.$row["id"].'" value="" class="timecalendar" showTime="'
				// 		.($row["type"] == 'datetime'? 'true' : 'false').'">';
				// 	break;

				// case 'numtext':
				// 	echo '<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>
				// 	<input type="number" name="p'.$row["id"].'" value="">';
				// 	break;

				// case 'checkbox':
				// 	echo '<input name="p'.$row["id"].'" id="feedback_p'.$row["id"].'" value="1" type="checkbox" ><label for="feedback_p'.$row["id"].'">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').'</label>';
				// 	break;

				// case 'radio':
				// 	echo '<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>';
				// 	echo '<div class="field-d__list">';
				// 	foreach ($row["select_array"] as $select)
				// 	{
				// 		echo
				// 		'<div class="field-d__item">
				// 			<input name="p'.$row["id"].'" type="radio" value="'.$select["id"].'" id="feedback_form_p'.$row["id"].'_'.$select["id"].'">
				// 			<label for="feedback_form_p'.$row["id"].'_'.$select["id"].'">'.$select["name"].'</label>
				// 		</div>';
				// 	}
				// 	echo '</div>';
				// 	break;

				case 'select':
					echo '
					<div class="tmForm__inputWrap tmForm__inputWrap--select'.($row["id"] == 15 ? ' tmForm__inputWrap--selectAccent' : '' ).'">
						<select name="p'.$row["id"].'" class="tmForm__input tmForm__input--select">
							<option value="" selected disabled hidden>'.$row["name"].'</option>';
					foreach ($row["select_array"] as $select)
					{
						echo '<option value="'.$select["id"].'">'.$select["name"].'</option>';
					}
					echo '
						</select>
					</div>';
					break;

				// case 'multiple':
				// 	echo '<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>
				// 	<div class="field-d__list">';
				// 	foreach ($row["select_array"] as $select)
				// 	{
				// 		echo
				// 		'<div class="field-d__item">
				// 			<input name="p'.$row["id"].'[]" id="feedback_p'.$select["id"].'[]" value="'.$select["id"].'" type="checkbox">
				// 			<label for="feedback_p'.$select["id"].'[]">'.$select["name"].'</label>
				// 		</div>';
				// 	}
				// 	echo '</div>';
				// 	break;

				// case "attachments":
				// 	echo
				// 	'<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>
				// 	<label class="inpattachment">
				// 		<input type="file" name="attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'">
				// 		<label>'.$this->diafan->_('Прикрепить файл').'</label>
				// 	</label>
				// 	<label class="inpattachment" style="display:none">
				// 		<input type="file" name="hide_attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'">
				// 		<label>'.$this->diafan->_('Прикрепить файл').'</label>
				// 	</label>';
				// 	if ($row["attachment_extensions"])
				// 	{
				// 		echo '<div class="field-d__text attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$row["attachment_extensions"].')</div>';
				// 	}
				// 	break;

				// case "images":
				// 	echo
				// 	'<div class="field-d__name">'.$row["name"].($row["required"] ? '<span class="_asterisk"></span>' : '').':</div>
				// 	<div class="inpimage">
				// 		<div class="images"></div>
				// 		<input type="file" name="images'.$row["id"].'" param_id="'.$row["id"].'" class="inpimages">
				// 		<label>'.$this->diafan->_('Прикрепить изображение').'</label>
				// 	</div>';
				// 	break;
			}

			// if($row["text"])
			// {
			// 	echo '<div class="field-d__text feedback_form_param_text">'.$row["text"].'</div>';
			// }

			// if($row["type"] != 'title')
			// {
			// 	echo '<div class="errors error_p'.$row["id"].'"'.($result["error_p".$row["id"]] ? '>'.$result["error_p".$row["id"]] : ' style="display:none">').'</div>';
			// }

			// echo '</div>';
		}
	}

	if(! empty($result["rows"]))
	{
		foreach ($result["rows"] as $row) //вывод полей из конструктора форм
		{
			if($row["required"])
			{
				$required = true;
			}

			switch ($row["type"])
			{
				case "phone":
					echo '
					<div class="tmForm__inputWrap">
						<input type="tel" class="tmForm__input" name="p'.$row["id"].'" value="" placeholder="+7 (___) ___-__-__">
					</div>';
					break;

				case 'textarea':
					echo '
					<div class="tmForm__inputWrap tmForm__inputWrap--textarea">
						<textarea name="p'.$row["id"].'" class="tmForm__input tmForm__input--textarea" placeholder="'.$row["name"].($row["required"] ? '' : '').'"></textarea>
					</div>';
					break;
			}
		}
	}
	

	//Защитный код
	echo $result["captcha"];

	//Кнопка Отправить
	if(! empty($result["attributes"]["btn"]))
	{
		echo
		'<button type="submit" class="tmForm__btn">
			'.$result["attributes"]["btn"].'
		</button>';
	}else{
		echo
		'<button type="submit" class="tmForm__btn">
			Отправить заявку
		</button>';
	}


	// echo '
	// <div class="tmForm__accept tmAccept">
	// 	<label class="tmAccept__field">
	// 		<input class="visually-hidden" type="checkbox" checked="">
	// 		<span class="tmAccept__icon"></span>
	// 	</label>
	// 	Нажимая на кнопку вы соглашаетесь с <a class="link link--main" href="/privacy/">политикой конфиденциальности</a>
	// </div>';

	// if($required)
	// {
	// 	echo '<div class="required_field"><span class="_asterisk"></span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>';
	// }

echo '</form>';

// echo '<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';

// echo '</div>';
