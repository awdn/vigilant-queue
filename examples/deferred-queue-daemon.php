<?php

$opts = getopt("", array('port:', 'zmqOut:', 'zmqIn:', 'debug:', 'udp:', "benchmark:", "timeout:", 'evictionTickrate:'));

$zmqOut = isset($opts['zmqOut']) ? (string)$opts['zmqOut'] : false;
$zmqIn = isset($opts['zmqIn']) ? (int)$opts['zmqIn'] : false;
$debug = isset($opts['debug']) ? (boolean)$opts['debug'] : false;
$evictionTicksPerSec = isset($opts['evictionTickrate']) ? (int)$opts['evictionTickrate'] : 1000;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');

if (!$zmqOut) {
    //$zmq = 'ipc://ipc-handle-deferred-queue.ipc';
    $zmqOut = 'tcp://127.0.0.1:5444';
}

if (!$zmqIn) {
    $zmqIn = 'tcp://127.0.0.1:4444';
}


Awdn\VigilantQueue\DeferredQueueServer
    ::factory($zmqIn, $zmqOut, $evictionTicksPerSec, $debug)
    ->run();


