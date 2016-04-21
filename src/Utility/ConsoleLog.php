<?php

namespace Awdn\VigilantQueue\Utility;

class ConsoleLog
{
    public static function log($message, $path = null) {
        echo date("Y-m-d H:i:s") .($path !== null ? " - " .$path : "") ." - {$message}\n";
    }
}