<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Plugins\PluginManager;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * 插件管理API控制器
 */
class AddonController extends Controller
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
        $showInstalledOnly = $request->boolean('installed_only', false);
        
        $query = Addon::query();
        
        if ($showActiveOnly) {
            $query->where('is_active', true)->where('status', Addon::STATUS_ACTIVE);
        }
        
        if ($showInstalledOnly) {
            $query->where('is_installed', true);
        }
        
        $addons = $query->get();
        $result = [];
        
        foreach ($addons as $addon) {
            $result[] = [
                'id' => $addon->id,
                'name' => $addon->name,
                'version' => $addon->version,
                'description' => $addon->description,
                'author' => $addon->author,
                'dependencies' => $addon->getDependencies(),
                'active' => $addon->isActive(),
                'installed' => $addon->isInstalled(),
                'status' => $addon->status,
                'status_label' => $addon->status_label,
                'error_message' => $addon->error_message,
                'installed_at' => $addon->installed_at,
                'activated_at' => $addon->activated_at,
                'created_at' => $addon->created_at,
                'updated_at' => $addon->updated_at,
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
     * 同步插件目录
     */
    public function sync()
    {
        try {
            $this->pluginManager->syncPlugins();
            return ApiResponse::success([], '插件目录同步成功');
        } catch (\Exception $e) {
            Log::error('Addon sync failed: ' . $e->getMessage());
            return ApiResponse::error('插件目录同步失败: ' . $e->getMessage());
        }
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
        
        try {
            if ($this->pluginManager->isPluginActive($name)) {
                return ApiResponse::error('插件已经是激活状态');
            }
            
            if ($this->pluginManager->activatePlugin($name)) {
                return ApiResponse::success([], "插件 '{$name}' 激活成功");
            } else {
                return ApiResponse::error("插件 '{$name}' 激活失败");
            }
        } catch (\Exception $e) {
            Log::error("Addon activation failed for {$name}: " . $e->getMessage());
            return ApiResponse::error("插件 '{$name}' 激活失败: " . $e->getMessage());
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
        
        try {
            if (!$this->pluginManager->isPluginActive($name)) {
                return ApiResponse::error('插件已经是停用状态');
            }
            
            if ($this->pluginManager->deactivatePlugin($name)) {
                return ApiResponse::success([], "插件 '{$name}' 停用成功");
            } else {
                return ApiResponse::error("插件 '{$name}' 停用失败");
            }
        } catch (\Exception $e) {
            Log::error("Addon deactivation failed for {$name}: " . $e->getMessage());
            return ApiResponse::error("插件 '{$name}' 停用失败: " . $e->getMessage());
        }
    }
    
    /**
     * 批量操作插件
     */
    public function batch(Request $request)
    {
        $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
            'addons' => 'required|array',
            'addons.*' => 'string',
        ]);
        
        $action = $request->input('action');
        $addons = $request->input('addons');
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($addons as $addonName) {
            $success = false;
            $message = '';
            
            try {
                if ($action === 'activate') {
                    $success = $this->pluginManager->activatePlugin($addonName);
                    $message = $success ? '激活成功' : '激活失败';
                } elseif ($action === 'deactivate') {
                    $success = $this->pluginManager->deactivatePlugin($addonName);
                    $message = $success ? '停用成功' : '停用失败';
                }
            } catch (\Exception $e) {
                $success = false;
                $message = $e->getMessage();
                Log::error("Batch {$action} failed for {$addonName}: " . $e->getMessage());
            }
            
            $results[] = [
                'addon' => $addonName,
                'success' => $success,
                'message' => $message,
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
                'total' => count($addons),
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
        $allAddons = Addon::all();
        $activeAddons = Addon::getActiveAddons();
        $installedAddons = Addon::getInstalledAddons();
        
        $stats = [
            'total' => $allAddons->count(),
            'active' => $activeAddons->count(),
            'inactive' => $allAddons->where('is_active', false)->count(),
            'installed' => $installedAddons->count(),
            'error' => $allAddons->where('status', Addon::STATUS_ERROR)->count(),
            'hooks' => [
                'actions' => count($this->pluginManager->getHookManager()->getAllActions()),
                'filters' => count($this->pluginManager->getHookManager()->getAllFilters()),
            ],
            'status_breakdown' => [
                'active' => $allAddons->where('status', Addon::STATUS_ACTIVE)->count(),
                'inactive' => $allAddons->where('status', Addon::STATUS_INACTIVE)->count(),
                'error' => $allAddons->where('status', Addon::STATUS_ERROR)->count(),
                'installing' => $allAddons->where('status', Addon::STATUS_INSTALLING)->count(),
                'uninstalling' => $allAddons->where('status', Addon::STATUS_UNINSTALLING)->count(),
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
        
        try {
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
        } catch (\Exception $e) {
            Log::error("Hook test failed: " . $e->getMessage());
            return ApiResponse::error("钩子测试失败: " . $e->getMessage());
        }
    }
    
    /**
     * 获取插件配置
     */
    public function getConfig(string $name, Request $request)
    {
        $addon = Addon::findByName($name);
        
        if (!$addon) {
            return ApiResponse::notFound('插件不存在');
        }
        
        $key = $request->input('key');
        $config = $key ? $addon->getConfig($key) : $addon->getConfig();
        
        return ApiResponse::success($config);
    }
    
    /**
     * 设置插件配置
     */
    public function setConfig(string $name, Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);
        
        $addon = Addon::findByName($name);
        
        if (!$addon) {
            return ApiResponse::notFound('插件不存在');
        }
        
        try {
            $addon->setConfig($request->input('key'), $request->input('value'));
            return ApiResponse::success([], '配置设置成功');
        } catch (\Exception $e) {
            Log::error("Config setting failed for {$name}: " . $e->getMessage());
            return ApiResponse::error('配置设置失败: ' . $e->getMessage());
        }
    }
}