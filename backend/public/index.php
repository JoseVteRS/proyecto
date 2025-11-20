<?php

use App\Core\Router;
use App\Core\RequestHelper;
use App\Controllers\HolaController;
use App\Routes\AuthRoutes;
use App\Routes\UserRoutes;
use App\Routes\AdminRoutes;

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('App/', 'app/', $path);
    if (file_exists($path)) require $path;
});

$router = new Router();
$hola = new HolaController();

// Ruta de prueba
$router->get('/api/hola', fn() => $hola->index());

// Cargar rutas por módulo
AuthRoutes::load($router);
UserRoutes::load($router);
AdminRoutes::load($router);

// Obtener URI y método HTTP
$requestUri = RequestHelper::getRequestUri();
$method = RequestHelper::getRequestMethod();

$response = $router->dispatch($method, $requestUri);

// Solo mostrar respuesta si no se ha enviado ya (los controladores usan exit)
if ($response !== null) {
    echo json_encode($response);
}
