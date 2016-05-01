<?php

namespace Awdn\VigilantQueue\Queue;

/**
 * Class MinPriorityQueue
 * @package Awdn\VigilantQueue\Queue
 */
class MinPriorityQueue extends \SplPriorityQueue
{

    /**
     * @param mixed $priority1
     * @param mixed $priority2
     * @return int
     */
    public function compare($priority1, $priority2)
    {
        if ($priority1 === $priority2) return 0;
        return $priority1 < $priority2 ? 1 : -1;
    }
}