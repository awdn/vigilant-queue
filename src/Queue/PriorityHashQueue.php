<?php
namespace Awdn\VigilantQueue\Queue;

use Awdn\VigilantQueue\Queue\MinPriorityQueue;

class PriorityHashQueue
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
}