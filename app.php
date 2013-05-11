<?php

include 'src/autoload.php';

$logger = new N7_FileLogger('app.log');

$app = new N7_LogParser(
	new N7_DataSource_Stdin(),
	new N7_Processor_Mongo('mongodb://localhost', 'log', 'log', $logger, 'myHostId'),
	new N7_LogFormat_Super(),
	$logger
);



