<?php
namespace Awdn\VigilantQueue\Queue;

use Awdn\VigilantQueue\Queue\MinPriorityQueue;
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
     * @var int
     */
    private $defaultPriority = 1;

    private $extractPolicy = self::EXTRACT_ALL;

    const EXTRACT_ALL = 1;
    const EXTRACT_KEY = 2;
    const EXTRACT_PRIORITY = 3;
    const EXTRACT_DATA = 4;

    /**
     * PriorityHashQueue constructor.
     */
    public function __construct()
    {
        $this->queue = new MinPriorityQueue;
        $this->queue->setExtractFlags(MinPriorityQueue::EXTR_BOTH);
        $this->data = new \ArrayObject();
        $this->priority = new \ArrayObject();
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param int $priority
     */
    public function push($key, $data, $priority)
    {
        $this->data->offsetSet($key, $data);
        $this->priority->offsetSet($key, $priority);
        $this->queue->insert($key, $priority);
    }

    /**
     * @param int $threshold
     * @return mixed|null
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
                    $this->data->offsetUnset($key);
                    $this->priority->offsetUnset($key);

                    switch ($this->getExtractPolicy()) {
                        case self::EXTRACT_DATA:
                            return $data;
                        case self::EXTRACT_KEY:
                            return $key;
                        case self::EXTRACT_PRIORITY:
                            return $item['priority'];
                        case self::EXTRACT_ALL:
                        default:
                            return [
                                'data' => $data,
                                'key' => $key,
                                'priority' => $item['priority']
                            ];
                    }
                }
            }
        }

        return null;
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
        return $this->data->offsetGet($offset);
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
            $this->offsetSet($offset, ['data' => $value, 'priority' => $this->getDefaultPriority()]);
            return;
        } else {
            if (!isset($value['data'])) {
                $value['data'] = null;
            }
            if (!isset($value['priority'])) {
                $value['priority'] = $this->getDefaultPriority();
            }
        }

        $this->push($offset, $value['data'], $value['priority']);
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
        $this->data->unset($offset);
        $this->priority->unset($offset);
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