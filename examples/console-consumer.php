<?php

$opts = getopt("", array('zmq:', 'debug:'));

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : false;
$debug = isset($opts['debug']) ? (boolean)$opts['debug'] : false;

require_once(DIRNAME(__FILE__) . '/../../vendor/autoload.php');


if (!$zmq) {
    $zmq = 'tcp://127.0.0.1:5444';
}

Awdn\VigilantQueue\Consumer\ConsoleConsumer
    ::factory($zmq, $debug)
    ->consume();


