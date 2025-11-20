<?php

namespace App\Core;

class CSRF
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function generateToken(): string
    {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function getToken(): ?string
    {
        self::startSession();
        return $_SESSION['csrf_token'] ?? null;
    }

    public static function validateToken(string $token): bool
    {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function regenerateToken(): string
    {
        self::startSession();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function getTokenFromRequest(): ?string
    {
        $headers = \App\Core\Controller::getHeaders();
        
        // Buscar en header
        $token = $headers['X-CSRF-Token'] 
              ?? $headers['x-csrf-token'] 
              ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
              ?? null;

        if ($token) {
            return $token;
        }

        // Buscar en body JSON
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        
        return $data['csrf_token'] ?? null;
    }
}

