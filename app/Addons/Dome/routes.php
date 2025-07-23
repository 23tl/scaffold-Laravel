<?php

use Illuminate\Support\Facades\Route;
use App\Addons\Dome\Controllers\DomeController;

/*
|--------------------------------------------------------------------------
| Dome 插件路由
|--------------------------------------------------------------------------
|
| 这里定义了 Dome 插件的路由
|
*/

// API 路由
Route::prefix('api')->group(function () {
    // 插件信息
    Route::get('/info', [DomeController::class, 'info']);
    
    // 数据管理 CRUD
    Route::get('/data', [DomeController::class, 'index']);
    Route::post('/data', [DomeController::class, 'store']);
    Route::get('/data/{id}', [DomeController::class, 'show']);
    Route::put('/data/{id}', [DomeController::class, 'update']);
    Route::delete('/data/{id}', [DomeController::class, 'destroy']);
    
    // 插件动作
    Route::post('/action', [DomeController::class, 'action']);
    
    // 测试过滤器
    Route::post('/test-filter', [DomeController::class, 'testFilter']);
    
    // 配置管理
    Route::get('/config', [DomeController::class, 'getConfig']);
    Route::post('/config', [DomeController::class, 'setConfig']);
});

// Web 路由
Route::prefix('web')->group(function () {
    // 插件主页
    Route::get('/', [DomeController::class, 'home'])->name('addon.dome.home');
    
    // 插件设置页
    Route::get('/settings', [DomeController::class, 'settings'])->name('addon.dome.settings');
    
    // 插件管理页
    Route::get('/manage', [DomeController::class, 'manage'])->name('addon.dome.manage');
});