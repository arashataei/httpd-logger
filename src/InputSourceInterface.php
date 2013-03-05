<?php

interface N7_InputSourceInterface
{
	public function read();
	public function hasMore();
	public function close();
}