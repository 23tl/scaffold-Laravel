<?php

namespace App\Constants;

class ErrorConstant
{
    public const PERMISSION_ERROR = [
        'message' => '您无权访问',
        'code' => 403,
    ];

    public const HTTP_ERROR = [
        404 => '接口不存在',
    ];
}
