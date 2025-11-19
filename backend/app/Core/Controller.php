<?php

namespace App\Core;

class Controller
{
    protected function json(array $data, int $status = 200)
    {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }
}
