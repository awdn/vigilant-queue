<?php

namespace Awdn\VigilantQueue\Server;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use React;
use Awdn\VigilantQueue\Queue\PriorityHashQueue;


/**
 * Class DeferredQueue
 *
 * This is the main server class.
 *
 * @package Awdn\VigilantQueue\Server
 */
class DeferredQueue
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @var RuntimeStatistics
     */
    private $runtimeStatistics;


    /**
     * @param Config $config
     * @param LoggerInterface $logger
     * @return DeferredQueue
     */
    public static function factory(Config $config, LoggerInterface $logger)
    {
        $server = new self($config, $logger);
        $server->init();
        return $server;
    }

    /**
     * @param Config $config
     * @param LoggerInterface $logger
     * @return DeferredQueue
     */
    private function __construct(Config $config, LoggerInterface $logger)
    {
        $this->setLogger($logger);
        $this->setConfig($config);
        $this->runtimeStatistics = new RuntimeStatistics();
    }

    /**
     * Executes the event loop.
     */
    public function run()
    {
        $this->reactLoop->run();
    }


    /**
     * @return void
     */
    protected function init()
    {
        $this->logger->info("Running ".self::class." on " .str_replace("\n", "", `hostname; echo ' - ';uname -a;`));
        $this->logger->info("The eviction tick rate is set to {$this->config->getEvictionTicksPerSec()}/second.");

        // Create the event loop
        $this->reactLoop = React\EventLoop\Factory::create();

        // Object pool
        $this->queue = new PriorityHashQueue();
        // In default mode the latest data will be replaced for a given key. In DATA_MODE_APPEND the data will be appended
        // internally and available within the consumer as array (for instance for reducing purposes)
        //$this->queue->setDataMode(PriorityHashQueue::DATA_MODE_APPEND);

        // Setup ZMQ to send evicted objects.
        $this->zmqContext = new React\ZMQ\Context($this->reactLoop);

        $this->logger->info("Binding inbound ZMQ to '{$this->config->getZmqIn()}'.");

        // Receiver queue for incoming objects
        $this->zmqInboundQueue = $this->zmqContext->getSocket(\ZMQ::SOCKET_PULL);
        $this->zmqInboundQueue->bind($this->config->getZmqIn());

        $this->logger->info("Binding outbound ZMQ to '{$this->config->getZmqOut()}'.");

        // Outgoing queue for evicted objects
        $this->zmqOutboundQueue = $this->zmqContext->getSocket(\ZMQ::SOCKET_PUSH);
        $this->zmqOutboundQueue->bind($this->config->getZmqOut());

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
            $this->runtimeStatistics->incrementAddedObjectCount();

            try {
                $message = RequestMessage::fromStringToArray($msg);
                $this->getQueue()->push($message['key'], $message['data'], round(microtime(true) * 1000000) + $message['timeout'], $message['type']);

                if ($this->isInLogLevel(Logger::DEBUG)) {
                    $this->logger->debug("[OnMessage] Data for key '{$message['key']}' [type '{$message['type']}', exp {$message['timeout']} ms]: ".str_replace("\n", "", var_export($message['data'], true)));
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        });
    }

    /**
     * Registers the eviction of items from the queue.
     */
    private function registerEvictionEvents() {
        $this->reactLoop->addPeriodicTimer(1 / $this->config->getEvictionTicksPerSec() , function () {
            if (($item = $this->getQueue()->evict(round(microtime(true) * 1000000))) !== null) {
                if ($this->isInLogLevel(Logger::DEBUG)) {
                    $this->logger->debug("[Eviction] Timeout detected for '{$item->getKey()}' at " . round($item->getPriority() / 1000000, 3));
                }

                $this->getZmqOutboundQueue()->send(ResponseMessage::fromQueueItemToString($item));
                $this->runtimeStatistics->incrementEvictedObjectCount();
            }
        });
    }

    /**
     * Registers a periodically triggered status event.
     */
    private function registerTimedEvents()
    {
        $this->reactLoop->addPeriodicTimer($this->config->getStatusLoopInterval(), function () {

            $memoryUsageMb = memory_get_usage(true) / 1024 / 1024;
            if ($memoryUsageMb > $this->config->getMemoryLimitMbWarn()) {
                $this->logger->warning("MemoryUsage:   {$memoryUsageMb} MB.");
            } else if ($memoryUsageMb > $this->config->getMemoryLimitMbInfo()) {
                $this->logger->info("MemoryUsage:   {$memoryUsageMb} MB.");
            }

            if (memory_get_peak_usage(true) / 1024 / 1024 > $this->config->getMemoryPeakLimitMbWarn()) {
                $this->logger->warning("MemoryPeakUsage " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB.");
            }

            $rateObjects = $this->runtimeStatistics->getAddedObjectRate($this->config->getStatusLoopInterval());
            $rateEvictions = $this->runtimeStatistics->getEvictionRate($this->config->getStatusLoopInterval());

            $this->logger->info("Added objects: {$this->runtimeStatistics->getAddedObjectCount()}, evictions: {$this->runtimeStatistics->getEvictedObjectCount()} ({$rateObjects} Obj/Sec, {$rateEvictions} Evi/Sec).");

            $this->runtimeStatistics->tick();
        });
    }

    /**
     * This allows to set a given dataMode for a request message type. Data mode can be one of
     * PriorityHashQueue::DATA_MODE_REPLACE or PriorityHashQueue::DATA_MODE_APPEND.
     *
     * Later this should become configurable via API.
     *
     * @param string $type
     * @param string $dataMode
     */
    public function setDataModeByType($type, $dataMode) {
        $this->queue->setDataModeByType($type, $dataMode);
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
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }


    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param int $level
     * @return bool
     */
    private function isInLogLevel($level) {
        return $level >= $this->config->getMinimumLogLevel();
    }

}