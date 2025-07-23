<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddonController;

/*
|--------------------------------------------------------------------------
| Addon API Routes
|--------------------------------------------------------------------------
|
| 这里定义了所有与Addon管理相关的API路由
|
*/

// Addon管理API路由组
Route::prefix('api/addons')->group(function () {
    // 获取所有插件列表
    Route::get('/', [AddonController::class, 'index'])->name('api.addons.index');
    
    // 获取插件统计信息
    Route::get('/stats', [AddonController::class, 'stats'])->name('api.addons.stats');
    
    // 获取钩子信息
    Route::get('/hooks', [AddonController::class, 'hooks'])->name('api.addons.hooks');
    
    // 测试钩子
    Route::post('/hooks/test', [AddonController::class, 'testHook'])->name('api.addons.hooks.test');
    
    // 同步插件目录
    Route::post('/sync', [AddonController::class, 'sync'])->name('api.addons.sync');
    
    // 批量操作插件
    Route::post('/batch', [AddonController::class, 'batch'])->name('api.addons.batch');
    
    // 激活插件
    Route::post('/activate', [AddonController::class, 'activate'])->name('api.addons.activate');
    
    // 停用插件
    Route::post('/deactivate', [AddonController::class, 'deactivate'])->name('api.addons.deactivate');
    
    // 获取单个插件信息
    Route::get('/{name}', [AddonController::class, 'show'])->name('api.addons.show');
    
    // 获取插件配置
    Route::get('/{name}/config', [AddonController::class, 'getConfig'])->name('api.addons.config.get');
    
    // 设置插件配置
    Route::post('/{name}/config', [AddonController::class, 'setConfig'])->name('api.addons.config.set');
});

// 插件通用API路由（供插件自己注册使用）
Route::prefix('api/addon')->group(function () {
    // 这里可以让各个插件注册自己的API路由
    // 例如: /api/addon/{plugin_name}/custom-endpoint
    
    // 插件数据API（通用接口）
    Route::get('/{name}/data/{key?}', function ($name, $key = null) {
        $addon = \App\Models\Addon::findByName($name);
        if (!$addon || !$addon->isActive()) {
            return \App\Http\Responses\ApiResponse::notFound('插件不存在或未激活');
        }
        
        // 这里可以实现通用的插件数据获取逻辑
        return \App\Http\Responses\ApiResponse::success([
            'addon' => $name,
            'key' => $key,
            'message' => '插件数据接口，需要具体实现'
        ]);
    })->name('api.addon.data');
});