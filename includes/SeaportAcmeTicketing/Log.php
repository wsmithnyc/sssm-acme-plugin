<?php

namespace SeaportAcmeTicketing;

use \Exception;
use GuzzleHttp\Exception\GuzzleException;

class Log
{
    protected Database $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public static function logFactory(string $type, string $message): Log
    {
        $instance = new Log();
        $instance->database->saveLog($type, $message);

        return $instance;
    }

    //***********************  Log Types ***********************************/
    public static function info(string $message): void
    {
        self::logFactory(Constants::LOG_INFO, $message);
    }

    public static function warning(string $message): void
    {
        self::logFactory(Constants::LOG_WARNING, $message);
    }

    public static function error(string $message): void
    {
        self::logFactory(Constants::LOG_ERROR, $message);
    }

    public static function debug(string $message): void
    {
        self::logFactory(Constants::LOG_DEBUG, $message);
    }

    public static function exception(Exception $exception): void
    {
        $message = "Error: " . $exception->getMessage();

        $message .= "/n/n" . $exception->getTraceAsString();

        self::logFactory(Constants::LOG_ERROR, $message);
    }

    public static function guzzleException(GuzzleException $exception): void
    {
        $message = "API Error: " . $exception->getMessage();

        $message .= "/n/n" . $exception->getTraceAsString();

        $message .= "/n/nCode: " . $exception->getCode();

        self::logFactory(Constants::LOG_API_ERROR, $message);
    }
}
