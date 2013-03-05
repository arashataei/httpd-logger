<?php

function httpd_logger_autoload($class)
{
	$class = trim($class);
	$file = dirname(__FILE__) . DIRECTORY_SEPARATOR;
	
	if (strpos($class, '_'))
	{
		$struct = explode('_', $class);
		
		if ($struct[0] != 'N7')
			return false;
		
		//the first piece is the vendor namespace, so we don't need that
		unset($struct[0]);
		
		$struct = implode(DIRECTORY_SEPARATOR, $struct);
		$file .= $struct . '.php';
	}
	else
	{
		$file .= $class . '.php';
	}
	
	if (!is_readable($file))
	{
		return false;
	}
	else
	{
		require($file);
		return true;
	}
	
}

//i can has autoloads
spl_autoload_register('httpd_logger_autoload');