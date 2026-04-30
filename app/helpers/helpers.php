<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $value = trim($value, "\"'");
        }

        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false || $value === null ? $default : $value;
}

function config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['config'] ?? [];

    if ($key === null) {
        return $config;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function base_url(string $path = ''): string
{
    $baseUrl = rtrim((string) config('app.url', ''), '/');
    $path = trim($path, '/');

    return $path === '' ? $baseUrl : $baseUrl . '/' . $path;
}

function asset_url(string $path = ''): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $basePath = parse_url((string) config('app.url', ''), PHP_URL_PATH) ?: '';

    if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath)) ?: '/';
    }

    $path = '/' . trim($path, '/');

    return $path === '/' ? $path : rtrim($path, '/');
}

function is_current_path(string $path): bool
{
    return current_path() === $path;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function with_old_input(array $input): void
{
    unset($input['_token'], $input['password'], $input['password_confirmation']);
    $_SESSION['_old'] = $input;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function with_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function errors(): array
{
    return $_SESSION['_errors'] ?? [];
}

function error(string $key): ?string
{
    return $_SESSION['_errors'][$key] ?? null;
}

function field_class(string $key, string $baseClass): string
{
    return $baseClass . (error($key) !== null ? ' is-invalid' : '');
}

function clear_errors(): void
{
    unset($_SESSION['_errors']);
}

function component(string $name, array $data = []): void
{
    $file = APP_PATH . '/views/components/' . trim($name, '/') . '.php';

    if (!is_file($file)) {
        throw new \App\HttpException('The requested component could not be loaded.', 500);
    }

    extract($data, EXTR_SKIP);

    require $file;
}

function capture(callable $callback): string
{
    ob_start();
    $callback();

    return (string) ob_get_clean();
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'done' => 'text-bg-success',
        'partial' => 'text-bg-warning',
        'skipped_with_note' => 'text-bg-primary',
        'missed' => 'text-bg-danger',
        'pending' => 'text-bg-secondary',
        'completed', 'on_time' => 'text-bg-success',
        'delayed', 'in_progress' => 'text-bg-warning',
        'cancelled' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
}

function status_label(string $status): string
{
    return match ($status) {
        'on_time' => 'On Time',
        'skipped_with_note' => 'Skipped With Note',
        'in_progress' => 'In Progress',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

function prayer_status_label(string $status): string
{
    return match ($status) {
        'on_time' => 'On Time',
        'delayed' => 'Delayed',
        'missed' => 'Missed',
        'pending' => 'Pending',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

function distraction_type_label(string $type): string
{
    return match ($type) {
        'mobile_used', 'phone_near' => 'Mobile Used',
        'social_media_used' => 'Social Media Used',
        'waste_time', 'too_many_breaks' => 'Waste Time',
        default => ucwords(str_replace('_', ' ', $type)),
    };
}

function distraction_badge_class(string $type): string
{
    return match ($type) {
        'mobile_used', 'phone_near' => 'text-bg-warning',
        'social_media_used' => 'text-bg-primary',
        'waste_time', 'too_many_breaks' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
}

function format_duration(int $minutes): string
{
    if ($minutes <= 0) {
        return '0m';
    }

    $hours = intdiv($minutes, 60);
    $remainingMinutes = $minutes % 60;

    if ($hours > 0 && $remainingMinutes > 0) {
        return sprintf('%dh %dm', $hours, $remainingMinutes);
    }

    if ($hours > 0) {
        return sprintf('%dh', $hours);
    }

    return sprintf('%dm', $remainingMinutes);
}

function format_decimal_hours(int $minutes): string
{
    return number_format($minutes / 60, 1) . 'h';
}

function auth_user_id(): ?int
{
    $userId = $_SESSION['auth']['user_id'] ?? null;

    return $userId === null ? null : (int) $userId;
}

function is_authenticated(): bool
{
    return auth_user_id() !== null;
}

function current_user(): ?array
{
    static $resolvedUser = false;
    static $user = null;

    if ($resolvedUser) {
        return $user;
    }

    $resolvedUser = true;

    if (!is_authenticated()) {
        return null;
    }

    $model = new \App\Models\User();
    $user = $model->findById(auth_user_id());

    if ($user === null) {
        logout_user();

        return null;
    }

    return $user;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['auth'] = [
        'user_id' => $userId,
    ];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
