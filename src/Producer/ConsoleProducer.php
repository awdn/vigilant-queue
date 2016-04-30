<?php

namespace Awdn\VigilantQueue\Producer;


use Awdn\VigilantQueue\Utility\ConsoleLog;

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
    private $debug = false;

    /**
     * @var bool
     */
    private $stdIn = false;

    /**
     * ConsoleProducer constructor.
     * @param string $zmqOut
     */
    private function __construct($zmqOut)
    {
        $this->setZmqOut($zmqOut);
    }

    /**
     * @param string $zmqOut
     * @param boolean $stdIn
     * @param boolean $debug
     * @return ConsoleProducer
     */
    public function factory($zmqOut, $stdIn, $debug) {
        $producer = new self($zmqOut);
        $producer->setDebug($debug);
        $producer->setStdIn($stdIn);
        return $producer;
    }

    /**
     * Produces
     */
    public function produce() {
        if ($this->isDebug()) ConsoleLog::log("Starting console producer.");

        $client = new Client($this->getZmqOut());
        $client->connect();
        $fp = false;
        if ($this->isStdIn()) {
            $fp = fopen('php://stdin', 'r');
            if (!is_resource($fp)) {
                ConsoleLog::log("Can not read from stdin.");
                exit(1);
            }
        }
        if ($this->isDebug()) ConsoleLog::log("Waiting for packets...");
        $packet = $prevPacket = false;
        do {
            if ($this->isStdIn()) {
                $packet = fgets($fp, 1024);
            } else {
                $prevPacket = $packet;
                $packet = readline();
            }

            if ($this->isDebug()) ConsoleLog::log("Received packet: {$packet}");
            // writeLog($logFile, "Packet Received: {$packet}", LOG_INFO);
            $client->send($packet);
            if ($this->isDebug()) {
                //echo __CLASS__ . ":: Sending to zmq with result: " . "\n";
            }

        } while (($this->isStdIn() && $packet !== false) || !(!$this->isStdIn() && trim($packet) == '' && trim($prevPacket) == '') );

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

        $client = new Client($this->getZmqOut());
        $client->connect();
        // Sleep until socket is ready
        // @todo Figure out a better way to check if the socket is ready.
        usleep(1000000);

        if ($this->isDebug()) ConsoleLog::log("Generating packets...");


        for ($i = 0; $i < $numMessages; $i++) {
            $key = $keyPrefix . '_' . mt_rand(1, $keyDistribution);
            $expire = mt_rand($expMinMs, $expMaxMs);
            $data = serialize(['a' => mt_rand(10,10), 'b' => mt_rand(10,10)]);
            $message = new RequestMessage($key, $data, $expire, 'aggregate');

            if ($this->isDebug()) {
                ConsoleLog::log((string)$message);
            }

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
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return boolean
     */
    public function isStdIn()
    {
        return $this->stdIn;
    }

    /**
     * @param boolean $stdIn
     */
    public function setStdIn($stdIn)
    {
        $this->stdIn = $stdIn;
    }


}