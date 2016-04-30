<?php

namespace Awdn\VigilantQueue\Producer;

/**
 * Class GearmanProducer
 *
 * This class is a worker to given Gearman job queues and translates
 * the messages into deferred queue items
 *
 * @package Awdn\VigilantQueue\Producer
 */
class GearmanProducer
{
    /**
     * @var \GearmanWorker
     */
    private $gearman;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var bool
     */
    private $active = true;

    public function __construct($zmq)
    {
        $this->gearman = new \GearmanWorker();
        $this->client = new Client($zmq);
    }

    public function addServer($ip, $port) {
        $this->gearman->addServer($ip, $port);
    }

    /**
     * Connects to the ZMQ inbound queue from the deferred queue server.
     * Starts the gearman worker and processes the workload by calling
     * the registered callback functions.
     */
    public function produce()
    {
        $this->client->connect();

        while ($this->active) {
            $this->gearman->work();
        }
    }

    /**
     * Registers the gearman queues where the worker has to listen on.
     * The given callback has to transform the workload into a RequestMessage object.
     *
     * @param string $callbackName
     * @param callable $workloadToMessageCallback The return value of
     */
    public function listenOn($callbackName, callable $workloadToMessageCallback) {
        $this->addCallback($callbackName, function($job) use ($workloadToMessageCallback) {
            $message = call_user_func($workloadToMessageCallback, $job->workload());
            if ($message instanceof RequestMessageInterface) {
                $this->client->send((string)$message);
            } else {
                throw new \Exception("Invalid return type.");
            }
        });
    }

    private function addCallback($name, $callback)
    {
        $this->gearman->addFunction($name, $callback);
    }

}