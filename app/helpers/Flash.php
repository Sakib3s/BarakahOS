<?php

declare(strict_types=1);

namespace App\Helpers;

class Flash
{
    public static function set(string $key, string $message, string $type = 'info'): void
    {
        $_SESSION['_flash'][$key] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    public static function get(string $key): ?array
    {
        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }

        $item = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $item;
    }

    public static function all(): array
    {
        $items = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $items;
    }
}
