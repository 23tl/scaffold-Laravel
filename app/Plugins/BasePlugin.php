<?php

namespace App\Plugins;

use App\Plugins\Contracts\PluginInterface;
use App\Plugins\HookManager;
use Illuminate\Support\Facades\Log;

/**
 * 基础插件抽象类
 * 提供插件的通用实现
 */
abstract class BasePlugin implements PluginInterface
{
    /**
     * 插件名称
     */
    protected string $name;
    
    /**
     * 插件版本
     */
    protected string $version = '1.0.0';
    
    /**
     * 插件描述
     */
    protected string $description = '';
    
    /**
     * 插件作者
     */
    protected string $author = '';
    
    /**
     * 插件依赖
     */
    protected array $dependencies = [];
    
    /**
     * 钩子管理器
     */
    protected ?HookManager $hookManager = null;
    
    /**
     * 构造函数
     */
    public function __construct(HookManager $hookManager = null)
    {
        $this->name = $this->name ?? class_basename(static::class);
        $this->hookManager = $hookManager;
    }
    
    /**
     * 插件激活时调用
     */
    public function activate(): void
    {
        Log::info("Plugin {$this->getName()} activated");
        
        // 子类可以重写此方法来实现自定义激活逻辑
        $this->onActivate();
    }
    
    /**
     * 插件停用时调用
     */
    public function deactivate(): void
    {
        Log::info("Plugin {$this->getName()} deactivated");
        
        // 子类可以重写此方法来实现自定义停用逻辑
        $this->onDeactivate();
    }
    
    /**
     * 注册钩子
     */
    public function registerHooks(HookManager $hookManager): void
    {
        $this->hookManager = $hookManager;
        
        // 子类可以重写此方法来注册自定义钩子
        $this->onRegisterHooks($hookManager);
    }
    
    /**
     * 获取插件名称
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * 获取插件版本
     */
    public function getVersion(): string
    {
        return $this->version;
    }
    
    /**
     * 获取插件描述
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * 获取插件作者
     */
    public function getAuthor(): string
    {
        return $this->author;
    }
    
    /**
     * 获取插件依赖
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
    
    /**
     * 检查插件是否兼容当前系统
     */
    public function isCompatible(): bool
    {
        // 默认兼容，子类可以重写此方法来实现自定义兼容性检查
        return $this->checkCompatibility();
    }
    
    /**
     * 添加动作钩子
     */
    protected function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if ($this->hookManager) {
            $this->hookManager->addAction($hook, $callback, $priority, $acceptedArgs);
        }
    }
    
    /**
     * 添加过滤器钩子
     */
    protected function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if ($this->hookManager) {
            $this->hookManager->addFilter($hook, $callback, $priority, $acceptedArgs);
        }
    }
    
    /**
     * 执行动作钩子
     */
    protected function doAction(string $hook, ...$args): void
    {
        if ($this->hookManager) {
            $this->hookManager->doAction($hook, ...$args);
        }
    }
    
    /**
     * 应用过滤器钩子
     */
    protected function applyFilters(string $hook, $value, ...$args): mixed
    {
        if ($this->hookManager) {
            return $this->hookManager->applyFilters($hook, $value, ...$args);
        }
        
        return $value;
    }
    
    /**
     * 获取插件配置
     */
    protected function getConfig(string $key, $default = null)
    {
        return config("plugins.{$this->getName()}.{$key}", $default);
    }
    
    /**
     * 设置插件配置
     */
    protected function setConfig(string $key, $value): void
    {
        config(["plugins.{$this->getName()}.{$key}" => $value]);
    }
    
    /**
     * 获取插件路径
     */
    protected function getPluginPath(): string
    {
        return app_path('Plugins/' . $this->getName());
    }
    
    /**
     * 获取插件URL
     */
    protected function getPluginUrl(): string
    {
        return url('plugins/' . strtolower($this->getName()));
    }
    
    /**
     * 记录插件日志
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $context['plugin'] = $this->getName();
        Log::log($level, "[Plugin {$this->getName()}] {$message}", $context);
    }
    
    /**
     * 子类可重写：激活时的自定义逻辑
     */
    protected function onActivate(): void
    {
        // 子类可以重写此方法
    }
    
    /**
     * 子类可重写：停用时的自定义逻辑
     */
    protected function onDeactivate(): void
    {
        // 子类可以重写此方法
    }
    
    /**
     * 子类可重写：注册钩子时的自定义逻辑
     */
    protected function onRegisterHooks(HookManager $hookManager): void
    {
        // 子类可以重写此方法
    }
    
    /**
     * 子类可重写：兼容性检查
     */
    protected function checkCompatibility(): bool
    {
        // 默认兼容，子类可以重写此方法
        return true;
    }
}