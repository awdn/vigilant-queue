<?php

namespace Awdn\VigilantQueue\Consumer;

/**
 * Interface ClientInterface
 * @package Awdn\VigilantQueue\Consumer
 */
interface ClientInterface
{
    /**
     * @return void
     */
    public function connect();

    /**
     * @return string
     */
    public function receive();
}