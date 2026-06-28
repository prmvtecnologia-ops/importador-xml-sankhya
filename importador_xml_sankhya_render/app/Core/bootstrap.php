<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

function config(string $name): array
{
    return require dirname(__DIR__, 2) . '/config/' . $name . '.php';
}

function app_log(string $message, array $context = []): void
{
    $dir = dirname(__DIR__, 2) . '/storage/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;

    if ($context) {
        $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    file_put_contents($dir . '/app.log', $line . PHP_EOL, FILE_APPEND);
}
