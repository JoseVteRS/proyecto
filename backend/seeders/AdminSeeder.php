<?php

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('App/', 'app/', $path);
    if (file_exists($path)) require $path;
});

use App\Core\Database;
use App\Models\User;

try {
    $db = Database::getInstance();
    $userModel = new User();

    // Verificar si ya existe un usuario ADMIN
    $stmt = Database::query("SELECT COUNT(*) as count FROM users WHERE role = 'ADMIN'");
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        echo "Ya existe un usuario ADMIN en la base de datos.\n";
        exit(0);
    }

    // Crear usuario ADMIN por defecto
    $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com';
    $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'admin123';
    $adminNombre = $_ENV['ADMIN_NOMBRE'] ?? 'Administrador';

    $admin = $userModel->create($adminNombre, $adminEmail, $adminPassword, 'ADMIN');

    echo "Usuario ADMIN creado exitosamente:\n";
    echo "Email: {$adminEmail}\n";
    echo "Password: {$adminPassword}\n";
    echo "ID: {$admin['id']}\n";

} catch (Exception $e) {
    echo "Error al crear usuario ADMIN: " . $e->getMessage() . "\n";
    exit(1);
}

