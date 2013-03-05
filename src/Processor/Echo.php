<?php

class N7_Processor_Echo implements N7_ProcessorInterface
{
	public function write(N7_LogEntryInterface $entry)
	{
		echo "$entry\n";
	}
}