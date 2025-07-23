<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\BusinessException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 注册API日志中间件
        $middleware->alias([
            'api.logger' => \App\Http\Middleware\ApiLogger::class,
        ]);
        
        // 为API路由组添加中间件
        $middleware->group('api', [
    
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'api.logger', // 添加API日志中间件
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 自定义异常渲染
        $exceptions->render(function (Throwable $e, Request $request) {
            // 只处理API请求
            if ($request->is('api/*') || $request->expectsJson()) {
                // 记录异常日志
                logException($e, $request);
                
                // 业务异常
                if ($e instanceof BusinessException) {
                    return $e->render($request);
                }
                
                // 验证异常
                if ($e instanceof ValidationException) {
                    return ApiResponse::validationError(
                        $e->errors(),
                        '参数验证失败'
                    );
                }
                
                // 认证异常
                if ($e instanceof AuthenticationException) {
                    return ApiResponse::unauthorized('请先登录');
                }
                
                // 授权异常
                if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    return ApiResponse::forbidden('权限不足');
                }
                
                // 模型未找到异常
                if ($e instanceof ModelNotFoundException) {
                    return ApiResponse::notFound('资源不存在');
                }
                
                // 路由未找到异常
                if ($e instanceof NotFoundHttpException) {
                    return ApiResponse::notFound('接口不存在');
                }
                
                // 请求方法不允许异常
                if ($e instanceof MethodNotAllowedHttpException) {
                    return ApiResponse::error('请求方法不允许', 405);
                }
                
                // 请求频率超限异常
                if ($e instanceof ThrottleRequestsException) {
                    return ApiResponse::error('请求过于频繁，请稍后再试', 429);
                }
                
                // CSRF Token 异常
                if ($e instanceof TokenMismatchException) {
                    return ApiResponse::error('CSRF Token 验证失败', 419);
                }
                
                // 文件上传过大异常
                if ($e instanceof PostTooLargeException) {
                    return ApiResponse::error('上传文件过大', 413);
                }
                
                // 签名验证失败异常
                if ($e instanceof InvalidSignatureException) {
                    return ApiResponse::error('签名验证失败', 403);
                }
                
                // HTTP响应异常
                if ($e instanceof HttpResponseException) {
                    return $e->getResponse();
                }
                
                // 其他异常
                $message = config('app.debug') ? $e->getMessage() : '服务器内部错误';
                return ApiResponse::error($message, 500);
            }
            
            return null; // 让Laravel处理非API请求的异常
        });
        
        // 异常报告（可选）
        $exceptions->report(function (Throwable $e) {
            // 这里可以添加自定义的异常报告逻辑
            // 比如发送到外部监控服务
        });
    })->create();

// 异常日志记录方法
function logException(Throwable $exception, Request $request): void
{

    $context = [
        'user_id' => Auth::check() ? Auth::id() : null,
        'url' => $request->fullUrl(),
        'method' => $request->method(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'trace' => $exception->getTraceAsString(),
        'Authorization' => empty($request->header('Authorization')) ? $request->input('token') : '',
    ];
    
    Log::error($exception->getMessage() ?: 'Unknown error', $context);
}
