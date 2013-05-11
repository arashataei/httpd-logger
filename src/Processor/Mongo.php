<?php

class N7_Processor_Mongo implements N7_ProcessorInterface
{
	protected $mongo;
	protected $db;
	protected $col;
	protected $serverId;
	
	protected $timeout;
	protected $logger;
	
	public function __construct(
		$constring = 'mongodb://localhost:27017',
		$db = 'log',
		$collection = 'log',
		N7_Psr_LoggerInterface $logger,
		$serverId = null,
		$timeout = 60
	)
	{
		$this->logger = $logger;
		$this->timeout = $timeout;
		$connected = false;
		$started = microtime(true);
		$wait = 1;//how many seconds to sleep for
		$waited = 0;
		
		while (!$connected)
		{
			try
			{
				$this->mongo = new MongoClient($constring);
				$this->db = $this->mongo->selectDB($db);
				$this->col = $this->db->$collection;
				
				if ($serverId)
					$this->serverId = $serverId;
				
				$connected = true;
			}
			catch (MongoConnectionException $e)
			{
				//keep a counter
				$waited = number_format(microtime(true) - $started,3);
				
				//we should sleep for some time and try again
				$logger->error("Waited $waited seconds. Could not connect to $constring. "
					. "Will try waiting $wait second(s) and try again.");
				
				//now we wait...
				sleep($wait);
			}
		}
		
		if ($waited)
		{
			$logger->info("And we're back! Resumed connection to $constring.");
		}
	}
	public function write(N7_LogEntryInterface $entry)
	{
		$map = $entry->getDataMap();
		
		foreach ($map as $key => $val)
		{
			if ($val === null)
				unset($map[$key]);
			else
				$map[$key] = utf8_encode($val);
		}
		
		if ($this->serverId)
		  $map['server_id'] = utf8_encode($this->serverId);
		
		//make a few tweaks
		$map['timestamp'] = new MongoDate($map['timestamp']);
		
		$succeeded = false;
		$start = microtime(true);
		
		while (!$succeeded)
		{
			try
			{
				$this->col->insert($map);
				$succeeded = true;
			}
			catch (MongoException $e)
			{
				//log the issue and retry for a few times
				$this->logger->error("While trying to write a row, we received an exception: "
					. get_class($e) . ', code:' . $e->getCode() . ', msg:' . $e->getMessage());
				
				//how long did we wait?
				$waited = microtime(true) - $start;
				
				sleep(1);
				
				if ($waited > $this->timeout)
				{
					//can't wait forever, so finish
					return;
				}
			}
		}
	}
}