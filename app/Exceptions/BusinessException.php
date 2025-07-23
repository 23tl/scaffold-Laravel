<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

class BusinessException extends Exception
{
    /**
     * 业务状态码
     *
     * @var int
     */
    protected $businessCode;

    /**
     * HTTP状态码
     *
     * @var int
     */
    protected $httpCode;

    /**
     * 额外数据
     *
     * @var mixed
     */
    protected $data;

    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $businessCode 业务状态码
     * @param int $httpCode HTTP状态码
     * @param mixed $data 额外数据
     * @param Exception|null $previous 上一个异常
     */
    public function __construct(
        string $message = '业务处理失败',
        int $businessCode = 500,
        int $httpCode = 500,
        $data = null,
        Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->businessCode = $businessCode;
        $this->httpCode = $httpCode;
        $this->data = $data;
    }

    /**
     * 获取业务状态码
     *
     * @return int
     */
    public function getBusinessCode(): int
    {
        return $this->businessCode;
    }

    /**
     * 获取HTTP状态码
     *
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * 获取额外数据
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 渲染异常响应
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return ApiResponse::error(
            $this->getMessage(),
            $this->getBusinessCode(),
            $this->getData(),
            $this->getHttpCode()
        );
    }

    /**
     * 参数验证异常
     *
     * @param string $message
     * @param array $errors
     * @return static
     */
    public static function validationFailed(string $message = '参数验证失败', array $errors = []): self
    {
        return new static($message, 422, 422, $errors);
    }

    /**
     * 资源未找到异常
     *
     * @param string $message
     * @return static
     */
    public static function notFound(string $message = '资源未找到'): self
    {
        return new static($message, 404, 404);
    }

    /**
     * 未授权异常
     *
     * @param string $message
     * @return static
     */
    public static function unauthorized(string $message = '未授权访问'): self
    {
        return new static($message, 401, 401);
    }

    /**
     * 禁止访问异常
     *
     * @param string $message
     * @return static
     */
    public static function forbidden(string $message = '禁止访问'): self
    {
        return new static($message, 403, 403);
    }

    /**
     * 服务器内部错误异常
     *
     * @param string $message
     * @return static
     */
    public static function serverError(string $message = '服务器内部错误'): self
    {
        return new static($message, 500, 500);
    }

    /**
     * 业务逻辑错误异常
     *
     * @param string $message
     * @param int $code
     * @return static
     */
    public static function businessError(string $message, int $code = 400): self
    {
        return new static($message, $code, 400);
    }

    /**
     * 数据库操作异常
     *
     * @param string $message
     * @return static
     */
    public static function databaseError(string $message = '数据库操作失败'): self
    {
        return new static($message, 500, 500);
    }

    /**
     * 外部服务异常
     *
     * @param string $message
     * @return static
     */
    public static function externalServiceError(string $message = '外部服务调用失败'): self
    {
        return new static($message, 502, 502);
    }

    /**
     * 请求频率限制异常
     *
     * @param string $message
     * @return static
     */
    public static function rateLimitExceeded(string $message = '请求频率超限'): self
    {
        return new static($message, 429, 429);
    }

    /**
     * 资源冲突异常
     *
     * @param string $message
     * @return static
     */
    public static function conflict(string $message = '资源冲突'): self
    {
        return new static($message, 409, 409);
    }
}