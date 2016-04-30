<?php

namespace Awdn\VigilantQueue\Consumer;


interface ClientInterface
{
    public function connect();
    public function receive();
}