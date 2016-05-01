<?php

namespace Awdn\VigilantQueue\Server;


use React\Http\Request;

/**
 * Interface RequestMessageInterface
 * @package Awdn\VigilantQueue\Server
 */
interface RequestMessageInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * @param string $message
     * @return RequestMessageInterface
     */
    public static function fromString($message);

    /**
     * @param string $message
     * @return array
     */
    public static function fromStringToArray($message);

    /**
     * @param string $key
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param int $timeoutMicroSeconds
     */
    public function setTimeoutMicroSeconds($timeoutMicroSeconds);

    /**
     * @return int
     */
    public function getTimeoutMicroSeconds();

}