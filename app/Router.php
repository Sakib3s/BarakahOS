<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|string $action, array|string $middlewares = []): void
    {
        $this->addRoute('GET', $path, $action, $middlewares);
    }

    public function post(string $path, callable|string $action, array|string $middlewares = []): void
    {
        $this->addRoute('POST', $path, $action, $middlewares);
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($this->extractPath($uri));
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            throw new HttpException('Page not found.', 404);
        }

        $action = $route['action'];

        foreach ($route['middlewares'] as $middleware) {
            $this->runMiddleware($middleware);
        }

        if (is_callable($action)) {
            $action();

            return;
        }

        [$controller, $handler] = explode('@', $action, 2);
        $controllerClass = 'App\\Controllers\\' . $controller;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException(sprintf('Controller "%s" was not found.', $controllerClass));
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $handler)) {
            throw new RuntimeException(sprintf('Method "%s::%s" was not found.', $controllerClass, $handler));
        }

        $instance->{$handler}();
    }

    private function addRoute(string $method, string $path, callable|string $action, array|string $middlewares = []): void
    {
        $this->routes[$method][$this->normalizePath($path)] = [
            'action' => $action,
            'middlewares' => (array) $middlewares,
        ];
    }

    private function runMiddleware(string $middleware): void
    {
        $middlewareClass = 'App\\Middleware\\' . $middleware;

        if (!class_exists($middlewareClass)) {
            throw new RuntimeException(sprintf('Middleware "%s" was not found.', $middlewareClass));
        }

        $instance = new $middlewareClass();

        if (!method_exists($instance, 'handle')) {
            throw new RuntimeException(sprintf('Middleware "%s" does not define handle().', $middlewareClass));
        }

        $instance->handle();
    }

    private function extractPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = parse_url((string) config('app.url', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return $path;
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }
}
