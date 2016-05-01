<?php

namespace Awdn\VigilantQueue\Server;

/**
 * Class RuntimeStatistics
 * @package Awdn\VigilantQueue\Server
 */
class RuntimeStatistics
{
    /**
     * @var float
     */
    private $startTime = 0;

    /**
     * @var int
     */
    private $addedObjectCount = 0;

    /**
     * @var int
     */
    private $evictedObjectCount = 0;

    /**
     * @var int
     */
    private $lastObjectCount = 0;

    /**
     * @var int
     */
    private $lastEvictionCount = 0;


    /**
     * RuntimeStatistics constructor.
     */
    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->startTime = 0;
        $this->addedObjectCount = 0;
        $this->evictedObjectCount = 0;
        $this->lastObjectCount = 0;
        $this->lastEvictionCount = 0;
    }

    /**
     * @return int
     */
    public function getEvictedObjectCount()
    {
        return $this->evictedObjectCount;
    }

    /**
     * @param int $evictedObjectCount Optional. Default 1.
     */
    public function incrementEvictedObjectCount($evictedObjectCount = 1)
    {
        $this->evictedObjectCount += $evictedObjectCount;
    }

    /**
     * @return int
     */
    public function getAddedObjectCount()
    {
        return $this->addedObjectCount;
    }

    /**
     * @param int $addedObjectCount
     */
    public function incrementAddedObjectCount($addedObjectCount = 1)
    {
        $this->addedObjectCount += $addedObjectCount;
    }

    /**
     * @param int $lastObjectCount
     */
    private function setLastObjectCount($lastObjectCount)
    {
        $this->lastObjectCount = $lastObjectCount;
    }

    /**
     * @return int
     */
    private function getLastObjectCount()
    {
        return $this->lastObjectCount;
    }

    /**
     * @return int
     */
    public function getLastEvictionCount()
    {
        return $this->lastEvictionCount;
    }

    /**
     * @param int $lastEvictionCount
     */
    private function setLastEvictionCount($lastEvictionCount)
    {
        $this->lastEvictionCount = $lastEvictionCount;
    }

    /**
     * @param float $interval
     * @return float
     */
    public function getAddedObjectRate($interval)
    {
        return ($this->getAddedObjectCount() - $this->getLastObjectCount()) / $interval;
    }

    /**
     * @param float $interval
     * @return float
     */
    public function getEvictionRate($interval) {
        return ($this->getEvictedObjectCount() - $this->getLastEvictionCount()) / $interval;
    }

    /**
     * Sets internal values for aggregations when the next cycle is calculated.
     */
    public function tick() {
        $this->setLastEvictionCount($this->getEvictedObjectCount());
        $this->setLastObjectCount($this->getAddedObjectCount());
    }
}