<?php

class N7_LogFormat_Super implements N7_LogEntryInterface
{
	protected static $regex = '/\[([^\]]+)\]\(([^|]+)\|([^\)]+)\) \(([^\)]+)\) "HTTP\/([^ ]+) ([a-zA-Z]+)" ([0-9]{3}) "([^"]+)" ([0-9]+) ([0-9]+):([0-9]+) "([^"]*)" "([^"]*)" "([^"]*)" "PHPSESSID:([^"]*)" "luid:([^"]*)"/';
	
	protected static $parsed_keys = array(
		'timestamp',
		'client_ip',
		'ip',
		'domain',
		'http_version',
		'method',
		'code',
		'uri',
		'proc_time',
		'bytes_in',
		'bytes_out',
		'query_string',
		'user_agent',
		'referrer',
		'session_id',
		'luid',
	);
	
	protected $data = array();
	
	public static function regex()
	{
		return self::$regex;
	}
	
	public static function create($line)
	{
		$matches = array();
		
		if (!preg_match(self::$regex, $line, $matches))
		{
			return false;
		}
		else
		{
			//dont need original string
			unset($matches[0]);
			
			//same number of keys necessary
			if (count($matches) != count(self::$parsed_keys))
				return false;
			
			$clean = array();
			
			foreach ($matches as $match)
			{
				if ($match == '-' || $match == '')
				{
					$clean[] = null;
				}
				elseif (preg_match('/^[0-9]+$/', $match))
				{
					$clean[] = (int) $match;
				}
				else
				{
					$clean[] = urldecode(stripcslashes($match));
				}
			}
			
			$inst = new self;
			$inst->data = array_combine(self::$parsed_keys, $clean);
			
			$inst->data['timestamp'] = strtotime($inst->data['timestamp']);
			
			return $inst;
		}
	}
	
	public function getTimestamp()
	{
		return $this->data['timestamp'];
	}
	
	
	public function getClientAddress()
	{
		return $this->data['client_ip'];
	}
	
	
	public function getHostAddress()
	{
		return $this->data['ip'];
	}
	
	
	public function getDomain()
	{
		return $this->data['domain'];
	}
	
	
	public function getHttpVersion()
	{
		return $this->data['http_version'];
	}
	
	
	public function getHttpMethod()
	{
		return $this->data['method'];
	}
	
	
	public function getHttpCode()
	{
		return $this->data['code'];
	}
	
	
	public function getUri()
	{
		return $this->data['uri'];
	}
	
	
	public function getProcTime()
	{
		return $this->data['proc_time'];
	}
	
	
	public function getBytesIn()
	{
		return $this->data['bytes_in'];
	}
	
	
	public function getBytesOut()
	{
		return $this->data['bytes_out'];
	}
	
	
	public function getQuery()
	{
		return $this->data['query_string'];
	}
	
	
	public function getUserAgent()
	{
		return $this->data['user_agent'];
	}
	
	
	public function getReferrer()
	{
		return $this->data['referrer'];
	}
	
	
	public function getSessionId()
	{
		return $this->data['session_id'];
	}
	
	
	public function getCookie($name)
	{
		if (array_key_exists($name, $this->data))
			return $this->data[$name];
		else
			return false;
	}
	
	public function getDataMap()
	{
		return $this->data;
	}
	
	public function __toString()
	{
		return date('Y-m-d H:i:s', $this->getTimestamp()) . ' - ' . $this->getClientAddress();
	}
}