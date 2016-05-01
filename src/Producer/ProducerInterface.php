<?php

namespace Awdn\VigilantQueue\Producer;

/**
 * Interface ProducerInterface
 * @package Awdn\VigilantQueue\Producer
 */
interface ProducerInterface
{
    /**
     * @return void
     */
    public function produce();
}