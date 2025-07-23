<?php

namespace App\Providers;

use App\Plugins\PluginManager;
use App\Plugins\HookManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * 插件服务提供者 - 管理addons目录中的插件
 */
class AddonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册PluginManager为单例
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager($app->make(HookManager::class));
        });
        
        // 注册全局辅助函数
        $this->registerHelperFunctions();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            $pluginManager = $this->app->make(PluginManager::class);
            
            // 同步插件目录到数据库
            $pluginManager->syncPlugins();
            
            // 加载激活的插件
            $pluginManager->loadActivePlugins();
            
        } catch (\Exception $e) {
            Log::error('AddonServiceProvider boot error: ' . $e->getMessage());
        }
    }

    /**
     * 注册全局辅助函数
     */
    protected function registerHelperFunctions(): void
    {
        if (!function_exists('plugin_manager')) {
            /**
             * 获取插件管理器实例
             */
            function plugin_manager(): PluginManager
            {
                return app(PluginManager::class);
            }
        }
        
        if (!function_exists('is_plugin_active')) {
            /**
             * 检查插件是否激活
             */
            function is_plugin_active(string $name): bool
            {
                return plugin_manager()->isPluginActive($name);
            }
        }
        
        if (!function_exists('get_plugin_info')) {
            /**
             * 获取插件信息
             */
            function get_plugin_info(string $name): ?array
            {
                return plugin_manager()->getPluginInfo($name);
            }
        }
        
        if (!function_exists('activate_plugin')) {
            /**
             * 激活插件
             */
            function activate_plugin(string $name): bool
            {
                try {
                    return plugin_manager()->activatePlugin($name);
                } catch (\Exception $e) {
                    Log::error("Failed to activate plugin {$name}: " . $e->getMessage());
                    return false;
                }
            }
        }
        
        if (!function_exists('deactivate_plugin')) {
            /**
             * 停用插件
             */
            function deactivate_plugin(string $name): bool
            {
                try {
                    return plugin_manager()->deactivatePlugin($name);
                } catch (\Exception $e) {
                    Log::error("Failed to deactivate plugin {$name}: " . $e->getMessage());
                    return false;
                }
            }
        }
        
        if (!function_exists('get_active_plugins')) {
            /**
             * 获取所有激活的插件
             */
            function get_active_plugins(): array
            {
                return plugin_manager()->getActivePlugins();
            }
        }
        
        if (!function_exists('get_all_plugins')) {
            /**
             * 获取所有插件
             */
            function get_all_plugins(): array
            {
                return plugin_manager()->getAllPlugins();
            }
        }
    }
}