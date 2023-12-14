<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\BaseModel
 * 属性声明
 *
 * @method Builder|Model orderByDefault(string $column = 'id', string $direction = 'desc')
 *
 * @mixin Model
 */
class BaseModel extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @var string[] 允许查询的字段
     */
    public $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @var string[] 转为数组需隐藏的字段
     */
    protected $hidden = ['id'];

    public static function boot(): void
    {
        parent::boot();
        self::creating(static function ($model) {
            $attributes = $model->getAttributes();
            $model->uuid = empty($attributes['uuid']) ? (string) Str::ulid() : $attributes['uuid'];
        });
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y/m/d H:i:s');
    }

    public function scopeOrderByDefault(Builder $query, string $column = 'id', string $direction = 'desc'): Builder
    {
        return $query->orderBy($column, $direction);
    }
}
