<?php

use App\Addons\TestNewAddon\Controllers\BaseController;
use Illuminate\Support\Facades\Route;
use App\Addons\TestNewAddon\Controllers\TestNewAddonController;

/*
|--------------------------------------------------------------------------
| TestNewAddon 插件路由
|--------------------------------------------------------------------------
|
| 这里定义了 TestNewAddon 插件的路由
|
*/

// API 路由
Route::prefix('api')->group(function () {
    // 插件信息
    Route::get('/info', [TestNewAddonController::class, 'info']);
    
    // 数据管理 CRUD
    Route::get('/data', [TestNewAddonController::class, 'index']);
    Route::post('/data', [TestNewAddonController::class, 'store']);
    Route::get('/data/{id}', [TestNewAddonController::class, 'show']);
    Route::put('/data/{id}', [TestNewAddonController::class, 'update']);
    Route::delete('/data/{id}', [TestNewAddonController::class, 'destroy']);
    
    // 插件动作
    Route::post('/action', [TestNewAddonController::class, 'action']);
    
    // 测试过滤器
    Route::post('/test-filter', [TestNewAddonController::class, 'testFilter']);
    
    // 配置管理
    Route::get('/config', [TestNewAddonController::class, 'getConfig']);
    Route::post('/config', [TestNewAddonController::class, 'setConfig']);

    Route::get('aa', [BaseController::class, 'info']);
});

// Web 路由
Route::prefix('web')->group(function () {
    // 插件主页
    Route::get('/', [TestNewAddonController::class, 'home'])->name('addon.testnewaddon.home');
    
    // 插件设置页
    Route::get('/settings', [TestNewAddonController::class, 'settings'])->name('addon.testnewaddon.settings');
    
    // 插件管理页
    Route::get('/manage', [TestNewAddonController::class, 'manage'])->name('addon.testnewaddon.manage');
});