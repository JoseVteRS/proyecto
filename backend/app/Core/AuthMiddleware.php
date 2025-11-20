<?php

namespace App\Core;

class AuthMiddleware
{
    private static function sendError(string $message, int $code = 401): void
    {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }

    public static function handle(callable $callback): callable
    {
        return function ($params = []) use ($callback) {
            $token = JWT::getTokenFromHeader();

            if (!$token) {
                self::sendError('Token de autenticación requerido');
            }

            $payload = JWT::decode($token);

            if (!$payload) {
                self::sendError('Token inválido o expirado');
            }

            $GLOBALS['user'] = [
                'id' => $payload['user_id'],
                'email' => $payload['email'],
                'role' => $payload['role']
            ];

            return call_user_func($callback, $params);
        };
    }

    public static function getCurrentUser(): ?array
    {
        return $GLOBALS['user'] ?? null;
    }
}

