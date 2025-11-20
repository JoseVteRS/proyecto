<?php

namespace App\Routes;

use App\Core\Router;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;
use App\Controllers\AuthController;
use App\Controllers\UserController;

class AdminRoutes
{
    public static function load(Router $router): void
    {
        $authController = new AuthController();
        $userController = new UserController();

        // Crear usuario ADMIN (público, sin autenticación)
        $router->post('/api/admin/users', fn($params = []) => $authController->createAdmin());

        // Listar usuarios (solo ADMIN)
        $router->get('/api/users', 
            AuthMiddleware::handle(
                RoleMiddleware::requireAdmin(fn($params = []) => $userController->index($params))
            )
        );
    }
}

