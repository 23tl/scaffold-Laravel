<?php

namespace App\Logic;

use App\Cache\BaseCache;
use App\Cache\UserCache;
use App\Facades\Json\Json;
use App\Models\User;
use JetBrains\PhpStorm\ArrayShape;

class UserLogic extends BaseLogic
{
    /**
     * 根据用户信息生成token
     */
    #[ArrayShape(['token' => 'string', 'exceptionTime' => 'int'])]
    private function setUserToken(User $user): array
    {
        $userInfo = Json::encode(array_merge(
            $user->toArray(),
            [
                'exceptionTime' => time() + BaseCache::WEEK,
            ]
        ));
        $token = md5($userInfo);
        UserCache::setUserInfo($token, $userInfo, BaseCache::WEEK);

        return [
            'token' => $token,
            'exceptionTime' => BaseCache::WEEK,
        ];
    }
}
