<?php

return [
    'host' => $_ENV['DB_HOST'] ?? 'postgres',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['DB_NAME'] ?? 'appdb',
    'username' => $_ENV['DB_USER'] ?? 'admin',
    'password' => $_ENV['DB_PASSWORD'] ?? 'admin',
];

