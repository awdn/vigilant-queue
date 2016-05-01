<?php

$parameterList = array(
    'zmqOut:' => '(string) Outbound ZMQ address. Default is tcp://127.0.0.1:5444. ',
    'zmqIn:' => '(string) Outbound ZMQ address. Default is tcp://127.0.0.1:4444.',
    'logLevel:' => 'Sets the minimum debug level. This is one of debug, info, warn, error.',
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

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');


$config = new \Awdn\VigilantQueue\Server\Config();
$config
    ->setMinimumLogLevel    (isset($opts['logLevel'])        ? (string)$opts['logLevel']      : 'error')
    ->setEvictionTicksPerSec(isset($opts['evictionTickrate']) ? (int)$opts['evictionTickrate'] : 1000)
    ->setZmqIn              (isset($opts['zmqIn'])            ? (int)$opts['zmqIn']            : 'tcp://127.0.0.1:4444')
    ->setZmqOut             (isset($opts['zmqOut'])           ? (string)$opts['zmqOut']        : 'tcp://127.0.0.1:5444');


$server = Awdn\VigilantQueue\Server\DeferredQueue::factory(
    $config,
    \Awdn\VigilantQueue\Utility\ConsoleLog::loggerFactory('DeferredQueue', $config->getMinimumLogLevel())
);

// For the following request message types, we change the behaviour of the queue to append the data to existing keys
// instead of replacing them.
$dataModeAppend = isset($opts['dataModeAppend']) ? (array)$opts['dataModeAppend'] : array();
foreach ($dataModeAppend as $messageType) {
    $server->setDataModeByType($messageType, \Awdn\VigilantQueue\Queue\PriorityHashQueue::DATA_MODE_APPEND);
}

$server->run();


