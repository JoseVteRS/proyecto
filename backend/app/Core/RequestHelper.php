<?php

namespace App\Core;

class RequestHelper
{
    public static function getRequestUri(): string
    {
        $requestUri = '/';

        // Prioridad 1: REQUEST_URI
        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }
        // Prioridad 2: REDIRECT_URL
        elseif (isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL'])) {
            $requestUri = $_SERVER['REDIRECT_URL'];
        }
        // Prioridad 3: PATH_INFO
        elseif (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
            $requestUri = $_SERVER['PATH_INFO'];
        }
        // Prioridad 4: ORIG_PATH_INFO
        elseif (isset($_SERVER['ORIG_PATH_INFO']) && !empty($_SERVER['ORIG_PATH_INFO'])) {
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
        }

        // Extraer solo el path (sin query string)
        $requestUri = parse_url($requestUri, PHP_URL_PATH);

        // Si la URI es /index.php, intentar obtener la original
        if ($requestUri === '/index.php' || strpos($requestUri, '/index.php/') === 0) {
            if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
                $requestUri = $_SERVER['PATH_INFO'];
            } elseif (isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL'])) {
                $requestUri = $_SERVER['REDIRECT_URL'];
            } else {
                $requestUri = '/';
            }
        }

        // Remover /index.php si está presente
        if (strpos($requestUri, '/index.php') === 0) {
            $requestUri = substr($requestUri, strlen('/index.php'));
        }

        // Normalizar: remover trailing slash excepto para root
        return rtrim($requestUri, '/') ?: '/';
    }

    public static function getRequestMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Algunos clientes envían PUT/DELETE como POST con _method
        if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        } elseif ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return $method;
    }
}

