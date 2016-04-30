<?php
$parameterList = array(
    'zmqOut:' => '(string) Outbound ZMQ address. Default is tcp://127.0.0.1:5444. ',
    'zmqIn:' => '(string) Outbound ZMQ address. Default is tcp://127.0.0.1:4444.',
    'verbose' => 'Enables debug messages.',
    'evictionTickrate:' => '(int) Determines how often per second the eviction event is fired. Defaults to 1000.',
    'dataModeAppend:' => '(string) Sets the data mode to append data for already existing entries within the queue instead of replacing them.'
);

$opts = getopt("", array_keys($parameterList));

if (empty($opts)) {
    echo "Usage: \n";
    foreach ($parameterList as $param => $description) {
        $_param = rtrim($param, ':');
        echo "  --{$_param}\n    {$description}\n";
    }
    exit(0);
}


$zmqOut = isset($opts['zmqOut']) ? (string)$opts['zmqOut'] : 'tcp://127.0.0.1:5444';
$zmqIn = isset($opts['zmqIn']) ? (int)$opts['zmqIn'] : 'tcp://127.0.0.1:4444';
$verbose = isset($opts['verbose']) ? true : false;
$evictionTicksPerSec = isset($opts['evictionTickrate']) ? (int)$opts['evictionTickrate'] : 1000;
$dataModeAppend = isset($opts['dataModeAppend']) ? (array)$opts['dataModeAppend'] : false;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');


$server = Awdn\VigilantQueue\DeferredQueueServer::factory($zmqIn, $zmqOut, $evictionTicksPerSec, $verbose);
foreach ($dataModeAppend as $messageType) {
    $server->setDataModeByType($messageType, \Awdn\VigilantQueue\Queue\PriorityHashQueue::DATA_MODE_APPEND);
}
$server->run();


