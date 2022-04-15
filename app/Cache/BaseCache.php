<?php


namespace App\Cache;

use Illuminate\Support\Facades\Redis;

class BaseCache extends Redis
{
    // 分别定义1分钟、5分钟、1小时、1天、1周、1月、1年、永久的缓存时间
    public const MINUTE = 60;
    public const FIVE_MINUTE = 300;
    public const HOUR = 3600;
    public const DAY = 86400;
    public const WEEK = 604800;
    public const MONTH = 2592000;
    public const YEAR = 31536000;
    public const PERMANENT = -1;

    // 设置缓存前缀
    public const PREFIX = '';

    /**
     * 用于格式化每个 Key 序列
     * @param string $str
     * @param mixed ...$keys
     * @return string
     * @example self::getKey('abc_%s_%s', 1, 2)
     */
    public static function getKey(string $str, ...$keys): string
    {
        return vsprintf(static::PREFIX . $str, $keys);
    }
}
