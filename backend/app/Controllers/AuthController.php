<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\JWT;
use App\Core\CSRF;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register()
    {
        $this->validateAndCreateUser('NORMAL', 'Usuario registrado correctamente');
    }

    public function login()
    {
        $data = $this->getRequestBody();

        $errors = $this->validateRequired($data, ['email', 'password']);
        if (!empty($errors)) {
            $this->error(implode(', ', $errors));
        }

        $email = $this->sanitize($data['email']);
        $password = $data['password'];

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->error('Credenciales inválidas', 401);
        }

        // Generar JWT
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $token = JWT::encode($payload);

        // Generar token CSRF
        $csrfToken = CSRF::generateToken();

        // Establecer cookie de sesión
        setcookie('session_token', $token, [
            'expires' => time() + 86400, // 24 horas
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        $this->success([
            'token' => $token,
            'csrf_token' => $csrfToken,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Login exitoso');
    }

    public function logout()
    {
        // Invalidar cookie
        setcookie('session_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true
        ]);

        // Limpiar token CSRF
        CSRF::startSession();
        unset($_SESSION['csrf_token']);

        $this->success([], 'Logout exitoso');
    }

    public function createAdmin()
    {
        $this->validateAndCreateUser('ADMIN', 'Usuario ADMIN creado correctamente');
    }

    private function validateAndCreateUser(string $role, string $successMessage): void
    {
        $data = $this->getRequestBody();

        $errors = $this->validateRequired($data, ['name', 'email', 'password']);
        if (!empty($errors)) {
            $this->error(implode(', ', $errors));
        }

        $name = $this->sanitize($data['name']);
        $email = $this->sanitize($data['email']);
        $password = $data['password'];

        if (!$this->validateEmail($email)) {
            $this->error('Email inválido');
        }

        if (strlen($password) < 6) {
            $this->error('La contraseña debe tener al menos 6 caracteres');
        }

        try {
            $user = $this->userModel->create($name, $email, $password, $role);
            $this->success($user, $successMessage, 201);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}

