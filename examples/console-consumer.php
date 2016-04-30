<?php

$opts = getopt("", array('zmq:', 'verbose'));

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : false;
$verbose = isset($opts['verbose']) ? true : false;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');


if (!$zmq) {
    $zmq = 'tcp://127.0.0.1:5444';
}

Awdn\VigilantQueue\Consumer\ConsoleConsumer
    ::factory($zmq, $verbose)
    ->consume();


