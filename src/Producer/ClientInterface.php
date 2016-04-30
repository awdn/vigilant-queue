<?php

namespace Awdn\VigilantQueue\Producer;


interface ClientInterface
{
    public function connect();

    /**
     * @param string $message
     * @return mixed
     */
    public function send($message);
}