<?php

namespace App\Plugins;

use App\Models\Addon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Plugins\Contracts\PluginInterface;
use App\Plugins\Exceptions\PluginException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * 插件管理器
 * 统一管理 addons 目录中的插件包
 */
class PluginManager
{
    /**
     * 钩子管理器
     */
    protected HookManager $hookManager;
    
    /**
     * 已加载的插件
     */
    protected array $loadedPlugins = [];
    
    /**
     * 插件目录路径
     */
    protected string $pluginsPath;
    
    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        $this->pluginsPath = config('plugins.plugin_path');
    }
    
    /**
     * 初始化插件系统
     */
    public function initialize(): void
    {
        // 加载所有激活的插件
        $this->loadActivePlugins();
        
        Log::info('Plugin system initialized');
    }
    
    /**
     * 扫描并同步插件目录中的插件到数据库
     */
    public function syncPlugins(): void
    {
        $directories = File::directories($this->pluginsPath);
        
        foreach ($directories as $directory) {
            $pluginName = basename($directory);
            $this->syncPlugin($pluginName);
        }
        
        // 清理数据库中不存在的插件
        $this->cleanupMissingPlugins();
    }
    
    /**
     * 同步单个插件
     */
    protected function syncPlugin(string $pluginName): void
    {
        $pluginPath = $this->pluginsPath . '/' . $pluginName;
        $mainFile = $pluginName . '.php';
        $mainFilePath = $pluginPath . '/' . $mainFile;
        
        if (!File::exists($mainFilePath)) {
            Log::warning("Plugin main file not found: {$mainFilePath}");
            return;
        }
        
        try {
            // 尝试加载插件类获取信息
            $className = "App\\Addons\\{$pluginName}\\{$pluginName}";
            
            if (!class_exists($className)) {
                // 尝试包含文件
                require_once $mainFilePath;
            }
            
            if (!class_exists($className)) {
                Log::warning("Plugin class not found: {$className}");
                return;
            }
            
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(PluginInterface::class)) {
                Log::warning("Plugin does not implement PluginInterface: {$className}");
                return;
            }
            
            // 创建临时实例获取插件信息
            $tempInstance = new $className($this->hookManager);
            
            // 检查数据库中是否已存在
            $addon = Addon::findByName($pluginName);
            
            $pluginData = [
                'name' => $pluginName,
                'version' => $tempInstance->getVersion(),
                'description' => $tempInstance->getDescription(),
                'author' => $tempInstance->getAuthor(),
                'dependencies' => $tempInstance->getDependencies(),
                'main_file' => $mainFile,
                'namespace' => $className,
                'is_installed' => true,
            ];
            
            if ($addon) {
                // 更新现有插件信息
                $addon->update($pluginData);
            } else {
                // 创建新插件记录
                $pluginData['installed_at'] = now();
                Addon::create($pluginData);
            }
            
        } catch (\Exception $e) {
            Log::error("Error syncing plugin {$pluginName}: " . $e->getMessage());
        }
    }
    
    /**
     * 清理数据库中不存在的插件
     */
    protected function cleanupMissingPlugins(): void
    {
        $installedPlugins = Addon::getInstalledAddons();
        
        foreach ($installedPlugins as $plugin) {
            $pluginPath = $this->pluginsPath . '/' . $plugin->name;
            if (!File::exists($pluginPath)) {
                $plugin->update([
                    'is_installed' => false,
                    'is_active' => false,
                    'status' => Addon::STATUS_INACTIVE,
                ]);
            }
        }
    }
    
    /**
     * 加载所有激活的插件
     */
    public function loadActivePlugins(): void
    {
        $activePlugins = Addon::getActiveAddons();
        
        foreach ($activePlugins as $plugin) {
            try {
                $this->loadPlugin($plugin);
            } catch (\Exception $e) {
                Log::error("Error loading plugin {$plugin->name}: " . $e->getMessage());
                $plugin->setError($e->getMessage());
            }
        }
    }
    
    /**
     * 加载单个插件
     */
    protected function loadPlugin(Addon $plugin): void
    {
        if (isset($this->loadedPlugins[$plugin->name])) {
            return;
        }
        
        $mainFilePath = $plugin->getMainFilePath();
        
        if (!File::exists($mainFilePath)) {
            throw new PluginException("Plugin main file not found: {$mainFilePath}", $plugin->name);
        }
        
        // 加载插件类
        if (!class_exists($plugin->namespace)) {
            require_once $mainFilePath;
        }
        
        if (!class_exists($plugin->namespace)) {
            throw new PluginException("Plugin class not found: {$plugin->namespace}", $plugin->name);
        }
        
        // 创建插件实例
        $pluginInstance = new $plugin->namespace($this->hookManager);
        
        if (!$pluginInstance instanceof PluginInterface) {
            throw new PluginException("Plugin must implement PluginInterface", $plugin->name);
        }
        
        // 检查兼容性
        if (!$pluginInstance->isCompatible()) {
            throw new PluginException("Plugin is not compatible with current system", $plugin->name);
        }
        
        // 检查依赖
        if (!$plugin->checkDependencies()) {
            throw new PluginException("Plugin dependencies not satisfied", $plugin->name);
        }
        
        // 注册钩子
        $pluginInstance->registerHooks($this->hookManager);
        
        // 加载插件路由
        $this->loadPluginRoutes($plugin);
        
        // 标记为已加载
        $this->loadedPlugins[$plugin->name] = $pluginInstance;
        
        Log::info("Plugin loaded successfully: {$plugin->name}");
    }
    
    /**
     * 加载插件路由
     */
    protected function loadPluginRoutes(Addon $plugin): void
    {
        $routeFile = dirname($plugin->getMainFilePath()) . '/routes.php';
        if (File::exists($routeFile)) {
            $this->registerRoute($routeFile, $plugin->name);
        }
    }
    
    /**
     * 注册路由文件
     */
    protected function registerRoute(string $routeFile, string $pluginName): void
    {
        // 注册插件路由，使用插件名称作为前缀
        Route::group([
            'prefix' => 'addon/' . strtolower($pluginName),
            'namespace' => "App\\Addons\\{$pluginName}\\Controllers",
        ], function () use ($routeFile) {
            require $routeFile;
        });
    }
    
    /**
     * 获取钩子管理器
     */
    public function getHookManager(): HookManager
    {
        return $this->hookManager;
    }
    
    /**
     * 激活插件
     */
    public function activatePlugin(string $pluginName): bool
    {
        try {
            $plugin = Addon::findByName($pluginName);
            
            if (!$plugin) {
                throw new PluginException("Plugin not found: {$pluginName}");
            }
            
            if (!$plugin->is_installed) {
                throw new PluginException("Plugin not installed: {$pluginName}");
            }
            
            if ($plugin->is_active) {
                return true; // 已经激活
            }
            
            // 加载插件实例
            $this->loadPlugin($plugin);
            
            // 调用激活方法
            if (isset($this->loadedPlugins[$pluginName])) {
                $this->loadedPlugins[$pluginName]->activate();
            }
            
            // 更新数据库状态
            $plugin->update([
                'is_active' => true,
                'status' => Addon::STATUS_ACTIVE,
                'activated_at' => now(),
                'error_message' => null,
            ]);
            
            Log::info("Plugin activated: {$pluginName}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to activate plugin {$pluginName}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 停用插件
     */
    public function deactivatePlugin(string $pluginName): bool
    {
        try {
            $plugin = Addon::findByName($pluginName);
            
            if (!$plugin) {
                throw new PluginException("Plugin not found: {$pluginName}");
            }
            
            if (!$plugin->is_active) {
                return true; // 已经停用
            }
            
            // 调用停用方法
            if (isset($this->loadedPlugins[$pluginName])) {
                $this->loadedPlugins[$pluginName]->deactivate();
                unset($this->loadedPlugins[$pluginName]);
            }
            
            // 更新数据库状态
            $plugin->update([
                'is_active' => false,
                'status' => Addon::STATUS_INACTIVE,
                'deactivated_at' => now(),
            ]);
            
            Log::info("Plugin deactivated: {$pluginName}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to deactivate plugin {$pluginName}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取所有插件
     */
    public function getAllPlugins(): array
    {
        return Addon::all()->toArray();
    }
    
    /**
     * 获取已激活的插件
     */
    public function getActivePlugins(): array
    {
        return Addon::getActiveAddons()->toArray();
    }
    
    /**
     * 获取已加载的插件实例
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }
    
    /**
     * 获取插件信息
     */
    public function getPluginInfo(string $pluginName): ?array
    {
        $plugin = Addon::findByName($pluginName);
        return $plugin ? $plugin->toArray() : null;
    }
    
    /**
     * 检查插件是否激活
     */
    public function isPluginActive(string $pluginName): bool
    {
        $plugin = Addon::findByName($pluginName);
        return $plugin ? $plugin->is_active : false;
    }
}