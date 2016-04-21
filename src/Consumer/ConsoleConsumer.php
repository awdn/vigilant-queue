<?php

namespace Awdn\VigilantQueue\Consumer;

use Awdn\VigilantQueue\Utility\ConsoleLog;

/**
 * Class ConsoleConsumer
 * @package Awdn\VigilantQueue\Consumer
 */
class ConsoleConsumer
{

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $zmq;

    /**
     * ConsoleConsumer constructor.
     * @param $zmq
     */
    private function __construct($zmq)
    {
        $this->setZmq($zmq);
    }

    /**
     * @param string $zmq
     * @param bool $debug
     * @return ConsoleConsumer
     */
    public function factory($zmq, $debug)
    {
        $consumer = new self($zmq);
        $consumer->setDebug($debug);
        return $consumer;
    }

    /**
     * Consumes evicted objects from the outbound queue.
     */
    public function consume()
    {
        $context = new \ZMQContext();
        $rep = $context->getSocket(\ZMQ::SOCKET_PULL);
        if ($this->isDebug()) {
            ConsoleLog::log("Connect to zmq at '{$this->getZmq()}' (incoming evicted jobs from queue).");
        }
        $rep->connect($this->getZmq());

        $i = 0;
        while (true) {
            $i++;
            $msg = $rep->recv();
            if ($this->isDebug()) {
                ConsoleLog::log("Received message: " . $msg);
            }
        }
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
     * @return string
     */
    public function getZmq()
    {
        return $this->zmq;
    }

    /**
     * @param string $zmq
     */
    public function setZmq($zmq)
    {
        $this->zmq = $zmq;
    }



}