<?php

namespace Awdn\VigilantQueue\Queue;

/**
 * Class MinPriorityQueue
 * @package Awdn\VigilantQueue\Queue
 */
class MinPriorityQueue extends \SplPriorityQueue
{

    public function compare($priority1, $priority2)
    {
        if ($priority1 === $priority2) return 0;
        return $priority1 < $priority2 ? 1 : -1;
    }
}