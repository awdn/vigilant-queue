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
        $client = new Client($this->getZmq());
        $client->connect();
        $i = 0;
        while (true) {
            $i++;
            $rawMessage = $client->receive();
            $msg = ResponseMessage::fromString($rawMessage);
            $rows = $msg->getData();
            if ($this->isDebug()) {
                ConsoleLog::log("Received raw message: " . $rawMessage);
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

            if ($this->isDebug()) {
                ConsoleLog::log("Reduced result for key '{$msg->getKey()}' and type '{$msg->getType()}':" . var_export($result, true));
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