<?php

namespace Awdn\Tests\VigilantQueue\Queue;

use Awdn\VigilantQueue\Queue\MinPriorityQueue;

/**
 * Class MinPriorityQueueTest
 * @covers Awdn\VigilantQueue\Queue\MinPriorityQueue
 * @package Awdn\Tests\VigilantQueue\Queue
 */
class MinPriorityQueueTest extends \PHPUnit_Framework_TestCase
{

    public function testCompare()
    {
        $q = new MinPriorityQueue();
        $this->assertEquals(0, $q->compare(1,1));
        $this->assertEquals(-1, $q->compare(1,0));
        $this->assertEquals(1, $q->compare(0,1));
    }

}
