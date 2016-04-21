<?php

namespace Awdn\VigilantQueue;

use React;
use Awdn\VigilantQueue\Queue\PriorityHashQueue;
use Awdn\VigilantQueue\Queue\Message;
use Awdn\VigilantQueue\Queue\MessageArrayAggregator;

/**
 * Class DeferredQueueServer
 * @package Awdn\VigilantQueue
 */
class DeferredQueueServer
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
     * @var React\EventLoop\ExtEventLoop|React\EventLoop\LibEventLoop|React\EventLoop\LibEvLoop|React\EventLoop\StreamSelectLoop
     */
    private $reactLoop;

    /**
     * @var \ZMQContext
     */
    private $zmqContext;

    /**
     * Outbound queue for evicted messages.
     * @var \ZMQSocket
     */
    private $zmqOutboundQueue;

    /**
     * Inbound queue for received messages.
     * @var \ZMQSocket
     */
    private $zmqInboundQueue;

    /**
     * @var PriorityHashQueue
     */
    private $queue;

    /**
     * @var \ArrayObject
     */
    public $messages;

    /**
     * Check as well: \React\EventLoop\Timer\Timer::MIN_INTERVAL
     */
    const EVICTION_LOOP_INTERVAL = 0.00001;
    const STATUS_LOOP_INTERVAL = 1.0;
    const MEMORY_WARN_LIMIT_MB = 0.5;
    const MEMORY_PEAK_WARN_LIMIT_MB = 30;


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
     * Send debug messages to the console.
     * @var bool
     */
    private $debug;

    /**
     * @param string $zmqIn
     * @param string $zmqOut
     * @param int $evictionTicksPerSec
     * @param boolean $debug
     * @return DeferredQueueServer
     */
    public static function factory($zmqIn, $zmqOut, $evictionTicksPerSec, $debug)
    {
        $daemon = new self($zmqIn, $zmqOut, $evictionTicksPerSec, $debug);
        $daemon->setDebug($debug);
        $daemon->init();
        return $daemon;
    }

    /**
     * @param string $zmqIn
     * @param string $zmqOut
     * @param int $evictionTicksPerSec
     * @return DeferredQueueServer
     */
    private function __construct($zmqIn, $zmqOut, $evictionTicksPerSec)
    {
        $this->setZmqIn($zmqIn);
        $this->setZmqOut($zmqOut);
        $this->setEvictionTicksPerSec($evictionTicksPerSec);
        $this->setDebug(false);
    }

    /**
     * Executes the event loop.
     */
    public function run()
    {
        $this->reactLoop->run();
    }


    protected function init()
    {
        if ($this->isDebug()) {
            echo "Running ".get_class()." on " .str_replace("\n", "", `hostname; echo ' - ';uname -a;`) . "\n";
            echo "The eviction tick rate is set to {$this->getEvictionTicksPerSec()}/second.\n";
        }

        // Create the event loop
        $this->reactLoop = React\EventLoop\Factory::create();

        // Object pool
        $this->queue = new PriorityHashQueue();

        // Setup ZMQ to send evicted objects.
        $this->zmqContext = new React\ZMQ\Context($this->reactLoop);


        if ($this->isDebug()) {
            echo "Binding inbound ZMQ to '{$this->getZmqIn()}'.\n";
        }
        // Receiver queue for incoming objects
        $this->zmqInboundQueue = $this->zmqContext->getSocket(\ZMQ::SOCKET_SUB);
        $this->zmqInboundQueue->connect($this->getZmqIn());
        $this->zmqInboundQueue->subscribe('obj');


        if ($this->isDebug()) {
            echo "Binding outbound ZMQ to '{$this->getZmqOut()}'.\n";
        }
        // Outgoing queue for evicted objects
        $this->zmqOutboundQueue = $this->zmqContext->getSocket(\ZMQ::SOCKET_PUSH);
        $this->zmqOutboundQueue->bind($this->getZmqOut());

        // Register events
        $this->registerInboundEvents();
        $this->registerEvictionEvents();
        $this->registerTimedEvents();
    }

    /**
     * Registers inbound data events.
     */
    private function registerInboundEvents()
    {
        // Handle requests from queue via SUBSCRIBER
        $this->zmqInboundQueue->on('message', function ($msg) {
            $this->incrementAddedObjectCount();
            $msg = substr($msg, 4); // remove 'obj ' prefix

            try {
                $message = Message::fromStringToArray($msg);
                $this->getQueue()->push($message['key'], $message['data'], round(microtime(true) * 1000000) + $message['timeout']);

                if ($this->isDebug()) {
                    echo "[OnMessage] Data for key '{$message['key']}' [type '{$message['type']}', exp {$message['timeout']} ms]: ".str_replace("\n", "", var_export($message['data'], true))."\n";
                }
            } catch (\Exception $e) {
                if ($this->isDebug()) {
                    echo "[WARN] " . $e->getMessage() . "\n";
                }
            }
        });
    }

    /**
     * Registers the eviction of items from the queue.
     */
    private function registerEvictionEvents() {
        $this->reactLoop->addPeriodicTimer(1 / $this->getEvictionTicksPerSec() , function () {
            if (($item = $this->getQueue()->evict(round(microtime(true) * 1000000))) !== null) {
                if ($this->isDebug()) {
                    echo "[Eviction] Timeout detected for '{$item['key']}' at " . round($item['priority'] / 1000000, 3). "\n";
                }

                $this->getZmqOutboundQueue()->send((string)$item['data']);
                $this->incrementEvictedObjectCount();
            }
        });
    }

    /**
     * Registers a periodically triggered status event.
     */
    private function registerTimedEvents()
    {
        $this->reactLoop->addPeriodicTimer(self::STATUS_LOOP_INTERVAL, function () {
            $d = date("Y-m-d H:i:s");
            if (memory_get_usage(true) / 1024 / 1024 > self::MEMORY_WARN_LIMIT_MB) {
                echo $d . " - [WARN] MemoryUsage:    " . (memory_get_usage(true) / 1024 / 1024) . " MB.\n";
            }
            if (memory_get_peak_usage(true) / 1024 / 1024 > self::MEMORY_PEAK_WARN_LIMIT_MB) {
                echo $d . " - [WARN] MemoryPeakUsage " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB.\n";
            }

            $rateObjects = ($this->getAddedObjectCount() - $this->getLastObjectCount()) / self::STATUS_LOOP_INTERVAL;
            $rateEvictions = ($this->getEvictedObjectCount() - $this->getLastEvictionCount()) / self::STATUS_LOOP_INTERVAL;

            echo $d . " - [STATS] Added objects: {$this->getAddedObjectCount()}, evictions: {$this->getEvictedObjectCount()} ({$rateObjects} Obj/Sec, {$rateEvictions} Evi/Sec).\n";

            $this->setLastEvictionCount($this->getEvictedObjectCount());
            $this->setLastObjectCount($this->getAddedObjectCount());
        });
    }


    /**
     * @return PriorityHashQueue
     */
    private function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return \ZMQSocket
     */
    private function getZmqOutboundQueue()
    {
        return $this->zmqOutboundQueue;
    }

    /**
     * @return \ZMQSocket
     */
    private function getZmqInboundQueue()
    {
        return $this->zmqInboundQueue;
    }

    /**
     * @return int
     */
    private function getEvictedObjectCount()
    {
        return $this->evictedObjectCount;
    }

    /**
     * @param int $evictedObjectCount Optional. Default 1.
     */
    private function incrementEvictedObjectCount($evictedObjectCount = 1)
    {
        $this->evictedObjectCount += $evictedObjectCount;
    }

    /**
     * @return int
     */
    private function getAddedObjectCount()
    {
        return $this->addedObjectCount;
    }

    /**
     * @param int $addedObjectCount
     */
    private function incrementAddedObjectCount($addedObjectCount = 1)
    {
        $this->addedObjectCount += $addedObjectCount;
    }

    /**
     * @return boolean
     */
    private function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     */
    private function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return int
     */
    private function getZmqIn()
    {
        return $this->zmqIn;
    }

    /**
     * @param int $zmqIn
     */
    private function setZmqIn($zmqIn)
    {
        $this->zmqIn = $zmqIn;
    }

    /**
     * @return string
     */
    private function getZmqOut()
    {
        return $this->zmqOut;
    }

    /**
     * @param string $zmqOut
     */
    private function setZmqOut($zmqOut)
    {
        $this->zmqOut = $zmqOut;
    }

    /**
     * @return int
     */
    private function getEvictionTicksPerSec()
    {
        return $this->evictionTicksPerSec;
    }

    /**
     * @param int $evictionTicksPerSec
     */
    private function setEvictionTicksPerSec($evictionTicksPerSec)
    {
        $this->evictionTicksPerSec = $evictionTicksPerSec;
    }

    /**
     * @return int
     */
    private function getLastObjectCount()
    {
        return $this->lastObjectCount;
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
    private function getLastEvictionCount()
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


}