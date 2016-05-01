<?php

namespace Awdn\VigilantQueue\Utility;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class ConsoleLog
 * @package Awdn\VigilantQueue\Utility
 */
class ConsoleLog
{

    /**
     * Creates a logger which writes to stdout.
     *
     * @param string $name
     * @param int $logLevel
     * @return Logger
     */
    public static function loggerFactory($name, $logLevel)
    {
        $output = "[%datetime%] %channel%.%level_name%: %message%\n";
        $formatter = new LineFormatter($output);

        $streamHandler = new StreamHandler('php://stdout', self::parseLogLevel($logLevel));
        $streamHandler->setFormatter($formatter);

        $logger = new Logger($name);
        $logger->pushHandler($streamHandler);

        return $logger;
    }

    /**
     * Converts a given log level string to a log level integer.
     *
     * @param string $logLevelStr Values could be DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
     * @return int
     * @throws \Exception
     */
    public static function parseLogLevel($logLevelStr) {
        if (is_numeric($logLevelStr)) {
            return (int)$logLevelStr;
        } else {
            switch (strtoupper($logLevelStr)) {
                case Logger::getLevelName(Logger::DEBUG):
                    $logLevel = Logger::DEBUG;
                    break;
                case Logger::getLevelName(Logger::INFO):
                    $logLevel = Logger::INFO;
                    break;
                case Logger::getLevelName(Logger::NOTICE):
                    $logLevel = Logger::NOTICE;
                    break;
                case Logger::getLevelName(Logger::WARNING):
                    $logLevel = Logger::WARNING;
                    break;
                case Logger::getLevelName(Logger::ERROR):
                    $logLevel = Logger::ERROR;
                    break;
                case Logger::getLevelName(Logger::CRITICAL):
                    $logLevel = Logger::CRITICAL;
                    break;
                case Logger::getLevelName(Logger::ALERT):
                    $logLevel = Logger::ALERT;
                    break;
                case Logger::getLevelName(Logger::EMERGENCY):
                    $logLevel = Logger::EMERGENCY;
                    break;
                default:
                    throw new \Exception("Log level '{$logLevelStr}' is not supported!'");
            }
            return $logLevel;
        }
    }
}