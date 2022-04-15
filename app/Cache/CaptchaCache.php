<?php
/**
 * 图形验证码
 */

namespace App\Cache;


class CaptchaCache extends BaseCache
{
    // 设置图形验证码缓存key常量
    public const CAPTCHA_CACHE_KEY = 'captcha_%s';

    /**
     * 获取图形验证码缓存key
     * @param string $key
     * @return string
     */
    public static function getCaptchaCacheKey(string $key): string
    {
        return self::get(self::getKey(self::CAPTCHA_CACHE_KEY, $key));
    }

    /**
     * 设置图形验证码缓存key
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return bool
     */
    public static function setCaptchaCacheKey(string $key, string $value, int $ttl = self::FIVE_MINUTE): bool
    {
        return self::set(self::getKey(self::CAPTCHA_CACHE_KEY, $key), $value, $ttl);
    }

    /**
     * 删除图形验证码缓存key
     * @param string $key
     * @return bool
     */
    public static function delCaptchaCacheKey(string $key): bool
    {
        return self::del(self::getKey(self::CAPTCHA_CACHE_KEY, $key));
    }
}
