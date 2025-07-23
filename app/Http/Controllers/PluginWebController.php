<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * 插件管理Web控制器
 */
class PluginWebController extends Controller
{
    /**
     * 显示插件管理页面
     */
    public function index()
    {
        return view('plugins.index');
    }
}