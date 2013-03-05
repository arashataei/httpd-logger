<?php

/**
 * An interface that defines how a data processor should act AFTER collecting
 * data from Apache.
 */

interface N7_ProcessorInterface
{
	public function write(N7_LogEntryInterface $data);
}
