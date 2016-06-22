<?php

namespace Awdn\VigilantQueue\Server;

use Awdn\VigilantQueue\Utility\MetricsInterface;

/**
 * Class RuntimeStatistics
 * @package Awdn\VigilantQueue\Server
 */
class RuntimeStatistics
{

    /**
     * This will trigger the class to count total numbers for added and evicted objects instead of the numbers
     * since last tick() call.
     * @var bool
     */
    private $countAbsolute = false;

    /**
     * @var MetricsInterface
     */
    private $metrics;

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
     * @var float
     */
    private $memoryUsageMb = 0.0;

    /**
     * @var float
     */
    private $memoryPeakUsageMb = 0.0;


    /**
     * RuntimeStatistics constructor.
     */
    public function __construct(MetricsInterface $metrics)
    {
        $this->startTime = microtime(true);
        $this->setMetrics($metrics);
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
     * @return float
     */
    public function getMemoryUsageMb()
    {
        return $this->memoryUsageMb;
    }

    /**
     * @param float $memoryUsageMb
     */
    public function setMemoryUsageMb($memoryUsageMb)
    {
        $this->memoryUsageMb = $memoryUsageMb;
    }

    /**
     * @return float
     */
    public function getMemoryPeakUsageMb()
    {
        return $this->memoryPeakUsageMb;
    }

    /**
     * @param float $memoryPeakUsageMb
     */
    public function setMemoryPeakUsageMb($memoryPeakUsageMb)
    {
        $this->memoryPeakUsageMb = $memoryPeakUsageMb;
    }



    /**
     * Sets internal values for aggregations when the next cycle is calculated.
     */
    public function tick() {
        $this->metrics->set('objects', $this->getAddedObjectCount());
        $this->metrics->set('evictions', $this->getEvictedObjectCount());
        $this->metrics->set('memory', $this->getMemoryUsageMb());
        $this->metrics->set('memory_peak', $this->getMemoryPeakUsageMb());

        if (!$this->countAbsolute) {
            $this->evictedObjectCount = 0;
            $this->addedObjectCount = 0;
        } else {
            $this->setLastEvictionCount($this->getEvictedObjectCount());
            $this->setLastObjectCount($this->getAddedObjectCount());
        }
    }

    /**
     * @return MetricsInterface
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @param MetricsInterface $metrics
     */
    public function setMetrics($metrics)
    {
        $this->metrics = $metrics;
    }


}