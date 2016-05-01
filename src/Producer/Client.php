<?php


namespace Awdn\VigilantQueue\Producer;


use Awdn\VigilantQueue\Server\RequestMessageInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Client
 * @package Awdn\VigilantQueue\Producer
 */
class Client implements ClientInterface
{

    /**
     * @var string
     */
    private $zmq;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * @var \ZMQContext
     */
    private $context;

    /**
     * @var \ZMQSocket
     */
    private $socket;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Client constructor.
     * @param string $zmq
     * @param LoggerInterface $logger
     */
    public function __construct($zmq, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->setZmq($zmq);
    }

    /**
     * @return void
     */
    public function connect()
    {
        $this->context = new \ZMQContext(1);
        $this->socket = new \ZMQSocket($this->context, \ZMQ::SOCKET_PUSH);

        $this->logger->info("Connecting to server inbound queue on ZMQ '{$this->getZmq()}'.");
        $this->socket->connect($this->getZmq());
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function send($message)
    {
        if ($this->isVerbose()) {
            $this->logger->debug("Sending message: " . $message);
        }
        $this->socket->send($message);
    }

    /**
     * @param RequestMessageInterface $message
     * @return void
     */
    public function message($message)
    {
        $this->send((string)$message);
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