<?php

namespace Awdn\VigilantQueue\Utility;


/**
 * This interface is inspired by StatsD implementations like https://github.com/domnikl/statsd-php
 */
interface MetricsInterface
{

    public function increment($key);
    public function decrement($key);
    public function count($key, $value);
    public function gauge($key, $value);
    public function set($key, $value);
    public function timing($key, $value);
    public function time($key, callable $callback);

}