<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    private function __construct()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../config/database.php';
            
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                self::$config['host'],
                self::$config['port'],
                self::$config['database']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $db = self::getInstance();
        $stmt = $db->prepare($sql);
        
        // Bindear parámetros con tipos correctos
        foreach ($params as $key => $value) {
            $paramType = PDO::PARAM_STR;
            if (is_int($value)) {
                $paramType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $paramType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $paramType = PDO::PARAM_NULL;
            }
            $stmt->bindValue($key, $value, $paramType);
        }
        
        $stmt->execute();
        return $stmt;
    }
}

