<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern: string, regex: string, parameters: array<string, int>, handler: callable|array|string}>>
     */
    private array $routes = [];

    /**
     * @param string $method
     * @param string $path
     * @param callable|array|string $handler
     */
    public function add(string $method, string $path, callable|array|string $handler): void
    {
        $method = strtoupper($method);

        [$regex, $parameters] = $this->compilePath($path);
        $this->routes[$method][] = [
            'pattern' => $path,
            'regex' => $regex,
            'parameters' => $parameters,
            'handler' => $handler,
        ];
    }

    public function get(string $path, callable|array|string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array|string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array|string $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array|string $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    public function any(string $path, callable|array|string $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->add($method, $path, $handler);
        }
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($uri);

        $candidates = $this->routes[$method] ?? [];
        foreach ($candidates as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['parameters'] as $name => $index) {
                $params[$name] = $matches[$index] ?? null;
            }

            return $this->invoke($route['handler'], $params);
        }

        http_response_code(404);
        echo '404 - Rota não encontrada';
        return null;
    }

    private function compilePath(string $path): array
    {
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/', static function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);

        $parameters = [];
        if (preg_match_all('/\(\?P<([a-zA-Z_][a-zA-Z0-9_-]*)>[^)]+\)/', $pattern, $paramMatches, PREG_OFFSET_CAPTURE)) {
            $index = 1;
            foreach ($paramMatches[1] as $param) {
                $parameters[$param[0]] = $index;
                $index++;
            }
        }

        $regex = '#^' . $pattern . '$#';

        return [$regex, $parameters];
    }

    private function normalizePath(string $uri): string
    {
        $parsed = parse_url($uri);
        $path = $parsed['path'] ?? '/';
        return rtrim($path, '/') ?: '/';
    }

    private function invoke(callable|array|string $handler, array $params): mixed
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            if (is_string($class)) {
                $class = new $class();
            }

            return $class->$method(...array_values($params));
        }

        if (is_string($handler)) {
            if (!str_contains($handler, '@')) {
                $callable = $handler;
            } else {
                [$class, $method] = explode('@', $handler, 2);
                $controller = new $class();
                $callable = [$controller, $method];
            }

            return call_user_func_array($callable, array_values($params));
        }

        return $handler(...array_values($params));
    }
}

