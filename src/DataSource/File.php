<?php

class N7_DataSource_File implements N7_InputSourceInterface
{
	protected $fp;
	
	public function __construct($file)
	{
		$this->fp = fopen($file, 'r');
	}
	
	public function read()
	{
		return fgets($this->fp);
	}
	
	public function hasMore()
	{
		return !feof($this->fp);
	}
	
	public function close()
	{
		fclose($this->fp);
	}
}
