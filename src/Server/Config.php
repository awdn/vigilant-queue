<?php
namespace Awdn\VigilantQueue\Server;

use Awdn\VigilantQueue\Utility\ConsoleLog;

/**
 * Class Config
 * @package Awdn\VigilantQueue\Server
 */
class Config
{
    /**
     * Ticks on the event loop per second.
     * @var int
     */
    private $evictionTicksPerSec = 1000;

    /**
     * ZMQ address for communication with the consumers.
     * @var string
     */
    private $zmqIn;

    /**
     * @var string
     */
    private $zmqOut;

    /**
     * Minimum log level. Needed to avoid spamming all incoming data into the logger, when minimumLogLevel is greater
     * debug.
     * @var int
     */
    private $minimumLogLevel;

    /**
     * Check as well: \React\EventLoop\Timer\Timer::MIN_INTERVAL
     * @var float
     */
    private $evictionLoopInterval = 0.00001;

    /**
     * @var float
     */
    private $statusLoopInterval = 1.0;

    /**
     * @var float
     */
    private $memoryLimitMbInfo = 0.5;

    /**
     * @var float
     */
    private $memoryLimitMbWarn = 10;

    /**
     * @var float
     */
    private $memoryPeakLimitMbWarn = 50;


    /**
     * @return int
     */
    public function getEvictionTicksPerSec()
    {
        return $this->evictionTicksPerSec;
    }

    /**
     * @param int $evictionTicksPerSec
     * @return Config
     */
    public function setEvictionTicksPerSec($evictionTicksPerSec)
    {
        $this->evictionTicksPerSec = $evictionTicksPerSec;
        return $this;
    }

    /**
     * @return string
     */
    public function getZmqIn()
    {
        return $this->zmqIn;
    }

    /**
     * @param string $zmqIn
     * @return Config
     */
    public function setZmqIn($zmqIn)
    {
        $this->zmqIn = $zmqIn;
        return $this;
    }

    /**
     * @return string
     */
    public function getZmqOut()
    {
        return $this->zmqOut;
    }

    /**
     * @param string $zmqOut
     * @return Config
     */
    public function setZmqOut($zmqOut)
    {
        $this->zmqOut = $zmqOut;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinimumLogLevel()
    {
        return $this->minimumLogLevel;
    }

    /**
     * @return mixed
     */
    public function getEvictionLoopInterval()
    {
        return $this->evictionLoopInterval;
    }

    /**
     * @param mixed $evictionLoopInterval
     * @return Config
     */
    public function setEvictionLoopInterval($evictionLoopInterval)
    {
        $this->evictionLoopInterval = $evictionLoopInterval;
        return $this;
    }

    /**
     * @return float
     */
    public function getStatusLoopInterval()
    {
        return $this->statusLoopInterval;
    }

    /**
     * @param float $statusLoopInterval
     * @return Config
     */
    public function setStatusLoopInterval($statusLoopInterval)
    {
        $this->statusLoopInterval = $statusLoopInterval;
        return $this;
    }

    /**
     * @return float
     */
    public function getMemoryLimitMbInfo()
    {
        return $this->memoryLimitMbInfo;
    }

    /**
     * @param float $memoryLimitMbInfo
     * @return Config
     */
    public function setMemoryLimitMbInfo($memoryLimitMbInfo)
    {
        $this->memoryLimitMbInfo = $memoryLimitMbInfo;
        return $this;
    }

    /**
     * @return float
     */
    public function getMemoryLimitMbWarn()
    {
        return $this->memoryLimitMbWarn;
    }

    /**
     * @param float $memoryLimitMbWarn
     * @return Config
     */
    public function setMemoryLimitMbWarn($memoryLimitMbWarn)
    {
        $this->memoryLimitMbWarn = $memoryLimitMbWarn;
        return $this;
    }

    /**
     * @return float
     */
    public function getMemoryPeakLimitMbWarn()
    {
        return $this->memoryPeakLimitMbWarn;
    }

    /**
     * @param float $memoryPeakLimitMbWarn
     * @return Config
     */
    public function setMemoryPeakLimitMbWarn($memoryPeakLimitMbWarn)
    {
        $this->memoryPeakLimitMbWarn = $memoryPeakLimitMbWarn;
        return $this;
    }


    /**
     * @param int|string $minimumLogLevel
     * @return Config
     * @throws \Exception
     */
    public function setMinimumLogLevel($minimumLogLevel)
    {
        if (is_int($minimumLogLevel)) {
            $this->minimumLogLevel = $minimumLogLevel;
        } else {
            $this->minimumLogLevel = ConsoleLog::parseLogLevel($minimumLogLevel);
        }
        return $this;
    }


}