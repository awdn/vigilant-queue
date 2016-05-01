<?php

$opts = getopt("", array('zmq:', 'verbose', 'logLevel:'));

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : false;
$verbose = isset($opts['verbose']) ? true : false;
$logLevel = isset($opts['logLevel']) ? (string)$opts['logLevel'] : false;
if ($verbose) {
    $logLevel = 'debug';
}

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');


if (!$zmq) {
    $zmq = 'tcp://127.0.0.1:5444';
}

Awdn\VigilantQueue\Consumer\ConsoleConsumer
    ::factory(
        $zmq,
        \Awdn\VigilantQueue\Utility\ConsoleLog::loggerFactory('ConsoleConsumer', $logLevel),
        $verbose
    )->consume();


