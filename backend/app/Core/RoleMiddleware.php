<?php

namespace App\Core;

class RoleMiddleware
{
    private static function sendError(string $message, int $code = 403): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }

    public static function requireAdmin(callable $callback): callable
    {
        return function ($params = []) use ($callback) {
            $user = AuthMiddleware::getCurrentUser();

            if (!$user) {
                self::sendError('No autenticado', 401);
            }

            if ($user['role'] !== 'ADMIN') {
                self::sendError('Acceso denegado. Se requiere rol ADMIN');
            }

            return call_user_func($callback, $params);
        };
    }

    public static function requireOwnOrAdmin(callable $callback): callable
    {
        return function ($params = []) use ($callback) {
            $user = AuthMiddleware::getCurrentUser();

            if (!$user) {
                self::sendError('No autenticado', 401);
            }

            $requestedUserId = $params['id'] ?? null;

            if (!$requestedUserId) {
                self::sendError('ID de usuario requerido', 400);
            }

            if ($user['role'] !== 'ADMIN' && $user['id'] !== $requestedUserId) {
                self::sendError('Acceso denegado. Solo puedes acceder a tu propia cuenta');
            }

            return call_user_func($callback, $params);
        };
    }
}

