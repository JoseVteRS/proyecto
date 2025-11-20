<?php

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('App/', 'app/', $path);
    if (file_exists($path)) require $path;
});

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    $sql = file_get_contents(__DIR__ . '/create_users_table.sql');
    
    // Ejecutar cada sentencia SQL separadamente
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $db->exec($statement);
        }
    }
    
    echo "Migración ejecutada exitosamente.\n";
    
} catch (Exception $e) {
    echo "Error al ejecutar migración: " . $e->getMessage() . "\n";
    exit(1);
}

