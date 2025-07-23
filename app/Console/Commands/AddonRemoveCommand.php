<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plugins\PluginManager;
use App\Models\Addon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * 插件删除命令
 */
class AddonRemoveCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'addon:remove {name : 插件名称} {--force : 强制删除，不询问确认}';
    
    /**
     * 命令描述
     */
    protected $description = '删除指定的插件';
    
    protected PluginManager $pluginManager;
    
    public function __construct(PluginManager $pluginManager)
    {
        parent::__construct();
        $this->pluginManager = $pluginManager;
    }
    
    /**
     * 执行命令
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $force = $this->option('force');
        
        // 检查插件是否存在
        $addon = Addon::findByName($name);
        if (!$addon) {
            $this->error("插件 '{$name}' 不存在");
            return 1;
        }
        
        // 显示插件信息
        $this->info("准备删除插件: {$name}");
        $this->table(
            ['属性', '值'],
            [
                ['名称', $addon->name],
                ['版本', $addon->version],
                ['描述', $addon->description],
                ['作者', $addon->author],
                ['状态', $addon->status_label],
                ['是否激活', $addon->is_active ? '是' : '否'],
            ]
        );
        
        // 确认删除
        if (!$force && !$this->confirm('确定要删除这个插件吗？此操作不可逆！')) {
            $this->info('删除操作已取消');
            return 0;
        }
        
        try {
            DB::beginTransaction();
            
            // 1. 如果插件已激活，先停用
            if ($addon->is_active) {
                $this->info('正在停用插件...');
                $this->pluginManager->deactivatePlugin($name);
            }
            
            // 2. 调用插件的卸载方法
            $this->info('正在执行插件卸载逻辑...');
            $loadedPlugins = $this->pluginManager->getLoadedPlugins();
            if (isset($loadedPlugins[$name])) {
                try {
                    $loadedPlugins[$name]->uninstall();
                } catch (\Exception $e) {
                    $this->warn("插件卸载方法执行失败: {$e->getMessage()}");
                }
            }
            
            // 3. 删除插件文件夹
            $addonPath = app_path('addons/' . $name);
            if (File::exists($addonPath)) {
                $this->info('正在删除插件文件...');
                File::deleteDirectory($addonPath);
            }
            
            // 4. 删除数据库记录
            $this->info('正在删除数据库记录...');
            $addon->delete();
            
            // 5. 清理相关数据（如果有的话）
            $this->cleanupAddonData($name);
            
            DB::commit();
            
            $this->info("✅ 插件 '{$name}' 删除成功！");
            Log::info("Plugin removed successfully", ['name' => $name]);
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("删除插件失败: {$e->getMessage()}");
            Log::error("Failed to remove plugin", [
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * 清理插件相关数据
     */
    protected function cleanupAddonData(string $name): void
    {
        try {
            // 清理插件可能创建的数据表
            $tableName = strtolower($name) . '_data';
            if (DB::getSchemaBuilder()->hasTable($tableName)) {
                if ($this->confirm("发现插件数据表 '{$tableName}'，是否删除？")) {
                    DB::getSchemaBuilder()->drop($tableName);
                    $this->info("数据表 '{$tableName}' 已删除");
                }
            }
            
            // 清理可能的配置缓存
            $configKey = 'addons.' . strtolower($name);
            if (config()->has($configKey)) {
                config()->forget($configKey);
            }
            
        } catch (\Exception $e) {
            $this->warn("清理插件数据时出现警告: {$e->getMessage()}");
        }
    }
}