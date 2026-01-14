<?php
namespace Core;

class Logger {
    public const ERROR = 'ERROR';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const DEBUG = 'DEBUG';

    private static function getLogFilePath(): string {
        $logDir = __DIR__ . '/../storage/logs/';
        $date = date('Y-m-d');
        return $logDir . 'app-' . $date . '.log';
    }

    public static function log(string $level, string $message) {
        $logFile = self::getLogFilePath();

        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level]: $message" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public static function info(string $message) {
        self::log(self::INFO, $message);
    }
    public static function error(string $message) {
        self::log(self::ERROR, $message);
    }
    public static function warning(string $message) {
        self::log(self::WARNING, $message);
    }
    public static function debug(string $message) {
        self::log(self::DEBUG, $message);
    }
}