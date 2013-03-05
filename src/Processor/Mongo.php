<?php

class N7_Processor_Mongo implements N7_ProcessorInterface
{
	protected $mongo;
	protected $db;
	protected $col;
	protected $serverId;
	
	public function __construct($constring = 'mongodb://localhost:27017', $db = 'log', $collection = 'log', $serverId = null)
	{
		$this->mongo = new MongoClient($constring);
		$this->db = $this->mongo->selectDB($db);
		$this->col = $this->db->$collection;
		
		if ($serverId)
		  $this->serverId = $serverId;
	}
	public function write(N7_LogEntryInterface $entry)
	{
		$map = $entry->getDataMap();
		
		foreach ($map as $key => $val)
		{
			if ($val === null)
				unset($map[$key]);
		}
		
		$map['server_id'] = $this->serverId;
		
		//make a few tweaks
		$map['timestamp'] = new MongoDate($map['timestamp']);
		
		$this->col->insert($map);
	}
}