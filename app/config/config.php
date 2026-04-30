<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => env('APP_NAME', 'Focus Ledger'),
        'env' => env('APP_ENV', 'local'),
        'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
        'url' => rtrim((string) env('APP_URL', 'https://php-site.ddev.site'), '/'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Dhaka'),
    ],
    'database' => [
        'host' => env('DB_HOST', 'db'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'db'),
        'user' => env('DB_USER', 'db'),
        'pass' => env('DB_PASS', 'db'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'timeout' => (int) env('DB_TIMEOUT', 5),
    ],
];
