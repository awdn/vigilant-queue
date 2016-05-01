<?php

namespace Awdn\VigilantQueue\Consumer;

/**
 * Interface ConsumerInterface
 * @package Awdn\VigilantQueue\Consumer
 */
interface ConsumerInterface
{
    /**
     * @return void
     */
    public function consume();
}