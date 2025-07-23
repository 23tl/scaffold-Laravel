<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 成功响应
     */
    protected function success($data = null, string $message = '操作成功', int $code = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * 失败响应
     */
    protected function error(string $message = '操作失败', int $code = 500, $data = null): JsonResponse
    {
        return ApiResponse::error($message, $code, $data);
    }

    /**
     * 分页响应
     */
    protected function paginate($data, string $message = '获取成功', int $code = 200): JsonResponse
    {
        return ApiResponse::paginate($data, $message, $code);
    }

    /**
     * 验证失败响应
     */
    protected function validationError(array $errors, string $message = '参数验证失败'): JsonResponse
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * 未授权响应
     */
    protected function unauthorized(string $message = '未授权访问'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * 禁止访问响应
     */
    protected function forbidden(string $message = '禁止访问'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * 资源未找到响应
     */
    protected function notFound(string $message = '资源未找到'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }
}
