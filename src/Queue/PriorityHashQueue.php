<?php
namespace Awdn\VigilantQueue\Queue;

use Awdn\VigilantQueue\Queue\MinPriorityQueue;

class PriorityHashQueue implements \Iterator, \ArrayAccess, \Countable
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
     * @param int $threshold
     * @return mixed|null
     */
    public function evict($threshold)
    {
        if ($this->queue->valid()) {
            $item = (object)$this->queue->top();
            if ($item->priority <= $threshold) {
                $this->queue->next();
                if ($this->priority->offsetExists($item->data)
                    && $this->priority->offsetGet($item->data) == $item->priority)
                {
                    $data = $this->data->offsetGet($item->data);
                    $this->data->offsetUnset($item->data);
                    $this->priority->offsetUnset($item->data);
                    //return $data;
                    return ['data' => $data, 'key' => $item->data, 'priority' => $item->priority];
                }
            }
        }

        return null;
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
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->queue->current();
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->queue->next();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->queue->key();
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->queue->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->queue->rewind();
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


}