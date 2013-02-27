<?php

/**
 * Basically a log entry, as in a single line, where access is defined.
 */

interface N7_LogEntryInterface
{
	public static function regex();
	public static function create($line);
	
	public function getTimestamp();
	public function getClientAddress();//X-Forwarded-For header if applicable
	public function getHostAddress();//usually a Load Balancer
	public function getDomain();
	public function getHttpVersion();
	public function getHttpMethod();
	public function getHttpCode();
	public function getUri();
	public function getProcTime();
	public function getBytesIn();
	public function getBytesOut();
	public function getQuery();
	public function getUserAgent();
	public function getReferrer();
	public function getSessionId();
	public function getCookie($name);
	
	public function getDataMap();
}

