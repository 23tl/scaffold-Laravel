<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Plugins\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * 插件管理API控制器
 */
class PluginController extends Controller
{
    protected PluginManager $pluginManager;
    
    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }
    
    /**
     * 获取所有插件列表
     */
    public function index(Request $request)
    {
        $showActiveOnly = $request->boolean('active_only', false);
        
        $plugins = $this->pluginManager->getAllPlugins();
        $result = [];
        
        foreach ($plugins as $name => $plugin) {
            $isActive = $this->pluginManager->isPluginActive($name);
            
            if ($showActiveOnly && !$isActive) {
                continue;
            }
            
            $result[] = [
                'name' => $name,
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'dependencies' => $plugin->getDependencies(),
                'active' => $isActive,
                'compatible' => $plugin->isCompatible(),
            ];
        }
        
        return ApiResponse::success($result);
    }
    
    /**
     * 获取单个插件信息
     */
    public function show(string $name)
    {
        $info = $this->pluginManager->getPluginInfo($name);
        
        if (!$info) {
            return ApiResponse::notFound('插件不存在');
        }
        
        return ApiResponse::success($info);
    }
    
    /**
     * 激活插件
     */
    public function activate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        
        $name = $request->input('name');
        
        if ($this->pluginManager->isPluginActive($name)) {
            return ApiResponse::error('插件已经是激活状态');
        }
        
        if ($this->pluginManager->activatePlugin($name)) {
            return ApiResponse::success([], "插件 '{$name}' 激活成功");
        } else {
            return ApiResponse::error("插件 '{$name}' 激活失败");
        }
    }
    
    /**
     * 停用插件
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        
        $name = $request->input('name');
        
        if (!$this->pluginManager->isPluginActive($name)) {
            return ApiResponse::error('插件已经是停用状态');
        }
        
        if ($this->pluginManager->deactivatePlugin($name)) {
            return ApiResponse::success([], "插件 '{$name}' 停用成功");
        } else {
            return ApiResponse::error("插件 '{$name}' 停用失败");
        }
    }
    
    /**
     * 批量操作插件
     */
    public function batch(Request $request)
    {
        $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
            'plugins' => 'required|array',
            'plugins.*' => 'string',
        ]);
        
        $action = $request->input('action');
        $plugins = $request->input('plugins');
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($plugins as $pluginName) {
            $success = false;
            
            if ($action === 'activate') {
                $success = $this->pluginManager->activatePlugin($pluginName);
            } elseif ($action === 'deactivate') {
                $success = $this->pluginManager->deactivatePlugin($pluginName);
            }
            
            $results[] = [
                'plugin' => $pluginName,
                'success' => $success,
                'message' => $success ? '操作成功' : '操作失败',
            ];
            
            if ($success) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        return ApiResponse::success([
            'results' => $results,
            'summary' => [
                'total' => count($plugins),
                'success' => $successCount,
                'failed' => $failCount,
            ],
        ], "批量{$action}操作完成");
    }
    
    /**
     * 获取插件统计信息
     */
    public function stats()
    {
        $allPlugins = $this->pluginManager->getAllPlugins();
        $activePlugins = $this->pluginManager->getActivePlugins();
        
        $stats = [
            'total' => count($allPlugins),
            'active' => count($activePlugins),
            'inactive' => count($allPlugins) - count($activePlugins),
            'hooks' => [
                'actions' => count($this->pluginManager->getHookManager()->getAllActions()),
                'filters' => count($this->pluginManager->getHookManager()->getAllFilters()),
            ],
        ];
        
        return ApiResponse::success($stats);
    }
    
    /**
     * 获取钩子信息
     */
    public function hooks()
    {
        $hookManager = $this->pluginManager->getHookManager();
        
        $hooks = [
            'actions' => $hookManager->getAllActions(),
            'filters' => $hookManager->getAllFilters(),
            'core_hooks' => config('plugins.core_hooks', []),
            'core_filters' => config('plugins.core_filters', []),
        ];
        
        return ApiResponse::success($hooks);
    }
    
    /**
     * 测试钩子
     */
    public function testHook(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['action', 'filter'])],
            'hook' => 'required|string',
            'data' => 'nullable',
        ]);
        
        $type = $request->input('type');
        $hook = $request->input('hook');
        $data = $request->input('data');
        
        $hookManager = $this->pluginManager->getHookManager();
        
        if ($type === 'action') {
            $hookManager->doAction($hook, $data);
            return ApiResponse::success([], "动作钩子 '{$hook}' 执行完成");
        } else {
            $result = $hookManager->applyFilters($hook, $data);
            return ApiResponse::success([
                'original' => $data,
                'filtered' => $result,
            ], "过滤器钩子 '{$hook}' 应用完成");
        }
    }
}