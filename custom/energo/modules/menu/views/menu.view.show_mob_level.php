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

if (empty($result["rows"][$result["parent_id"]]))
{
	return true;
}


foreach ($result["rows"][$result["parent_id"]] as $row)
{
	if ( $row["link"] == 'services/' ) {
		echo $this->htmleditor('<insert name="show_block" module="menu" id="2" template="mob_service" />');
	}
}

