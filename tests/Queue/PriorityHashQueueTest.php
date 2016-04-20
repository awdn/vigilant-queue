<?php
/**
 * Created by PhpStorm.
 * User: aw
 * Date: 20.04.16
 * Time: 18:31
 */

namespace Awdn\Tests\VigilantQueue\Queue;


use Awdn\VigilantQueue\Queue\PriorityHashQueue;

class PriorityHashQueueTest extends \PHPUnit_Framework_TestCase
{

    public function testPush()
    {

    }

    public function testEvict()
    {
        // test1
        $data = "data1";
        $q = new PriorityHashQueue();
        $q->push("test_evict_key", $data, 1);
        $result = $q->evict(2);
        $this->assertArrayHasKey("data", $result);
        $this->assertEquals($data, $result['data']);
        $result = $q->evict(2);
        $this->assertNull($result);


        // test two objects
        $data = "data1";
        $data2 = "data2";
        $q = new PriorityHashQueue();
        $q->push("test_evict_key", $data, 1);
        $q->push("test_evict_key2", $data2, 3);

        $result = $q->evict(2);
        $this->assertArrayHasKey("data", $result);
        $this->assertEquals($data, $result['data']);

        $result = $q->evict(2);
        $this->assertNull($result);
    }
}
