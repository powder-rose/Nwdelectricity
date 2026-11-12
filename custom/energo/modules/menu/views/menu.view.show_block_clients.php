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

if (empty($result["rows"]))
{
	return false;
}

echo '<div class="clients">';
	if (!empty($result["name"]))
	{
		echo '<div class="section-title">'.$result["name"].'</div>';
	}

	echo '<ul class="clients__list">';
	foreach ($result["rows"][$result["parent_id"]] as $row)
	{
		echo '<li class="clients__item">';
			if (! empty($row["img"]))
			{
				echo '<img class="clients__preview" src="'.$row["img"]["src"].'">';
			}
		echo '</li>';
	}
	echo '</ul>';
echo '</div>';

