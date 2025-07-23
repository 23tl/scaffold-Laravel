<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugins\PluginManager;
use Illuminate\Support\Facades\Log;

/**
 * 插件同步命令
 */
class AddonSyncCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'addon:sync';
    
    /**
     * 命令描述
     */
    protected $description = '同步插件目录到数据库';
    
    protected PluginManager $pluginManager;
    
    public function __construct(PluginManager $pluginManager)
    {
        parent::__construct();
        $this->pluginManager = $pluginManager;
    }
    
    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始同步插件目录...');
        
        try {
            $this->pluginManager->syncPlugins();
            $this->info('✅ 插件目录同步成功');
        } catch (\Exception $e) {
            $this->error('❌ 插件目录同步失败: ' . $e->getMessage());
            Log::error('Plugin sync failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}