<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\BaseModel
 *
 *
 * 属性声明
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
    public $guarded = ['id', 'createdTime', 'updatedTime', 'deletedTime'];

    /**
     * @var string[] 转为数组需隐藏的字段
     */
    protected $hidden = ['id', 'userId'];

    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     *  @var string|null
     */
    public const CREATED_AT = 'createdTime';

    /**
     *  @var string|null
     */
    public const UPDATED_AT = 'updatedTime';

    /**
     * @var  string|null
     */
    public const DELETED_AT = 'deletedTime';

    /**
     * @param Builder $query
     * @param string  $column
     * @param string  $direction
     *
     * @return Builder
     */
    public function scopeOrderByDefault(Builder $query, string $column = 'id', string $direction = 'desc'): Builder
    {
        return $query->orderBy($column, $direction);
    }
}
