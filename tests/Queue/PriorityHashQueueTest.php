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
        $q = new PriorityHashQueue();
        $max = 10000;
        $ts = microtime(true);
        for ($i = 1; $i <= $max; $i++) {
            $q->push('k-'.$i, "d-".$i, $i);
        }
        $te = microtime(true);
        $this->assertEquals($max, $q->count(), "Duration for string value test: " . ($te - $ts));

        $count = 0;
        while (($item = $q->evict(10)) !== null) {
            $count++;
            $this->assertEquals("d-".$count, $item['data']);
        }

        $this->assertEquals($count, 10);

        // Replacing one existing entry in the data array. The old item will still be in the queue, but it won't be valid.
        $q->push('k-11', 'd-11-new', 11);
        $item = $q->evict(12);
        $this->assertEquals('d-11-new', $item['data']);

        // Next eviction should be a null item which represents the old queue entry for key 'k-11'.
        $item = $q->evict(12);
        $this->assertNull($item);

        // Here we'll get the item for 'k-12'
        $item = $q->evict(12);
        $this->assertEquals('d-12', $item['data']);

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
        $this->assertEquals(2, $q->count());

        $result = $q->evict(2);
        $this->assertArrayHasKey("data", $result);
        $this->assertEquals($data, $result['data']);

        $result = $q->evict(2);
        $this->assertNull($result);

        $result = $q->evict(3);
        $this->assertArrayHasKey("data", $result);
        $this->assertEquals($data2, $result['data']);

        $this->assertEquals(0, $q->count());
    }
}
