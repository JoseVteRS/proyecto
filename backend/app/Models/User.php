<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public function create(string $name, string $email, string $password, string $role = 'NORMAL'): ?array
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (id, name, email, password, role, created_at, updated_at) 
                VALUES (uuid_generate_v4(), :name, :email, :password, :role, NOW(), NOW()) 
                RETURNING id, name, email, role, created_at, updated_at";

        try {
            $stmt = Database::query($sql, [
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);

            $user = $stmt->fetch();
            unset($user['password']);
            return $user;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') { // Unique violation
                throw new \Exception('El email ya está registrado');
            }
            throw new \Exception('Error al crear usuario: ' . $e->getMessage());
        }
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, name, email, role, created_at, updated_at 
                FROM users 
                WHERE id = :id";

        $stmt = Database::query($sql, [':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password, role, created_at, updated_at 
                FROM users 
                WHERE email = :email";

        $stmt = Database::query($sql, [':email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function update(string $id, array $data): ?array
    {
        $updates = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $updates[] = 'name = :name';
            $params[':name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updates[] = 'email = :email';
            $params[':email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updates[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['role'])) {
            $updates[] = 'role = :role';
            $params[':role'] = $data['role'];
        }

        if (empty($updates)) {
            return $this->findById($id);
        }

        $updates[] = 'updated_at = NOW()';
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id RETURNING id, name, email, role, created_at, updated_at";

        try {
            $stmt = Database::query($sql, $params);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (\PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new \Exception('El email ya está registrado');
            }
            throw new \Exception('Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        
        try {
            Database::query($sql, [':id' => $id]);
            return true;
        } catch (\PDOException $e) {
            throw new \Exception('Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    public function listAll(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT id, name, email, role, created_at, updated_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = Database::query($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);

        return $stmt->fetchAll();
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

