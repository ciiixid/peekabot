<?php

namespace TeleCore;

class Logger
{
    protected static ?string $logPath = null;

    public static function setPath(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        self::$logPath = rtrim($path, '/\\');
    }

    public static function error(string $message, array $context = []): void
    {
        $path = self::$logPath ?? __DIR__ . '/../storage/logs';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $timeStr = date('Y-m-d H:i:s');
        $logFile = "{$path}/error.log";

        $contextJson = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[{$timeStr}] [ERROR] {$message}{$contextJson}" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}