<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 健康检查接口
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API服务正常',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

// API版本分组
Route::prefix('v1')->group(function () {
    
    
    

    
});



// 404处理
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'code' => 404,
        'message' => 'API接口不存在',
        'data' => null,
        'timestamp' => now()->toDateTimeString(),
    ], 404);
});