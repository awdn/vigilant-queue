<?php


namespace Awdn\VigilantQueue\Producer;


class Client implements ClientInterface
{

    /**
     * @var string
     */
    private $zmq;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var \ZMQContext
     */
    private $context;

    /**
     * @var \ZMQSocket
     */
    private $socket;

    /**
     * Client constructor.
     * @param string $zmq
     */
    public function __construct($zmq)
    {
        $this->setZmq($zmq);
    }

    public function connect()
    {
        $this->context = new \ZMQContext(1);
        $this->socket = new \ZMQSocket($this->context, \ZMQ::SOCKET_PUSH);

        if ($this->isDebug()) {
            ConsoleLog::log("Using {$this->getZmq()} for inter process communication.");
        }
        $this->socket->connect($this->getZmq());
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function send($message)
    {
        $this->socket->send($message);
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


}