<?php

namespace Awdn\VigilantQueue\Consumer;


use Awdn\VigilantQueue\Queue\QueueItem;

class ResponseMessage implements ResponseMessageInterface
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $type;


    public function __construct($message)
    {
        $this->message = $message;
    }

    public static function fromQueueItemToString(QueueItem $item) {
        return "{$item->getKey()}:{$item->getType()}|{$item->getData()}";
        // return $item->getData();
    }

    public static function fromString($message, $doUnserialize = false) {
        $m = new ResponseMessage($message);
        $m->parse($doUnserialize);
        return $m;
    }

    public function __toString()
    {
        return $this->message;
    }

    public function parse($doUnserialize = false)
    {
        $posDataDelimiter = strpos($this->message, '|');

        if ($posDataDelimiter === false) {
            throw new \Exception("Can not parse message: ". $this->message);
        }

        list($key, $type) = explode(':', substr($this->message, 0, $posDataDelimiter));
        $this->key = $key;
        $this->type = $type;
        $data = substr($this->message, $posDataDelimiter + 1);

        $total = strlen($data);
        $offset = 0;
        $this->data = [];
        while ($offset < $total) {
            $p = strpos($data, ':', $offset);
            $len = substr($data, $offset, $p - $offset);

            $d = substr($data, $p + 1, $len);

            if ($doUnserialize) {
                $this->data[] = unserialize($d);
            } else {
                $this->data[] = $d;
            }

            $offset += strlen($len) + 1 + $len /* \0 */;
        }

    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

}