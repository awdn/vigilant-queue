<?php

namespace Awdn\VigilantQueue\Producer;

/**
 * Interface ClientInterface
 * @package Awdn\VigilantQueue\Producer
 */
interface ClientInterface
{
    public function connect();

    /**
     * @param string $message
     * @return mixed
     */
    public function send($message);

    /**
     * @param $message
     * @return mixed
     */
    public function message($message);
}