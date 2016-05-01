<?php

namespace Awdn\VigilantQueue\Server;

/**
 * Class RequestMessage
 * @package Awdn\VigilantQueue\Server
 */
class RequestMessage implements RequestMessageInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $timeoutMicroSeconds;

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $type;

    /**
     * Message constructor.
     * @param string $key
     * @param string $data
     * @param int $timeoutMicroSeconds
     * @param string|null $type
     */
    public function __construct($key, $data, $timeoutMicroSeconds, $type = null)
    {
        $this->setKey($key);
        $this->setData($data);
        $this->setTimeoutMicroSeconds($timeoutMicroSeconds);
        if ($type === null) {
            $this->setType(gettype($data));
        } else {
            $this->setType($type);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->getKey()}:{$this->getTimeoutMicroSeconds()}:{$this->getType()}|{$this->getData()}";
    }

    /**
     * @param string $msg
     * @return RequestMessage
     * @throws \Exception
     */
    public static function fromString($msg)
    {
        $posDataDelimiter = strpos($msg, '|');

        if ($posDataDelimiter === false) {
            throw new \Exception("Can not parse message: ". $msg);
        }

        list($key, $timeoutMicroSeconds, $type) = explode(':', substr($msg, 0, $posDataDelimiter));
        $data = substr($msg, $posDataDelimiter + 1);

        return new self($key, $data, $timeoutMicroSeconds, $type);
    }

    /**
     * @param $msg
     * @return array
     * @throws \Exception
     */
    public static function fromStringToArray($msg)
    {
        $posDataDelimiter = strpos($msg, '|');

        if ($posDataDelimiter === false) {
            throw new \Exception("Can not parse message: ". $msg);
        }

        list($key, $timeoutMicroSeconds, $type) = explode(':', substr($msg, 0, $posDataDelimiter));
        $data = substr($msg, $posDataDelimiter + 1);

        return ['key' => $key, 'data' => $data, 'timeout' => $timeoutMicroSeconds, 'type' => $type];
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getTimeoutMicroSeconds()
    {
        return $this->timeoutMicroSeconds;
    }

    /**
     * @param mixed $timeoutMicroSeconds
     */
    public function setTimeoutMicroSeconds($timeoutMicroSeconds)
    {
        $this->timeoutMicroSeconds = $timeoutMicroSeconds;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }



}
