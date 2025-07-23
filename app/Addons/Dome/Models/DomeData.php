<?php

namespace App\Addons\Dome\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Dome 数据模型
 */
class DomeData extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 数据表名
     */
    protected $table = 'dome_data';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'value',
        'metadata',
        'is_active',
        'category',
        'sort_order',
    ];

    /**
     * 属性转换
     */
    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 默认属性值
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
        'metadata' => '{}',
    ];

    /**
     * 查询作用域：激活状态
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 查询作用域：按分类
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 查询作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * 根据名称查找数据
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * 设置元数据
     */
    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?: [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * 获取元数据
     */
    public function getMetadata(string $key, $default = null)
    {
        $metadata = $this->metadata ?: [];
        return $metadata[$key] ?? $default;
    }

    /**
     * 激活数据
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * 停用数据
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }
}