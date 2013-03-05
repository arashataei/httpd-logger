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
		
		//the listener
		while ($this->source->hasMore())
		{
			//read the line and parse it
			$line = $this->source->read();
			$c = $this->creator;
			$entry = call_user_func(array("$c", 'create'), $line);
			
			if ($entry)
			{
				$this->proc->write($entry);
			}
			
			$this->logger->debug($line);
		}
	}
	
}
