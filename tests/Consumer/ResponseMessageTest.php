<?php

namespace Awdn\VigilantQueue\Tests\Consumer;


use Awdn\VigilantQueue\Server\ResponseMessage;

class ResponseMessageTest extends \PHPUnit_Framework_TestCase
{

    public function testParseAndGetData()
    {
        //$m = str_replace("\0","",'30:a:2:{s:1:"a";i:0;s:1:"b";i:0;}30:a:2:{s:1:"a";i:7;s:1:"b";i:8;}31:a:2:{s:1:"a";i:0;s:1:"b";i:10;}31:a:2:{s:1:"a";i:10;s:1:"b";i:4;}30:a:2:{s:1:"a";i:3;s:1:"b";i:2;}30:a:2:{s:1:"a";i:8;s:1:"b";i:2;}30:a:2:{s:1:"a";i:5;s:1:"b";i:8;}30:a:2:{s:1:"a";i:7;s:1:"b";i:3;}30:a:2:{s:1:"a";i:5;s:1:"b";i:9;}30:a:2:{s:1:"a";i:9;s:1:"b";i:6;}');
        $m = ('key:type|30:a:2:{s:1:"a";i:0;s:1:"b";i:0;}30:a:2:{s:1:"a";i:7;s:1:"b";i:8;}31:a:2:{s:1:"a";i:0;s:1:"b";i:10;}31:a:2:{s:1:"a";i:10;s:1:"b";i:4;}30:a:2:{s:1:"a";i:3;s:1:"b";i:2;}30:a:2:{s:1:"a";i:8;s:1:"b";i:2;}30:a:2:{s:1:"a";i:5;s:1:"b";i:8;}30:a:2:{s:1:"a";i:7;s:1:"b";i:3;}30:a:2:{s:1:"a";i:5;s:1:"b";i:9;}30:a:2:{s:1:"a";i:9;s:1:"b";i:6;}');
        $r = new ResponseMessage($m);
        $r->parse();
        $this->assertEquals(10,count($r->getData()));
    }
}
