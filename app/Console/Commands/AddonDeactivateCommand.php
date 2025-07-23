<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugins\PluginManager;
use Illuminate\Support\Facades\Log;

/**
 * 插件停用命令
 */
class AddonDeactivateCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'addon:deactivate {name : 插件名称}';
    
    /**
     * 命令描述
     */
    protected $description = '停用指定的插件';
    
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
        $name = $this->argument('name');
        
        $this->info("正在停用插件: {$name}");
        
        try {
            if (!$this->pluginManager->isPluginActive($name)) {
                $this->warn("⚠️  插件 '{$name}' 已经是停用状态");
                return 0;
            }
            
            if ($this->pluginManager->deactivatePlugin($name)) {
                $this->info("✅ 插件 '{$name}' 停用成功");
            } else {
                $this->error("❌ 插件 '{$name}' 停用失败");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ 插件 '{$name}' 停用失败: " . $e->getMessage());
            Log::error("Plugin deactivation failed for {$name}: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}