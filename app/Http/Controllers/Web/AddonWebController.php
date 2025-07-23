<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Plugins\PluginManager;
use App\Models\Addon;
use Illuminate\Http\Request;

/**
 * 插件 Web管理控制器
 */
class AddonWebController extends Controller
{
    protected PluginManager $pluginManager;
    
    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }
    
    /**
     * 显示插件管理页面
     */
    public function index()
    {
        $addons = Addon::all();
        $stats = [
            'total' => $addons->count(),
            'active' => $addons->where('is_active', true)->count(),
            'inactive' => $addons->where('is_active', false)->count(),
            'error' => $addons->where('status', Addon::STATUS_ERROR)->count(),
        ];
        
        return view('addons.index', compact('addons', 'stats'));
    }
}