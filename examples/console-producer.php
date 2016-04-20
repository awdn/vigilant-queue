<?php

$opts = getopt("", array(
    'zmq:',
    'debug:',
    'stdin:', // does not work yet

    'simulate:',
        'keyPrefix:',
        'keyDistribution:',
        'numMessages:',
        'expMinMs:',
        'expMaxMs:',
        'sleepUs:'
));

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : false;
$stdIn = isset($opts['stdin']) ? true : false;
$debug = isset($opts['debug']) ? (boolean)$opts['debug'] : false;

$simulate = isset($opts['simulate']) ? true : false;
$keyPrefix = isset($opts['keyPrefix']) ? (string)$opts['keyPrefix'] : false;
$keyDistribution = isset($opts['keyDistribution']) ? (int)$opts['keyDistribution'] : false;
$numMessages = isset($opts['numMessages']) ? (int)$opts['numMessages'] : false;
$expMinMs = isset($opts['expMinMs']) ? (int)$opts['expMinMs'] : false;
$expMaxMs = isset($opts['expMaxMs']) ? (int)$opts['expMaxMs'] : false;
$sleepMicroSeconds = isset($opts['sleepUs']) ? (int)$opts['sleepUs'] : false;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');

if (!$zmq) {
    //$zmq = 'ipc://ipc-handle-deferred-queue.ipc';
    $zmq = 'tcp://127.0.0.1:4444';
}

//-$keyPrefix, $keyDistribution, $numMessages, $expMinMs, $expMaxMs

if ($simulate) {
    Awdn\VigilantQueue\Producer\ConsoleProducer
        ::factory($zmq, $stdIn, $debug)
        ->simulate($keyPrefix, $keyDistribution, $numMessages, $expMinMs, $expMaxMs, $sleepMicroSeconds);
} else {
    Awdn\VigilantQueue\Producer\ConsoleProducer
        ::factory($zmq, $stdIn, $debug)
        ->produce();
}


