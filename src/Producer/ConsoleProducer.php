<?php

namespace Awdn\VigilantQueue\Producer;


use Awdn\VigilantQueue\Server\RequestMessage;
use Psr\Log\LoggerInterface;

/**
 * Class ConsoleProducer
 * @package Awdn\VigilantQueue\Producer
 */
class ConsoleProducer
{
    /**
     * @var string
     */
    private $zmqOut;

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConsoleProducer constructor.
     * @param string $zmqOut
     * @param LoggerInterface $logger
     */
    private function __construct($zmqOut, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setZmqOut($zmqOut);
    }

    /**
     * @param string $zmqOut
     * @param LoggerInterface $logger
     * @param boolean $verbose
     * @return ConsoleProducer
     */
    public static function factory($zmqOut, $logger, $verbose) {
        $producer = new self($zmqOut, $logger);
        $producer->setVerbose($verbose);
        return $producer;
    }

    /**
     * Produces messages from stdIn.
     */
    public function produce() {
        $this->logger->info("Starting console producer.");

        $client = new Client($this->getZmqOut(), $this->logger);
        $client->connect();

        $this->logger->info("Waiting for packets...");

        $packet = $prevPacket = false;
        do {
            $prevPacket = $packet;
            $packet = readline();

            if ($this->verbose) {
                $this->logger->debug("Received packet: {$packet}");
            }

            $client->send($packet);


        } while (!(trim($packet) == '' && trim($prevPacket) == ''));

    }

    /**
     * Simulates messages on the producer side.
     *
     * @param string $keyPrefix Prefix for key generation.
     * @param int $keyDistribution Number of different keys to generate.
     * @param int $numMessages Number of messages to send
     * @param int $expMinMs Start value for random expiration in microseconds.
     * @param int $expMaxMs End value for random expiration in microseconds.
     * @param int $sleepMicroSeconds Sleep duration between each send call in microseconds.
     */
    public function simulate($keyPrefix, $keyDistribution, $numMessages, $expMinMs, $expMaxMs, $sleepMicroSeconds) {
        $this->logger->info("Starting console producer simulation. Generating {$numMessages} messages with a key " .
                            "distribution of {$keyDistribution}. The key prefix is {$keyPrefix} and the expiration ".
                            "timeout is between {$expMinMs} and {$expMaxMs} microseconds.");

        $client = new Client($this->getZmqOut(), $this->logger);
        $client->setVerbose($this->isVerbose());
        $client->connect();
        // Sleep until socket is ready
        // @todo Figure out a better way to check if the socket is ready.
        usleep(1000000);

        $this->logger->info("Starting to generate packets...");


        for ($i = 0; $i < $numMessages; $i++) {
            $key = $keyPrefix . '_' . mt_rand(1, $keyDistribution);
            $expire = mt_rand($expMinMs, $expMaxMs);
            $data = serialize(['a' => mt_rand(1,10), 'b' => mt_rand(1,10)]);
            $message = new RequestMessage($key, $data, $expire, 'aggregate');

            $client->send((string)$message);

            if ($sleepMicroSeconds) {
                usleep($sleepMicroSeconds);
            }
        }

    }

    /**
     * @return mixed
     */
    public function getZmqOut()
    {
        return $this->zmqOut;
    }

    /**
     * @param mixed $zmqOut
     */
    public function setZmqOut($zmqOut)
    {
        $this->zmqOut = $zmqOut;
    }

    /**
     * @return boolean
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

}