<?php

namespace Awdn\VigilantQueue\Producer;

use Awdn\VigilantQueue\Server\RequestMessageInterface;
use Awdn\VigilantQueue\Utility\MetricsInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GearmanProducer
 *
 * This class is a worker to a given Gearman job queues and translates
 * the jobs into deferred queue messages.
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MetricsInterface
     */
    private $metrics;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * GearmanProducer constructor.
     * @param string $zmq
     * @param LoggerInterface $logger
     * @param MetricsInterface $metrics
     * @param bool $verbose
     */
    public function __construct($zmq, LoggerInterface $logger, MetricsInterface $metrics, $verbose)
    {
        $this->gearman = new \GearmanWorker();
        $this->client = new Client($zmq, $logger);
        $this->logger = $logger;
        $this->metrics = $metrics;
        $this->verbose = $verbose;
    }

    /**
     * @param string $ip
     * @param int $port
     */
    public function addServer($ip, $port) {
        $this->logger->info("Adding Gearman server {$ip}:{$port}.");
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
                if ($this->verbose) {
                    $this->logger->debug("Sending message to queue: " . (string)$message);
                }
                $this->metrics->increment('message');
                $this->client->message($message);
            } else {
                throw new \Exception("Invalid return type.");
            }
        });
    }

    /**
     * @param string $name
     * @param callable $callback
     */
    private function addCallback($name, callable $callback)
    {
        $this->gearman->addFunction($name, $callback);
    }

}