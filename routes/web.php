<?php

use App\Http\Controllers\PluginWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 插件管理页面
Route::get('/plugins', [PluginWebController::class, 'index'])->name('plugins.index');
Route::get('/addons', [App\Http\Controllers\Web\AddonWebController::class, 'index'])->name('addons.index');
