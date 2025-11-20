<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function put(string $path, callable $callback)
    {
        $this->routes['PUT'][$path] = $callback;
    }

    public function delete(string $path, callable $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    public function patch(string $path, callable $callback)
    {
        $this->routes['PATCH'][$path] = $callback;
    }

    private function matchRoute(string $method, string $uri): ?array
    {
        // Normalizar la URI: remover query string y normalizar
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        if (!isset($this->routes[$method])) {
            return null;
        }

        // Buscar ruta exacta primero
        if (isset($this->routes[$method][$uri])) {
            return ['callback' => $this->routes[$method][$uri], 'params' => []];
        }

        // Buscar ruta con parámetros dinámicos
        foreach ($this->routes[$method] as $route => $callback) {
            // Normalizar la ruta también para comparar
            $normalizedRoute = rtrim($route, '/') ?: '/';
            
            // Si la ruta no tiene parámetros dinámicos, saltar
            if (strpos($normalizedRoute, '{') === false) {
                continue;
            }
            
            // Convertir ruta a patrón regex
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $normalizedRoute);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remover el match completo
                preg_match_all('/\{([^}]+)\}/', $normalizedRoute, $paramNames);
                $params = [];
                foreach ($paramNames[1] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }
                return ['callback' => $callback, 'params' => $params];
            }
        }

        return null;
    }

    public function dispatch(string $method, string $uri)
    {
        $match = $this->matchRoute($method, $uri);

        if ($match) {
            $callback = $match['callback'];
            $params = $match['params'];
            
            if (is_callable($callback)) {
                return call_user_func($callback, $params);
            }
        }

        // Verificar si la ruta existe en otro método HTTP
        foreach ($this->routes as $routeMethod => $routes) {
            if ($routeMethod !== $method && $this->matchRoute($routeMethod, $uri)) {
                http_response_code(405);
                return ['error' => "Method {$method} not valid"];
            }
        }

        http_response_code(404);
        return ['error' => 'Ruta no encontrada'];
    }
}
