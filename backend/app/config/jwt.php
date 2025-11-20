<?php

return [
    'secret' => $_ENV['JWT_SECRET'] ?? 'tu-secret-key-muy-segura-cambiar-en-produccion',
    'algorithm' => 'HS256',
    'expiration' => 86400, // 24 horas en segundos
];

