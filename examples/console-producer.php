<?php

$opts = getopt("", array(
    'zmq:',
    'verbose',
    'logLevel:',

    'simulate:',
        'keyPrefix:',
        'keyDistribution:',
        'numMessages:',
        'expMinMs:',
        'expMaxMs:',
        'sleepUs:'
));

if (empty($opts)) {
    echo "Example: php console-producer.php --verbose --simulate 1 --keyPrefix mk --keyDistribution 100000 --numMessages 100000 --expMinMs 3000000 --expMaxMs 3000000 --sleepUs 1\n";
    exit(0);
}

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : 'tcp://127.0.0.1:4444';
$stdIn = isset($opts['stdin']) ? true : false;
$verbose = isset($opts['verbose']) ? true : false;
$logLevel = isset($opts['logLevel']) ? (string)$opts['logLevel'] : 'error';

$simulate = isset($opts['simulate']) ? true : false;
$keyPrefix = isset($opts['keyPrefix']) ? (string)$opts['keyPrefix'] : false;
$keyDistribution = isset($opts['keyDistribution']) ? (int)$opts['keyDistribution'] : false;
$numMessages = isset($opts['numMessages']) ? (int)$opts['numMessages'] : false;
$expMinMs = isset($opts['expMinMs']) ? (int)$opts['expMinMs'] : false;
$expMaxMs = isset($opts['expMaxMs']) ? (int)$opts['expMaxMs'] : false;
$sleepMicroSeconds = isset($opts['sleepUs']) ? (int)$opts['sleepUs'] : false;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');

if ($verbose) {
    $logLevel = 'debug';
}

$producer = Awdn\VigilantQueue\Producer\ConsoleProducer::factory(
    $zmq,
    \Awdn\VigilantQueue\Utility\ConsoleLog::loggerFactory('ConsoleProducer', $logLevel),
    $verbose
);

if ($simulate) {
    // Generate a bunch of messages and send them to the inbound queue of the deferred server.
    $producer->simulate($keyPrefix, $keyDistribution, $numMessages, $expMinMs, $expMaxMs, $sleepMicroSeconds);
} else {
    // Get messages from readline and write to inbound queue of the server.
    $producer->produce();
}


