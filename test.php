<?php

ini_alter('display_errors', 'On');
error_reporting(-1);

require_once 'Apache_Log_Parser.php';


function myFilter($s)
{
	/*
	if (array_key_exists('timestamp', $stuff))
		$stuff['timestamp'] = strtotime($stuff['timestamp']);
	*/
	$s['session_id'] = str_replace('PHPSESSID:', '', $s['session_id']);
	if ($s['session_id'] == '-')
		$s['session_id'] = null;
	
	$s['luid'] = str_replace('luid:', '', $s['luid']);
	if ($s['luid'] == '-')
		$s['luid'] = null;
	
	return $s;
}


function tosql($stuff)
{
	print_r($stuff);
}

function err($p = null)
{
	echo "*** Error! (" . var_export($p, true) . ")\n\n";
}

function myLog($msg)
{
	echo "$msg\n";
}

class MysqlLog
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
		
		$this->insert_query = 'INSERT INTO `log` (';
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
}

//$mysql = new MysqlLog('tomato.ujafedny.com', 'apache_log', 'logtastic', 'apache_log');
//$mongo = new MongoLogger();

$mysql2 = new MysqlLog('127.0.0.1', 'apache_log', 'logtastic', 'apache_log');

$start = microtime(true);
myLog("Memory start: " . memory_get_usage(true)/1024);


$p = new Apache_Log_Parser('myLog', 'php://stdin');
$p->config(array(
	'pipe_to' => array($mysql2, 'write'),
	'custom_filter' => 'myFilter',
	'error_to' => 'err'
));
$p->listen();

$end = microtime(true);

echo "Time: " . number_format(($end-$start), 3) . "\n\n";
myLog("Memory end: " . memory_get_usage(true)/1024);



