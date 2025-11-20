<?php

namespace App\Routes;

use App\Core\Router;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;
use App\Controllers\UserController;

class UserRoutes
{
    public static function load(Router $router): void
    {
        $userController = new UserController();

        // Ver usuario específico (ADMIN o propio)
        $router->get('/api/users/{id}', 
            AuthMiddleware::handle(
                RoleMiddleware::requireOwnOrAdmin(fn($params) => $userController->show($params))
            )
        );

        // Actualizar usuario (ADMIN o propio)
        $router->put('/api/users/{id}', 
            AuthMiddleware::handle(
                RoleMiddleware::requireOwnOrAdmin(fn($params) => $userController->update($params))
            )
        );

        // Eliminar usuario (ADMIN o propio)
        $router->delete('/api/users/{id}', 
            AuthMiddleware::handle(
                RoleMiddleware::requireOwnOrAdmin(fn($params) => $userController->delete($params))
            )
        );
    }
}

