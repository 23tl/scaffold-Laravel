<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * 插件模型
 */
class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'description',
        'author',
        'dependencies',
        'main_file',
        'namespace',
        'is_active',
        'is_installed',
        'config',
        'routes',
        'status',
        'error_message',
        'installed_at',
        'activated_at',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'config' => 'array',
        'routes' => 'array',
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ERROR = 'error';
    const STATUS_INSTALLING = 'installing';
    const STATUS_UNINSTALLING = 'uninstalling';

    /**
     * 获取所有激活的插件
     */
    public static function getActiveAddons()
    {
        return static::where('is_active', true)
            ->where('status', self::STATUS_ACTIVE)
            ->get();
    }

    /**
     * 获取所有已安装的插件
     */
    public static function getInstalledAddons()
    {
        return static::where('is_installed', true)->get();
    }

    /**
     * 根据名称查找插件
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * 激活插件
     */
    public function activate(): bool
    {
        $this->update([
            'is_active' => true,
            'status' => self::STATUS_ACTIVE,
            'activated_at' => now(),
            'error_message' => null,
        ]);

        return true;
    }

    /**
     * 停用插件
     */
    public function deactivate(): bool
    {
        $this->update([
            'is_active' => false,
            'status' => self::STATUS_INACTIVE,
            'activated_at' => null,
            'error_message' => null,
        ]);

        return true;
    }

    /**
     * 设置错误状态
     */
    public function setError(string $message): void
    {
        $this->update([
            'status' => self::STATUS_ERROR,
            'error_message' => $message,
            'is_active' => false,
        ]);
    }

    /**
     * 检查插件是否激活
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === self::STATUS_ACTIVE;
    }

    /**
     * 检查插件是否已安装
     */
    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    /**
     * 获取插件完整路径
     */
    public function getFullPath(): string
    {
        return app_path('addons/' . $this->name);
    }

    /**
     * 获取插件主文件路径
     */
    public function getMainFilePath(): string
    {
        return $this->getFullPath() . '/' . $this->main_file;
    }

    /**
     * 获取插件配置
     */
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config ?? [];
        }

        return data_get($this->config, $key, $default);
    }

    /**
     * 设置插件配置
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->update(['config' => $config]);
    }

    /**
     * 获取插件路由配置
     */
    public function getRoutes(): array
    {
        return $this->routes ?? [];
    }

    /**
     * 设置插件路由配置
     */
    public function setRoutes(array $routes): void
    {
        $this->update(['routes' => $routes]);
    }

    /**
     * 获取依赖列表
     */
    public function getDependencies(): array
    {
        return $this->dependencies ?? [];
    }

    /**
     * 检查依赖是否满足
     */
    public function checkDependencies(): bool
    {
        $dependencies = $this->getDependencies();
        
        foreach ($dependencies as $dependency) {
            $dependentAddon = static::findByName($dependency);
            if (!$dependentAddon || !$dependentAddon->isActive()) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取状态标签
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                self::STATUS_ACTIVE => '已激活',
                self::STATUS_INACTIVE => '未激活',
                self::STATUS_ERROR => '错误',
                self::STATUS_INSTALLING => '安装中',
                self::STATUS_UNINSTALLING => '卸载中',
                default => '未知状态',
            }
        );
    }
}