<?php

namespace Awdn\VigilantQueue\Queue;

/**
 * Class QueueItem
 * @package Awdn\VigilantQueue\Queue
 */
class QueueItem
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $type;

    /**
     * QueueItem constructor.
     * @param string $key
     * @param string $data
     * @param int $priority
     * @param string $type
     */
    public function __construct($key, $data, $priority, $type)
    {
        $this->key = $key;
        $this->data = $data;
        $this->priority = $priority;
        $this->type = $type;
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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }



}