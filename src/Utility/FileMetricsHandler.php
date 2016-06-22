<?php

namespace Awdn\VigilantQueue\Utility;


class FileMetricsHandler implements MetricsInterface
{

    private $file;
    private $handle;

    public function __construct($file) {
        $this->file = $file;

        $this->handle = fopen($file, "w+");
        if (!is_resource($this->handle)) {
            throw new \Exception("File {$file} can not be opened.");
        }
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    public function increment($key)
    {
        $this->write('increment', $key);
    }

    public function decrement($key)
    {
        $this->write('decrement', $key);
    }

    public function count($key, $value)
    {
        $this->write('count', $key, $value);
    }

    public function gauge($key, $value)
    {
        $this->write('gauge', $key, $value);
    }

    public function set($key, $value)
    {
        $this->write('set', $key, $value);
    }

    public function timing($key, $value)
    {
        $this->write('timing', $key, $value);
    }

    public function time($key, callable $callback)
    {
        $tStart = microtime(true);
        call_user_func($callback);
        $this->write('time', $key, microtime(true) - $tStart);
    }

    private function write($name, $key, $value = null) {
        fputcsv($this->handle, array($name, $key, $value),',', '"');
    }
}