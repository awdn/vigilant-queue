<?php

namespace Awdn\VigilantQueue\Consumer;

use Awdn\VigilantQueue\Server\ResponseMessage;
use Psr\Log\LoggerInterface;

/**
 * Class ConsoleConsumer
 * @package Awdn\VigilantQueue\Consumer
 */
class ConsoleConsumer implements ConsumerInterface
{
    /**
     * @var string
     */
    private $zmq;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * ConsoleConsumer constructor.
     * @param string $zmq
     */
    private function __construct($zmq, LoggerInterface $logger)
    {
        $this->setZmq($zmq);
        $this->logger = $logger;
    }

    /**
     * @param string $zmq
     * @param LoggerInterface $logger
     * @param bool $verbose
     * @return ConsoleConsumer
     */
    public function factory($zmq, LoggerInterface $logger, $verbose)
    {
        $consumer = new self($zmq, $logger);
        $consumer->setVerbose($verbose);
        return $consumer;
    }

    /**
     * Consumes evicted objects from the outbound queue.
     */
    public function consume()
    {
        $this->logger->info("Connecting to server outbound queue on '{$this->getZmq()}'.");

        $client = new Client($this->getZmq());
        $client->connect();
        $i = 0;
        while (true) {
            $i++;
            $rawMessage = $client->receive();
            try {
                $msg = ResponseMessage::fromString($rawMessage);
                $rows = $msg->getData();

                if ($this->isVerbose()) {
                    $this->logger->debug("Received raw message: " . $rawMessage);
                }


                switch ($msg->getType()) {
                    case 'aggregate':
                        $result = $this->reducerAggregate($rows);
                        break;
                    case 'count':
                        $result = $this->reducerCount($rows);
                        break;
                    default:
                        $result = $rows;
                }

                if ($this->isVerbose()) {
                    $this->logger->debug("Reduced result for key '{$msg->getKey()}' and type '{$msg->getType()}':" . var_export($result,
                            true));
                }
            } catch (\Exception $e) {
                $this->logger->error((string)$e);
            }

            if ($i % 1000 == 0) {
                $this->logger->info("Fetched {$i} messages.");
            }
        }
    }

    /**
     * This reducer expects serialized array data per row. If the value is of type numeric the sum for the values
     * of the same key of all rows will be created.
     * @param array $rows
     * @return array
     */
    private function reducerAggregate($rows) {
        $reduced = [];
        foreach ($rows as $rowSerialized) {
            $row = unserialize($rowSerialized);
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    if (!isset($reduced[$key])) {
                        $reduced[$key] = 0;
                    }
                    $reduced[$key] += $value;
                } else {
                    if (!isset($reduced[$key])) {
                        $reduced[$key] = [];
                    }
                    $reduced[$key][] = $value;
                }
            }
        }

        return $reduced;
    }

    /**
     * This reducer counts how often a given value is within all rows.
     * @param array $rows
     * @return array
     */
    private function reducerCount($rows) {
        $reduced = [];
        foreach ($rows as $key => $value) {
            if (!isset($reduced[$value])) {
                $reduced[$value] = 0;
            }
            $reduced[$value]++;
        }

        return $reduced;
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