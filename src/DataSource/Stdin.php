<?php

class N7_DataSource_Stdin extends N7_DataSource_File
{
	public function __construct()
	{
		parent::__construct('php://stdin');
	}
}