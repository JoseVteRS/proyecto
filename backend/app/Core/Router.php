<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function dispatch(string $method, string $uri)
    {
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri]();
        }

        http_response_code(404);
        return ['error' => 'Ruta no encontrada'];
    }
}
