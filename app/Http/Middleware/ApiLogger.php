<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $startTime = microtime(true);
        
        // 记录请求日志
        $this->logRequest($request);
        
        $response = $next($request);
        
        // 记录响应日志
        $this->logResponse($request, $response, $startTime);
        
        return $response;
    }

    /**
     * 记录请求日志
     *
     * @param Request $request
     */
    protected function logRequest(Request $request): void
    {
        $logData = [
            'type' => 'api_request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $this->filterHeaders($request->headers->all()),
            'query_params' => $request->query(),
            'body_params' => $this->filterSensitiveData($request->all()),
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'timestamp' => now()->toDateTimeString(),
            'Authorization' => empty($request->header('Authorization')) ? $request->input('token') : '',
        ];
        
        Log::info('API请求', $logData);
    }

    /**
     * 记录响应日志
     *
     * @param Request $request
     * @param SymfonyResponse $response
     * @param float $startTime
     */
    protected function logResponse(Request $request, SymfonyResponse $response, float $startTime): void
    {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // 毫秒
        
        $logData = [
            'type' => 'api_response',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'uri' => $request->getRequestUri(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent()),
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'timestamp' => now()->toDateTimeString(),
        ];
        
        // 根据状态码选择日志级别
        if ($response->getStatusCode() >= 500) {
            Log::error('API响应', array_merge($logData, [
                'response_content' => $this->getResponseContent($response)
            ]));
        } elseif ($response->getStatusCode() >= 400) {
            Log::warning('API响应', array_merge($logData, [
                'response_content' => $this->getResponseContent($response)
            ]));
        } else {
            Log::info('API响应', $logData);
        }
    }

    /**
     * 过滤敏感请求头
     *
     * @param array $headers
     * @return array
     */
    protected function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ];
        
        $filtered = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $filtered[$key] = ['***FILTERED***'];
            } else {
                $filtered[$key] = $value;
            }
        }
        
        return $filtered;
    }

    /**
     * 过滤敏感数据
     *
     * @param array $data
     * @return array
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'social_security_number',
        ];
        
        $filtered = $data;
        foreach ($sensitiveFields as $field) {
            if (isset($filtered[$field])) {
                $filtered[$field] = '***FILTERED***';
            }
        }
        
        return $filtered;
    }

    /**
     * 获取响应内容
     *
     * @param SymfonyResponse $response
     * @return mixed
     */
    protected function getResponseContent(SymfonyResponse $response)
    {
        $content = $response->getContent();
        
        // 尝试解析JSON响应
        if ($response->headers->get('Content-Type') === 'application/json' || 
            str_contains($response->headers->get('Content-Type', ''), 'application/json')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        // 如果响应内容太大，只记录前1000个字符
        if (strlen($content) > 1000) {
            return substr($content, 0, 1000) . '... [TRUNCATED]';
        }
        
        return $content;
    }
}