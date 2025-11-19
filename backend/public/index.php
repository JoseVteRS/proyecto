<?php

use App\Core\Router;
use App\Controllers\HolaController;

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require $path;
});

$router = new Router();
$hola = new HolaController();

$router->get('/hola', fn() => $hola->index());

$response = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
echo json_encode($response);
