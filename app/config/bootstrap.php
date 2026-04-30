<?php

declare(strict_types=1);

require_once APP_PATH . '/helpers/helpers.php';

loadEnv(BASE_PATH . '/.env');

date_default_timezone_set((string) env('APP_TIMEZONE', 'UTC'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
    ]);
    session_start();
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);

    if (count($parts) > 1) {
        $parts[0] = strtolower($parts[0]);
    }

    $file = APP_PATH . '/' . implode('/', $parts) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

$GLOBALS['config'] = require APP_PATH . '/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');

set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception): void {
    $statusCode = $exception instanceof \App\HttpException
        ? ($exception->getCode() >= 400 && $exception->getCode() < 600 ? $exception->getCode() : 500)
        : 500;

    http_response_code($statusCode);

    $errorView = APP_PATH . '/views/errors/' . $statusCode . '.php';

    if (!is_file($errorView)) {
        $errorView = APP_PATH . '/views/errors/500.php';
    }

    $pageTitle = sprintf('%s Error', $statusCode);
    $message = $exception->getMessage();

    require $errorView;
});
