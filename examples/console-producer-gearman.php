<?php

$opts = getopt("", array(
    'zmq:',
    'gearman:',
    'verbose',
    'keyPrefix:',
    'keyDistribution:',
    'numMessages:',
    'expMinMs:',
    'expMaxMs:',
    'sleepUs:',
));

if (empty($opts)) {
    echo "Example: php console-producer-gearman.php --expMinMs 3000000 --expMaxMs 15000000 --keyPrefix \"mk_\" --keyDistribution 2 --numMessages 1000 --sleepUs 0 --verbose\n";
    exit(0);
}

$zmq = isset($opts['zmq']) ? (string)$opts['zmq'] : false;
$gearman = isset($opts['gearman']) ? (string)$opts['gearman'] : false;
$verbose = isset($opts['verbose']) ? true : false;

$keyPrefix = isset($opts['keyPrefix']) ? (string)$opts['keyPrefix'] : '';
$keyDistribution = isset($opts['keyDistribution']) ? (int)$opts['keyDistribution'] : 1;
$numMessages = isset($opts['numMessages']) ? (int)$opts['numMessages'] : 1;
$expMinMs = isset($opts['expMinMs']) ? (int)$opts['expMinMs'] : 0;
$expMaxMs = isset($opts['expMaxMs']) ? (int)$opts['expMaxMs'] : 0;
$sleepMicroSeconds = isset($opts['sleepUs']) ? (int)$opts['sleepUs'] : false;

require_once(DIRNAME(__FILE__) . '/../vendor/autoload.php');

if (!$zmq) {
    //$zmq = 'ipc://ipc-handle-deferred-queue.ipc';
    $zmq = 'tcp://127.0.0.1:4444';
}
if (!$gearman) {
    $gearman = '127.0.0.1:4730';
}

list($gIp, $gPort) = explode(':', $gearman);
$gearmanQueueName = 'test_queue_' . md5(microtime());

// Create parent and child process to be able to create messages while listening on them.
if (pcntl_fork() == 0) {
    // Setup the gearman client and feed some data into the queue
    // Gearman Queue name

    $gearmanClient = new GearmanClient();
    $gearmanClient->addServer($gIp, (int)$gPort);

    // Prepare Gearman jobs
    for ($i = 0; $i < $numMessages; $i++) {
        $workload = new stdClass();
        $workload->key = $keyPrefix . mt_rand(1, $keyDistribution);
        $workload->data = mt_rand(10000,10010);
        $workload->type = 'count';

        // Create asynchronous background job.
        $gearmanClient->doBackground($gearmanQueueName, json_encode($workload));
        if ($sleepMicroSeconds) {
            usleep($sleepMicroSeconds);
        }
    }
    if ($verbose) {
        \Awdn\VigilantQueue\Utility\ConsoleLog::log("Finished creating {$numMessages} gearman jobs.\n");
    }
} else {
    // Setup the producer, which connects to the gearman queue and fetches the jobs, prepares them for
    // the deferred priority queue

    $producer = new \Awdn\VigilantQueue\Producer\GearmanProducer($zmq);
    $producer->addServer($gIp, (int)$gPort);
    $producer->listenOn($gearmanQueueName, function ($workload) use ($expMinMs, $expMaxMs) {
        $data = json_decode($workload);
        return new \Awdn\VigilantQueue\Producer\RequestMessage(
            $data->key,
            $data->data,
            mt_rand($expMinMs, $expMaxMs),
            $data->type
        );
    });
    $producer->produce();
}