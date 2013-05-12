<?php

class N7_LogParser
{
	protected $source;
	protected $proc;
	protected $creator;
	protected $logger;
	
	public function __construct(
		N7_InputSourceInterface $source,
		N7_ProcessorInterface $proc,
		N7_LogEntryInterface $entry,
		N7_Psr_LoggerInterface $logger
	) {
		$this->source = $source;
		$this->proc = $proc;
		$this->creator = get_class($entry);
		$this->logger = $logger;
		
		$this->logger->info("Started httpd log listener (PID:" . getmypid() . ')');
		
		//the listener
		while ($this->source->hasMore())
		{
			//read the line and parse it
			$line = $this->source->read();
			
			//ignore blank lines
			$line = trim($line);
			if (empty($line))
			{
    			continue;
			}
			
			$entry = call_user_func(array("{$this->creator}", 'create'), $line);
			
			if ($entry)
			{
				$this->proc->write($entry);
			}
			else
			{
    			$this->logger->info("The line '$line' could not be parsed correctly.");
			}
		}
		
		$this->logger->info("Shutting down httpd log listener (PID:" . getmypid() . ')');
	}
	
}
