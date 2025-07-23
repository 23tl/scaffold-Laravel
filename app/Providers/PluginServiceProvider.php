<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Plugins\HookManager;

/**
 * 插件服务提供者
 */
class PluginServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册钩子管理器为单例
        $this->app->singleton(HookManager::class, function ($app) {
            return new HookManager();
        });
        

        
        // 注册全局辅助函数
        $this->registerHelperFunctions();
    }
    
    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/../../config/plugins.php' => config_path('plugins.php'),
        ], 'plugin-config');
        

        
        // 注册核心钩子
        $this->registerCoreHooks();
    }
    

    
    /**
     * 注册核心钩子
     */
    protected function registerCoreHooks(): void
    {
        $hookManager = $this->app->make(HookManager::class);
        
        // 应用启动钩子
        $hookManager->doAction('app.booted', $this->app);
        
        // 注册请求生命周期钩子
        $this->app->booted(function () use ($hookManager) {
            // 请求开始钩子
            $hookManager->doAction('request.starting');
            
            // 注册请求结束钩子
            $this->app->terminating(function () use ($hookManager) {
                $hookManager->doAction('request.terminating');
            });
        });
    }
    
    /**
     * 注册全局辅助函数
     */
    protected function registerHelperFunctions(): void
    {

        
        if (!function_exists('hook_manager')) {
            /**
             * 获取钩子管理器实例
             */
            function hook_manager(): HookManager
            {
                return app(HookManager::class);
            }
        }
        
        if (!function_exists('do_action')) {
            /**
             * 执行动作钩子
             */
            function do_action(string $hook, ...$args): void
            {
                hook_manager()->doAction($hook, ...$args);
            }
        }
        
        if (!function_exists('apply_filters')) {
            /**
             * 应用过滤器钩子
             */
            function apply_filters(string $hook, $value, ...$args): mixed
            {
                return hook_manager()->applyFilters($hook, $value, ...$args);
            }
        }
        
        if (!function_exists('add_action')) {
            /**
             * 添加动作钩子
             */
            function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
            {
                hook_manager()->addAction($hook, $callback, $priority, $acceptedArgs);
            }
        }
        
        if (!function_exists('add_filter')) {
            /**
             * 添加过滤器钩子
             */
            function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
            {
                hook_manager()->addFilter($hook, $callback, $priority, $acceptedArgs);
            }
        }
        
        if (!function_exists('remove_action')) {
            /**
             * 移除动作钩子
             */
            function remove_action(string $hook, callable $callback, int $priority = 10): bool
            {
                return hook_manager()->removeAction($hook, $callback, $priority);
            }
        }
        
        if (!function_exists('remove_filter')) {
            /**
             * 移除过滤器钩子
             */
            function remove_filter(string $hook, callable $callback, int $priority = 10): bool
            {
                return hook_manager()->removeFilter($hook, $callback, $priority);
            }
        }
        
        if (!function_exists('has_action')) {
            /**
             * 检查是否有动作钩子
             */
            function has_action(string $hook, ?callable $callback = null): bool
            {
                return hook_manager()->hasAction($hook, $callback);
            }
        }
        
        if (!function_exists('has_filter')) {
            /**
             * 检查是否有过滤器钩子
             */
            function has_filter(string $hook, ?callable $callback = null): bool
            {
                return hook_manager()->hasFilter($hook, $callback);
            }
        }
    }
}