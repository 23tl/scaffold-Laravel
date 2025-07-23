<?php

namespace App\Addons\TestNewAddon\Controllers;


use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function info()
    {
        return $this->success([
            'ok' => 'o'
        ]);
    }
}
