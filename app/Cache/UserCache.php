<?php

namespace App\Cache;

use App\Facades\Json\Json;
use Illuminate\Support\Collection;

class UserCache extends BaseCache
{
    public const USER_KEY = 'user_token_%s';

    /**
     * 保存用户信息
     *
     * @param  string  $token     用户 token
     * @param  string  $userData  用户信息
     * @param  int  $ttl       缓存时间
     */
    public static function setUserInfo(string $token, string $userData, int $ttl = self::WEEK): mixed
    {
        return self::setex(self::getKey(self::USER_KEY, $token), $ttl, $userData);
    }

    /**
     * 返回用户信息
     *
     * @param  string  $token 用户 token
     */
    public static function getUserInfo(string $token): Collection
    {
        $userInfo = self::get(self::getKey(self::USER_KEY, $token));

        return collect(Json::decode($userInfo));
    }
}
