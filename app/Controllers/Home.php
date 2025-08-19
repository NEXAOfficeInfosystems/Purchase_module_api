<?php

namespace App\Controllers;

use App\Models\api\Table2;
use CodeIgniter\RESTful\ResourceController;

class Home extends ResourceController
{
    public function index(): string
    {
        return view('welcome_message');
    }

}
