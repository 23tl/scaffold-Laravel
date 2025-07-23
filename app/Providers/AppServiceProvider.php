<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\AddonMakeCommand;
use App\Console\Commands\AddonActivateCommand;
use App\Console\Commands\AddonDeactivateCommand;
use App\Console\Commands\AddonListCommand;
use App\Console\Commands\AddonSyncCommand;
use App\Console\Commands\AddonRemoveCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 注册插件服务提供者
        $this->app->register(PluginServiceProvider::class);
        
        // 注册Addon服务提供者
        $this->app->register(AddonServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册 Addon 管理命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddonMakeCommand::class,
                AddonActivateCommand::class,
                AddonDeactivateCommand::class,
                AddonListCommand::class,
                AddonSyncCommand::class,
                AddonRemoveCommand::class,
            ]);
        }
    }
}
