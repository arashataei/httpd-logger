<?php

class MongoLogger
{
	protected $mongo;
	protected $db;
	protected $col;
	
	public function __construct($constring = 'mongodb://localhost:27017', $db = 'log', $collection = 'log')
	{
		$this->mongo = new MongoClient($constring);
		$this->db = $this->mongo->selectDB($db);
		$this->col = $this->db->$collection;
	}
	
	public function write($log)
	{
		if (!is_array($log)) return;
		
		$this->col->insert($log, array('safe' => false));
	}
	
	public function pull()
	{
		$obj = $this->col->findOne();
		$id = $obj['_id'];
		$this->col->remove(array('_id' => $id));
		return $obj;
	}
	
	public function hasMore()
	{
		return $this->col->count();
	}
}



class LogTransfer
{
	protected $insert_query;
	
	protected $mysql;
	
	public function __construct($host, $user, $password, $db, $port = 3306)
	{
		ini_alter('mysqli.allow_persistent', true);
		
		//prep the insert statement
		$cols = array(
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
			'luid'
		);
		
		$this->insert_query = 'INSERT DELAYED INTO `log` (';
		$temp_cols = array();
		
		foreach ($cols as $col)
		{
			$temp_cols[] = "`$col`";
		}
		
		$this->insert_query .= implode(', ', $temp_cols) . ') VALUES (';
		
		$this->mysql = new mysqli($host, $user, $password, $db, $port);
		
		if ($this->mysql->connect_errno)
		{
			throw new RuntimeException("Connection to $host/$db failed with code #{$this->mysql->connect_errno}.");
		}
	}
	
	public function write($log = null)
	{
		if (!is_array($log) || count($log) != 16)
			return false;
		
		$query = $this->insert_query;
		
		$tmp = array();
		foreach ($log as $key => $value)
		{
			if ($value == null)
				$tmp[] = 'NULL';
			else
				$tmp[] = "'" . $this->mysql->real_escape_string($value) . "'";
		}
		
		$query .= implode(', ', $tmp) . ');';
		
		$this->mysql->real_query($query);
	}
}


function m($bytes)
{
	$bytes = $bytes/1024;
	$b = 'KiB';
	
	if ($bytes > 1024)
	{
		$bytes = $bytes/1024;
		$b = 'MiB';
	}
	
	return number_format($bytes, 2) . " $b";
}


function mStat()
{
	echo '[' . date('Y-m-d H:i:s') . "]\n";
	echo "-- Current stats --\n";
	
	$c = m(memory_get_usage(true));
	$p = m(memory_get_peak_usage());
	
	echo "Memory: $c\n";
	echo "Peak:   $p\n";
	
	echo "--------------------\n\n";
}

$m = new MongoLogger();
$ticks = 0;
$perObjects = 100;
$perTicks = 2048;

while (true)
{
	echo '[' . date('Y-m-d H:i:s') . "] Waking up!\n";
	
	while ($m->hasMore())
	{
		$obj = $m->pull();
		//echo "Proc " . $obj['_id'] . "\n";
		//echo '*';
		
		if ($ticks % $perObjects == 00)
			echo "#";
		
		$ticks++;
		
		
		if ($ticks == $perTicks)
		{
			$ticks = 0;
			//mStat();
		}
		
		
	}
	echo "\n";
	
	mStat();
	
	echo "No data, will sleep for a few seconds…\n\n";
	sleep(rand(1,4));
}

