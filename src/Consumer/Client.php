<?php

namespace Awdn\VigilantQueue\Consumer;


class Client implements ClientInterface
{
    /**
     * @var \ZMQContext
     */
    private $context;

    /**
     * @var \ZMQSocket
     */
    private $socket;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $zmq;

    public function __construct($zmq)
    {
        $this->setZmq($zmq);
    }

    /**
     * Connects to the given endpoint
     */
    public function connect()
    {
        $this->context = new \ZMQContext();
        $this->socket = $this->context->getSocket(\ZMQ::SOCKET_PULL);
        if ($this->isDebug()) {
            ConsoleLog::log("Connecting to zmq at '{$this->getZmq()}'");
        }
        $this->socket->connect($this->getZmq());
    }

    /**
     * This is a blocking operation by default which tries to receive
     * from data from the underlying socket to zmq.
     * @see \ZMQ::MODE_* constants for non-blocking behaviour. Needs to be implemented.
     * @return string
     */
    public function receive()
    {
        return $this->socket->recv();
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