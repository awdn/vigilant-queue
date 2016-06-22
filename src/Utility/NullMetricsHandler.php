<?php

namespace Awdn\VigilantQueue\Utility;


class NullMetricsHandler implements MetricsInterface
{

    public function increment($key)
    {
        // TODO: Implement increment() method.
    }

    public function decrement($key)
    {
        // TODO: Implement decrement() method.
    }

    public function count($key, $value)
    {
        // TODO: Implement count() method.
    }

    public function gauge($key, $value)
    {
        // TODO: Implement gauge() method.
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
    }

    public function timing($key, $value)
    {
        // TODO: Implement timing() method.
    }

    public function time($key, callable $callback)
    {
        // TODO: Implement time() method.
    }
}