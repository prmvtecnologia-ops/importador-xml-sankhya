<?php

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = require __DIR__ . '/config.php';
        }

        if ($key === null) {
            return $config;
        }

        $parts = explode('.', $key);
        $value = $config;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . '/storage' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . '/public' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('log_app')) {
    function log_app(string $message, array $context = []): void
    {
        $dir = storage_path('logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if ($context) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($dir . '/app.log', $line . PHP_EOL, FILE_APPEND);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
