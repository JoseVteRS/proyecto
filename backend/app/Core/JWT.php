<?php

namespace App\Core;

class JWT
{
    private static array $config = [];

    private static function getConfig(): array
    {
        if (empty(self::$config)) {
            self::$config = require __DIR__ . '/../config/jwt.php';
        }
        return self::$config;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function encode(array $payload): string
    {
        $config = self::getConfig();
        $header = [
            'typ' => 'JWT',
            'alg' => $config['algorithm']
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payload['exp'] = time() + $config['expiration'];
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $config['secret'],
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $config = self::getConfig();
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $config['secret'],
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function getTokenFromHeader(): ?string
    {
        $headers = \App\Core\Controller::getHeaders();
        
        $authHeader = $headers['Authorization'] 
                   ?? $headers['authorization'] 
                   ?? $_SERVER['HTTP_AUTHORIZATION'] 
                   ?? null;

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

