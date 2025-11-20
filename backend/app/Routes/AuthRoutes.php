<?php

namespace App\Routes;

use App\Core\Router;
use App\Core\AuthMiddleware;
use App\Controllers\AuthController;

class AuthRoutes
{
    public static function load(Router $router): void
    {
        $authController = new AuthController();

        // Rutas de autenticación públicas
        $router->post('/api/register', fn() => $authController->register());
        $router->post('/api/login', fn() => $authController->login());
        
        // Ruta de logout (requiere autenticación)
        $router->post('/api/logout', AuthMiddleware::handle(fn() => $authController->logout()));
    }
}

