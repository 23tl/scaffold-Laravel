<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Addon;

/**
 * 插件列表命令
 */
class AddonListCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'addon:list {--active : 只显示激活的插件}';
    
    /**
     * 命令描述
     */
    protected $description = '显示所有插件列表';
    
    /**
     * 执行命令
     */
    public function handle()
    {
        $showActiveOnly = $this->option('active');
        
        $query = Addon::query();
        
        if ($showActiveOnly) {
            $query->where('is_active', true)->where('status', Addon::STATUS_ACTIVE);
        }
        
        $addons = $query->get();
        
        if ($addons->isEmpty()) {
            $this->info('没有找到任何插件');
            return 0;
        }
        
        $headers = ['名称', '版本', '状态', '描述', '作者'];
        $rows = [];
        
        foreach ($addons as $addon) {
            $status = $addon->isActive() ? '<fg=green>已激活</fg=green>' : '<fg=red>未激活</fg=red>';
            
            if ($addon->status === Addon::STATUS_ERROR) {
                $status = '<fg=red>错误</fg=red>';
            }
            
            $rows[] = [
                $addon->name,
                $addon->version,
                $status,
                $addon->description ?: '-',
                $addon->author ?: '-',
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info("\n总计: {$addons->count()} 个插件");
        
        if (!$showActiveOnly) {
            $activeCount = $addons->where('is_active', true)->count();
            $this->info("激活: {$activeCount} 个");
            $this->info("未激活: " . ($addons->count() - $activeCount) . " 个");
        }
        
        return 0;
    }
}