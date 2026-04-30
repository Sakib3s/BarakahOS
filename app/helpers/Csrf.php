<?php

declare(strict_types=1);

namespace App\Helpers;

use App\HttpException;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            e(self::token())
        );
    }

    public static function verify(?string $token): bool
    {
        if (!isset($_SESSION['_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], (string) $token);
    }

    public static function ensureValid(?string $token): void
    {
        if (!self::verify($token)) {
            throw new HttpException('The request token is invalid or expired.', 403);
        }
    }
}
