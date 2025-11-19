<?php

namespace App\Controllers;

use App\Core\Controller;

class HolaController extends Controller
{
    public function index()
    {
        $this->json([
            "message" => "Hola mundo"
        ]);
    }
}
