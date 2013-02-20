<?php


class ApacheLogParser
{
	const DATE_REGEX	= '[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}';
	const DOMAIN_REGEX	= '([a-zA-Z0-9\-_]+\.)+[a-zA-Z0-9\-_]+';
	const IP_REGEX		= '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
	
	public static function parse($str)
	{
		/*
		$regex = '/\[(' . self::DATE_REGEX . ')\]\((-|' . self::IP_REGEX . ')\|(-|' . self::IP_REGEX . ')\) '
			. '\((' . self::IP_REGEX . '|' . self::DOMAIN_REGEX . ')\)'
			. '/';
			*/
		//$regex = "/\((-|" . self::IP_REGEX . ")\|(-|" . self::IP_REGEX . ")\)/";
		
		$regex = '/\[([^\]]+)\]\(([^|]+)\|([^\)]+)\) \(([^\)]+)\) "([^ ]+) ([a-zA-Z]+)" ([0-9]{3}) "([^"]+)" ([0-9]+) '
			. '([0-9]+):([0-9]+) "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)"/';
		
		$matches = array();
		preg_match($regex, $str, $matches);
		
		//we need a reliable format here
		if (!is_array($matches) || count($matches) != 17)
		{
			return false;
		}
		
		$clean = array();
		foreach ($matches as $match)
		{
			if ($match == '-')
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
		
		$parsed = array(
			'timestamp'		=> strtotime($clean[1]),
			'client_ip'		=> $clean[2],
			'ip'			=> $clean[3],
			'domain'		=> $clean[4],
			'http_version'	=> $clean[5],
			'method'		=> $clean[6],
			'code'			=> $clean[7],
			'uri'			=> $clean[8],
			'proc_time'		=> $clean[9],
			'bytes_in'		=> $clean[10],
			'bytes_out'		=> $clean[11],
			'query_string'	=> $clean[12],
			'user_agent'	=> $clean[13],
			'referrer'		=> $clean[14],
			'session_id'	=> $clean[15],
			'luid'			=> $clean[16],
		);
		
		return $parsed;
	}
}

function logmsg($m)
{
	$lp = fopen('0test.log', 'a');
	$m .= PHP_EOL;
	$w = fwrite($lp, $m, strlen($m));
	fclose($lp);
	return $w;
}

logmsg("[LOG INIT] log(" . getmypid() . '), ' . getmyuid() . ':' . getmygid() . ')  ');

$fp = fopen('php://stdin', 'r');

$stats = array();

$total_len = 0;

while (!feof($fp))
{
	$s = fgets($fp);
	$total_len += strlen($s);
	
	//echo "Original: $s" . PHP_EOL;
	$parsed = ApacheLogParser::parse($s);
	//print_r($parsed);
	
	logmsg("[LOG] Received {$parsed['timestamp']} entry.");
	
	//let's do some math
	$key = $parsed['uri'] ;//. $parsed['query_string'];
	if (!array_key_exists($key, $stats))
	{
		$stats[$key] = array(
			'total_time' => 0,
			'count' => 0
		);
	}
	
	$stats[$key]['total_time'] += $parsed['proc_time'];
	$stats[$key]['count']++;
}

logmsg("[LOG KILL] I guess we're done now.");

/*
foreach ($stats as $uri => $stat)
{
	$avg = number_format(($stat['total_time'] / $stat['count'])/1000, 0) . 'ms';
	echo "URI:			$uri\n";
	echo "Avg proc time:		$avg\n";
	echo "Hits:			{$stat['count']}\n\n";
}
*/

