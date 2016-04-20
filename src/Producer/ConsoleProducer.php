<?php

namespace Awdn\VigilantQueue\Producer;


use Awdn\VigilantQueue\Queue\Message;

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
     * Receives objects to push through a given zmq channel.
     *
     */
    public function produce() {
        if ($this->isDebug()) echo __CLASS__ . ":: Starting console producer.\n";

        $context = new \ZMQContext(1);
        $pub = new \ZMQSocket($context, \ZMQ::SOCKET_PUB);
        //$pub->bind('tcp://127.0.0.1:6444');
        //$ipcPath = 'ipc://'.$this->getZmqOut();
        if ($this->isDebug()) {
            echo __CLASS__ . " Using {$this->getZmqOut()} for inter process communication.\n";
        }
        $pub->bind($this->getZmqOut());

        $fp = false;
        if ($this->isStdIn()) {
            $fp = fopen('php://stdin', 'r');
            if (!is_resource($fp)) {
                echo "Can not read from stdin.\n";
                exit(1);
            }
        }
        if ($this->isDebug()) echo __CLASS__ . ":: Waiting for packets...\n";
        $packet = $prevPacket = false;
        do {
            if ($this->isStdIn()) {
                $packet = fgets($fp, 1024);
            } else {
                $prevPacket = $packet;
                $packet = readline();
            }

            if ($this->isDebug()) echo __CLASS__ . ":: Received packet: {$packet}\n";
            // writeLog($logFile, "Packet Received: {$packet}", LOG_INFO);
            $pub->send('obj ' . $packet, \ZMQ::MODE_DONTWAIT);
            if ($this->isDebug()) {
                //echo __CLASS__ . ":: Sending to zmq with result: " . "\n";
            }

        } while (($this->isStdIn() && $packet !== false) || !(!$this->isStdIn() && trim($packet) == '' && trim($prevPacket) == '') );

    }

    public function simulate($keyPrefix, $keyDistribution, $numMessages, $expMinMs, $expMaxMs, $sleepMicroSeconds) {
        if ($this->isDebug()) {
            echo __CLASS__ . ":: Starting console producer simulation with the following parameters:\n";
            $args = func_get_args();
            var_dump($args);
        }


        $context = new \ZMQContext(1);
        $pub = new \ZMQSocket($context, \ZMQ::SOCKET_PUB);

        if ($this->isDebug()) {
            echo __CLASS__ . " Using {$this->getZmqOut()} for inter process communication.\n";
        }
        $pub->bind($this->getZmqOut());

        // Sleep until socket is ready
        usleep(1000000);

        if ($this->isDebug()) echo __CLASS__ . ":: Generating packets...\n";


        for ($i = 0; $i < $numMessages; $i++) {
            $key = $keyPrefix . '_' . mt_rand(1, $keyDistribution);
            $expire = mt_rand($expMinMs, $expMaxMs);

            $message = new Message($key, sha1($key . $expire . microtime(true)), $expire);

            if ($this->isDebug()) {
                echo __CLASS__ . ":: Sending {$message}\n";
            }

            $pub->send('obj ' . trim((string)$message));

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