<?php

namespace App\Core;

class Controller
{
    protected function json(array $data, int $status = 200)
    {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    protected function getRequestBody(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        return $data ?? [];
    }

    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "El campo {$field} es requerido";
            }
        }
        return $errors;
    }

    protected function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function error(string $message, int $status = 400)
    {
        $this->json(['error' => $message], $status);
    }

    protected function success(array $data = [], string $message = '', int $status = 200)
    {
        if ($message) {
            $data['message'] = $message;
        }
        $this->json($data, $status);
    }

    protected static function getHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
}
