<?php


namespace App\Http\Controllers;


use App\Http\Requests\TestRequest;

class TestController extends Controller
{
    public function index(TestRequest $request)
    {
        return 'Hello World';
    }
}
