<?php


class Apache_Log_Parser
{
	protected $config = array(
		'regex' => '/\[([^\]]+)\]\(([^|]+)\|([^\)]+)\) \(([^\)]+)\) "([^ ]+) ([a-zA-Z]+)" ([0-9]{3}) "([^"]+)" ([0-9]+) ([0-9]+):([0-9]+) "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)"/',
		'parsed_keys' => array(
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
		),
		
		'custom_filter' => false,//use this to pass a callable to filter the resulting array
		
		'error_to'	=> false,//where to send error data to
		
		'pipe_to' => false//this should be something useful, or else it does nothing
	);
	
	protected $logger;
	
	protected $handle;
	
	protected $in;
	
	public function config(array $config)
	{
		$this->config = array_merge($this->config, $config);
	}
	
	public function __construct($logger, $listener = 'php://stdin')
	{
		$this->logger = $logger;
		
		//initialize a listener
		$this->handle = fopen($listener, 'r');
		$this->in = $listener;
	}
	
	public function listen()
	{
		$this->log("[INIT] Started listening for log messages on {$this->in} at " . date('Y-m-d H:i:s') . '. '
			. '(PID: ' . getmypid() . ', UID: ' . getmyuid() . ', GID: ' . getmygid() . ')');
		
		if (!$this->handle)
		{
			throw new RuntimeException('Could not listen since improperly initialized.');
		}
		
		while (!feof($this->handle))
		{
			$line = fgets($this->handle);
			$parsed = $this->parse($line);
			
			if ($this->config['pipe_to'])
			{
				call_user_func_array($this->config['pipe_to'], array($parsed));
			}
		}
		
		//cleanup
		fclose($this->handle);
		
		$this->log("[EXIT] Received EOF on {$this->in} at " . date('Y-m-d H:i:s') . '.');
	}
	
	public function log($msg)
	{
		call_user_func_array($this->logger, array($msg));
	}
	
	public function parse($str)
	{
		$regex = $this->config['regex'];
		
		$matches = array();
		if (!preg_match($regex, $str, $matches))
		{
			//line doesn't match
			if ($this->config['error_to'])
			{
				return call_user_func($this->config['error_to'], $str);
			}
		}
		
		//don't need the original
		unset($matches[0]);
		
		if (count($matches) != count($this->config['parsed_keys']))
		{
			//a problem
			if ($this->config['error_to'])
			{
				return call_user_func($this->config['error_to'], $matches);
			}
		}
		
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
		
		$parsed = array_combine($this->config['parsed_keys'], $clean);
		
		//do a final filter, if applicable
		if ($this->config['custom_filter'])
		{
			$parsed = call_user_func_array($this->config['custom_filter'], array($parsed));
		}
		
		return $parsed;
	}
}

