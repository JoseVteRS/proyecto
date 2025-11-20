<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\CSRF;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(array $params = [])
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            $users = $this->userModel->listAll($limit, $offset);
            $this->success(['users' => $users, 'total' => count($users)]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    public function show(array $params)
    {
        $id = $this->validateUserId($params);

        try {
            $user = $this->userModel->findById($id);

            if (!$user) {
                $this->error('Usuario no encontrado', 404);
            }

            $this->success($user);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    public function update(array $params)
    {
        $this->validateCSRF();
        $id = $this->validateUserId($params);

        $data = $this->getRequestBody();

        // Validar que al menos un campo se esté actualizando
        $allowedFields = ['name', 'email', 'password'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'email' && !$this->validateEmail($data[$field])) {
                    $this->error('Email inválido');
                }
                if ($field === 'password' && strlen($data[$field]) < 6) {
                    $this->error('La contraseña debe tener al menos 6 caracteres');
                }
                $updateData[$field] = $field === 'password' ? $data[$field] : $this->sanitize($data[$field]);
            }
        }

        // Solo ADMIN puede cambiar roles
        $user = AuthMiddleware::getCurrentUser();
        if (isset($data['role']) && $user['role'] === 'ADMIN') {
            if (!in_array($data['role'], ['ADMIN', 'NORMAL'])) {
                $this->error('Rol inválido');
            }
            $updateData['role'] = $data['role'];
        }

        if (empty($updateData)) {
            $this->error('No hay campos para actualizar', 400);
        }

        try {
            $updatedUser = $this->userModel->update($id, $updateData);

            if (!$updatedUser) {
                $this->error('Usuario no encontrado', 404);
            }

            $this->success($updatedUser, 'Usuario actualizado correctamente');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function delete(array $params)
    {
        $this->validateCSRF();
        $id = $this->validateUserId($params);

        try {
            $deleted = $this->userModel->delete($id);

            if (!$deleted) {
                $this->error('Usuario no encontrado', 404);
            }

            $this->success([], 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    private function validateCSRF(): void
    {
        $csrfToken = CSRF::getTokenFromRequest();
        if (!$csrfToken || !CSRF::validateToken($csrfToken)) {
            $this->error('Token CSRF inválido', 403);
        }
    }

    private function validateUserId(array $params): string
    {
        $id = $params['id'] ?? '';
        
        // Validar formato UUID
        if (empty($id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            $this->error('ID de usuario inválido', 400);
        }
        
        return $id;
    }
}

