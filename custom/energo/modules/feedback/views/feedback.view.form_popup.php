<?php

if (! defined('DIAFAN'))
{
	$path = __FILE__; $i = 0;
	while(! file_exists($path.'/includes/404.php'))
	{
		if($i == 10) exit; $i++;
		$path = dirname($path);
	}
	include $path.'/includes/404.php';
}

if (! empty($result["text"]))
{
	echo $result["text"];
	return;
}


echo '<div id="modal" class="modal">';
	
	echo '
	<button type="button" data-fancybox-close class="modal__close">
		<span class="visually-hidden">Закрыть окно</span>
		<span class="modal__close-icon"></span>
	</button>';

	echo '<div class="modal__content">';

		echo '
		<form method="POST" enctype="multipart/form-data" action="" class="form form--vertical ajax">
		<input type="hidden" name="module" value="feedback">
		<input type="hidden" name="action" value="add">
		<input type="hidden" name="form_tag" value="' . $result["form_tag"] . '">
		<input type="hidden" name="site_id" value="' . $result["site_id"] . '">
		<input type="hidden" name="add_c" value="4l">
		<input type="hidden" name="tmpcode" value="' . md5(mt_rand(0, 9999)) . '">';

		echo '<div class="form__wrapper">';

			if (!empty($result["name"])) {
				echo '<p class="form__title">' . $result["name"] . '</p>';
			}

			$required = false;
			if (!empty($result["rows"])) 
			{
				foreach ($result["rows"] as $row) //вывод полей из конструктора форм
				{
					if ($row["required"]) {
						$required = true;
					}
					echo '<p class="form__item ' . $row["id"] . '">';

					switch ($row["type"]) {
						case 'title':
							echo '<div class="infoform">' . $row["name"] . ':</div>';
							break;

						case 'text':
							echo '
									<label class="visually-hidden" for=""p' . $row["id"] . '">' . $row["name"] . '</label>
									<input class="form__field form__field-decor form__field-decor--name" type="text" name="p' . $row["id"] . '" value="" placeholder="' . $row["name"] . '">';
							break;

						case "email":
							echo '
									<label class="visually-hidden" for=""p' . $row["id"] . '">' . $row["name"] . '</label>
									<input class="form__field form__field-decor form__field-decor--mail" type="email" name="p' . $row["id"] . '" value="" placeholder="' . $row["name"] . '">';
							break;

						case "phone":
							echo '
									<label class="visually-hidden" for=""p' . $row["id"] . '">' . $row["name"] . '</label>
									<input class="form__field form__field-decor form__field-decor--phone" type="tel" name="p' . $row["id"] . '" value="" placeholder="+7 (___) ___ - __ - __">';
							break;

						case 'textarea':
							echo '
									<label class="visually-hidden" for=""p' . $row["id"] . '">' . $row["name"] . '</label>
									<textarea class="form__field form__field--message form__field-decor form__field-decor--message" name="p' . $row["id"] . '" placeholder="' . $row["name"] . '"></textarea>';
							break;

							// case 'date':
							// case 'datetime':
							// 	$timecalendar  = true;
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
							// 		<input type="text" name="p'.$row["id"].'" value="" class="timecalendar" showTime="'
							// 		.($row["type"] == 'datetime'? 'true' : 'false').'">';
							// 	break;

							// case 'numtext':
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
							// 	<input type="number" name="p'.$row["id"].'" value="">';
							// 	break;

							// case 'checkbox':
							// 	echo '<input name="p'.$row["id"].'" id="feedback_p'.$row["id"].'" value="1" type="checkbox" ><label for="feedback_p'.$row["id"].'">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').'</label>';
							// 	break;

							// case 'radio':
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
							// 	foreach ($row["select_array"] as $select)
							// 	{
							// 		echo '<input name="p'.$row["id"].'" type="radio" value="'.$select["id"].'" id="feedback_form_p'.$row["id"].'_'.$select["id"].'"> <label for="feedback_form_p'.$row["id"].'_'.$select["id"].'">'.$select["name"].'</label>';
							// 	}
							// 	break;

							// case 'select':
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
							// 	<select name="p'.$row["id"].'" class="inpselect">
							// 		<option value="">-</option>';
							// 	foreach ($row["select_array"] as $select)
							// 	{
							// 		echo '<option value="'.$select["id"].'">'.$select["name"].'</option>';
							// 	}
							// 	echo '</select>';
							// 	break;

							// case 'multiple':
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
							// 	foreach ($row["select_array"] as $select)
							// 	{
							// 		echo '<input name="p'.$row["id"].'[]" id="feedback_p'.$select["id"].'[]" value="'.$select["id"].'" type="checkbox"><label for="feedback_p'.$select["id"].'[]">'.$select["name"].'</label><br>';
							// 	}
							// 	break;

							// case "attachments":
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
							// 	echo '<div class="inpattachment"><input type="file" name="attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
							// 	echo '<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
							// 	if ($row["attachment_extensions"])
							// 	{
							// 		echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$row["attachment_extensions"].')</div>';
							// 	}
							// 	break;

							// case "images":
							// 	echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div><div class="images"></div>';
							// 	echo '<input type="file" name="images'.$row["id"].'" param_id="'.$row["id"].'" class="inpimages">';
							// 	break;
					}

					if ($row["text"]) {
						echo '<div class="feedback_form_param_text">' . $row["text"] . '</div>';
					}
					if ($row["type"] != 'title') {
						echo '<span class="errors error_p' . $row["id"] . '"' . ($result["error_p" . $row["id"]] ? '>' . $result["error_p" . $row["id"]] : ' style="display:none">') . '</span>';
					}
					echo '</p>';
				}
			}


			if ( !empty($result["captcha"]) )
				echo '<p>'.$result["captcha"].'</p>';


			//Кнопка Отправить
			echo '<button type="submit" class="form__button button">';
				if ( !empty($result["attributes"]["btn"]) ) 
				{
					echo $result["attributes"]["btn"];
				}
				else 
				{
					echo 'Подать заявку';
				}
			echo '</button>';

			echo '
			<p class="form__confirm">
				<span class="form__confirm-field">
					<input class="visually-hidden" id="confirm2" type="checkbox" checked>
					<label class="form__confirm-label" for="confirm2"></label>
				</span>
					Нажимая на кнопку вы соглашаетесь с <a href="/privacy/">политикой конфиденциальности</a>
			</p>';

		echo '</div>';
	echo '</form>';
	echo '<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">'). '</div>';
echo '</div>';