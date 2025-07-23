<?php

namespace App\Addons\Dome;

use App\Plugins\BasePlugin;
use App\Plugins\HookManager;
use Illuminate\Support\Facades\Log;

/**
 * Dome 插件
 * 
 * @author test
 * @version 1.0.0
 */
class Dome extends BasePlugin
{
    /**
     * 插件名称
     */
    protected string $name = 'Dome';
    
    /**
     * 插件版本
     */
    protected string $version = '1.0.0';
    
    /**
     * 插件描述
     */
    protected string $description = '测试插件';
    
    /**
     * 插件作者
     */
    protected string $author = 'test';
    
    /**
     * 插件依赖
     */
    protected array $dependencies = [];
    
    /**
     * 插件激活时调用
     */
    public function activate(): void
    {
        Log::info('Addon activated: ' . $this->name);
        
        // 在这里添加插件激活时的逻辑
        // 例如：创建数据库表、初始化配置等
    }
    
    /**
     * 插件停用时调用
     */
    public function deactivate(): void
    {
        Log::info('Addon deactivated: ' . $this->name);
        
        // 在这里添加插件停用时的逻辑
        // 例如：清理缓存、移除临时文件等
    }
    
    /**
     * 注册钩子
     */
    public function registerHooks(HookManager $hookManager): void
    {
        // 注册动作钩子示例
        $hookManager->addAction('user.created', [$this, 'onUserCreated'], 10, 1);
        
        // 注册过滤器钩子示例
        $hookManager->addFilter('api.response.data', [$this, 'filterApiResponse'], 10, 2);
        
        // 注册自定义钩子
        $hookManager->addAction('Dome.custom_action', [$this, 'handleCustomAction']);
    }
    
    /**
     * 用户创建时的处理
     */
    public function onUserCreated($user): void
    {
        Log::info('User created event triggered', ['user_id' => $user->id]);
        
        // 在这里添加用户创建时的处理逻辑
    }
    
    /**
     * 过滤API响应数据
     */
    public function filterApiResponse($data, $request)
    {
        // 在这里添加API响应数据的过滤逻辑
        // 例如：添加额外字段、格式化数据等
        
        return $data;
    }
    
    /**
     * 处理自定义动作
     */
    public function handleCustomAction(): void
    {
        Log::info('Custom action triggered for ' . $this->name);
        
        // 在这里添加自定义动作的处理逻辑
    }
    
    /**
     * 获取插件信息
     */
    public function getInfo(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'dependencies' => $this->dependencies,
        ];
    }
}