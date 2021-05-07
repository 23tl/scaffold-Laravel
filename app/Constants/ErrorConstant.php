<?php


namespace App\Constants;


class ErrorConstant
{
    const PERMISSION_ERROR = [
        'message' => '您无权访问',
        'code' => 403
    ];

    const HTTP_ERROR = [
        404 => '接口不存在'
    ];
}