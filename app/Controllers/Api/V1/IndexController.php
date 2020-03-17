<?php

namespace App\Controllers\Api\V1;

use App\Base\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return $this->jsonResponse('success',[
           'Greeting'
        ]);
    }
}