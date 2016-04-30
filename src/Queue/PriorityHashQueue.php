<?php
namespace Awdn\VigilantQueue\Queue;


use Traversable;

/**
 * Class PriorityHashQueue
 * @package Awdn\VigilantQueue\Queue
 */
class PriorityHashQueue implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var MinPriorityQueue
     */
    private $queue;

    /**
     * @var \ArrayObject
     */
    private $data;

    /**
     * @var \ArrayObject
     */
    private $priority;

    /**
     * @var \ArrayObject
     */
    private $type;

    /**
     * @var int
     */
    private $defaultPriority = 1;

    /**
     * @var int
     */
    private $extractPolicy = self::EXTRACT_ALL;

    const EXTRACT_ALL = 1;
    const EXTRACT_KEY = 2;
    const EXTRACT_PRIORITY = 3;
    const EXTRACT_DATA = 4;
    const EXTRACT_TYPE = 5;

    /**
     * Default data mode
     * @var int
     */
    private $dataMode = self::DATA_MODE_REPLACE;

    /**
     * Data mode depending on the message type
     * @var array
     */
    private $dataModeByType = [];


    const DATA_MODE_REPLACE = 1;
    const DATA_MODE_APPEND = 2;

    /**
     * @var int
     */
    private $itemMaxSizeKb = 1024;

    /**
     * PriorityHashQueue constructor.
     */
    public function __construct()
    {
        $this->queue = new MinPriorityQueue;
        $this->queue->setExtractFlags(MinPriorityQueue::EXTR_BOTH);
        $this->data = new \ArrayObject();
        $this->priority = new \ArrayObject();
        $this->type = new \ArrayObject();
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param int $priority
     * @param string $type
     */
    public function push($key, $data, $priority, $type = null)
    {
        $this->setData($key, $data, $type);
        $this->priority->offsetSet($key, $priority);
        $this->type->offsetSet($key, $type);
        $this->queue->insert($key, $priority);
    }

    /**
     * @param int $threshold
     * @return string|null|QueueItem
     */
    public function evict($threshold)
    {
        if ($this->queue->valid()) {
            $item = $this->queue->top();
            if ($item['priority'] <= $threshold) {
                $this->queue->next();
                $key = $item['data'];

                // Return only the most recent items which have not been evicted so far.
                if ($this->priority->offsetExists($key)
                    && $this->priority->offsetGet($key) == $item['priority']
                ) {
                    $data = $this->data->offsetGet($key);
                    $type = $this->type->offsetGet($key);

                    // Unset prio, data, type for the key.
                    $this->offsetUnset($key);

                    switch ($this->getExtractPolicy()) {
                        case self::EXTRACT_DATA:
                            return $data;
                        case self::EXTRACT_KEY:
                            return $key;
                        case self::EXTRACT_TYPE:
                            return $type;
                        case self::EXTRACT_PRIORITY:
                            return $item['priority'];
                        case self::EXTRACT_ALL:
                        default:
                            return new QueueItem(
                                $key,
                                $data,
                                $item['priority'],
                                $type
                            );
                    }
                }
            }
        }

        return null;
    }

    private function setData($offset, $data, $type)
    {
        // If the global data mode is set to append by default OR if the data mode for the given message type requires
        // to append, then the data will be appended to existing data instead of replacing the value.
        if (($this->dataMode == self::DATA_MODE_APPEND || $this->getDataModeByType($type) == self::DATA_MODE_APPEND) && $this->data->offsetExists($offset)) {
            $d = $this->data->offsetGet($offset) . strlen($data) . ":" . $data;
            $this->data->offsetSet($offset, $d);

            // Message size is bigger than the allowed max size. Try to force the eviction.
            if (strlen($d) > 1024 * $this->getItemMaxSizeKb()) {
                $this->markForEviction($offset, 0);
            }
        } else {
            $this->data->offsetSet($offset, strlen($data) . ":" . $data);
        }
    }

    /**
     * @return int
     */
    public function getItemMaxSizeKb()
    {
        return $this->itemMaxSizeKb;
    }

    /**
     * @param int $itemMaxSizeKb
     */
    public function setItemMaxSizeKb($itemMaxSizeKb)
    {
        $this->itemMaxSizeKb = $itemMaxSizeKb;
    }

    /**
     * Try to enforce the eviction.
     * @todo Locking mechanism, so that a subsequent push() for the same key won't set the prio to a higher value
     * @param $offset
     */
    private function markForEviction($offset) {
        $this->queue->insert($offset, 0);
        $this->priority->offsetSet($offset, 0);
    }

    /**
     * @return string
     */
    public function getDataModeByType($type)
    {
        return isset($this->dataModeByType[$type]) ? $this->dataModeByType[$type] : self::DATA_MODE_REPLACE;
    }

    /**
     * @param string $type
     * @param string $dataModeByType
     */
    public function setDataModeByType($type, $dataMode)
    {
        $this->dataModeByType[$type] = $dataMode;
    }


    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return [
            'data' => $this->data->offsetGet($offset),
            'priority' => $this->priority->offsetGet($offset),
            'type' => $this->type->offsetGet($offset)
        ];
    }

    /**
     * Offset to set
     * The value should be an array with two indexes 'data' and 'priority'. If the priority is not
     * given the method falls back to the default priority.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (!is_array($value)) {
            $this->offsetSet($offset, ['data' => $value, 'priority' => $this->getDefaultPriority(), 'type' => null]);
            return;
        } else {
            if (!isset($value['data'])) {
                $value['data'] = null;
            }
            if (!isset($value['priority'])) {
                $value['priority'] = $this->getDefaultPriority();
            }
            if (!isset($value['type'])) {
                $value['type'] = null;
            }
        }

        $this->push($offset, $value['data'], $value['priority'], $value['type']);
    }

    /**
     * Offset to unset
     *
     * The method can not remove the data from the PriorityQueue. This could be an issue
     * if there are entries staying for a long time within the queue without being removed.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->data->offsetUnset($offset);
        $this->priority->offsetUnset($offset);
        $this->type->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->data->count();
    }

    /**
     * @return int
     */
    public function getDefaultPriority()
    {
        return $this->defaultPriority;
    }

    /**
     * @param int $defaultPriority
     */
    public function setDefaultPriority($defaultPriority)
    {
        $this->defaultPriority = $defaultPriority;
    }

    /**
     * @return int
     */
    public function getExtractPolicy()
    {
        return $this->extractPolicy;
    }

    /**
     * @param int $extractPolicy
     */
    public function setExtractPolicy($extractPolicy)
    {
        $this->extractPolicy = $extractPolicy;
    }

    /**
     * @return int
     */
    public function getDataMode()
    {
        return $this->dataMode;
    }

    /**
     * @param int $dataMode
     */
    public function setDataMode($dataMode)
    {
        $this->dataMode = $dataMode;
    }


    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->data->getIterator();
    }

    /**
     * @param string $className
     */
    public function setIteratorClass($className) {
        $this->data->setIteratorClass($className);
    }

    /**
     * @return string
     */
    public function getIteratorClass() {
        return $this->data->getIteratorClass();
    }
}