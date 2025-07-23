<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    /**
     * 成功响应
     *
     * @param mixed $data 响应数据
     * @param string $message 响应消息
     * @param int $code 业务状态码
     * @param int $httpCode HTTP状态码
     * @return JsonResponse
     */
    public static function success($data = null, string $message = '操作成功', int $code = 200, int $httpCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toDateTimeString(),
        ], $httpCode);
    }

    /**
     * 失败响应
     *
     * @param string $message 错误消息
     * @param int $code 业务状态码
     * @param mixed $data 响应数据
     * @param int $httpCode HTTP状态码
     * @return JsonResponse
     */
    public static function error(string $message = '操作失败', int $code = 500, $data = null, int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toDateTimeString(),
        ], $httpCode);
    }

    /**
     * 分页响应
     *
     * @param mixed $data 分页数据
     * @param string $message 响应消息
     * @param int $code 业务状态码
     * @return JsonResponse
     */
    public static function paginate($data, string $message = '获取成功', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ]
            ],
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * 验证失败响应
     *
     * @param array $errors 验证错误信息
     * @param string $message 错误消息
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = '参数验证失败'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => 422,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'timestamp' => now()->toDateTimeString(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * 未授权响应
     *
     * @param string $message 错误消息
     * @return JsonResponse
     */
    public static function unauthorized(string $message = '未授权访问'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => 401,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * 禁止访问响应
     *
     * @param string $message 错误消息
     * @return JsonResponse
     */
    public static function forbidden(string $message = '禁止访问'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => 403,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * 资源未找到响应
     *
     * @param string $message 错误消息
     * @return JsonResponse
     */
    public static function notFound(string $message = '资源未找到'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => 404,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_NOT_FOUND);
    }
}