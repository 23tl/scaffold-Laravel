<?php

namespace App\Http\Middleware;

use App\Constants\ErrorConstant;
use App\Facades\Json\Json;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Response
{
    public int $timer;

    /**
     * 排除路径
     */
    public array $excludePaths = [];

    public function __construct()
    {
        $this->timer = time();
    }

    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $response = $next($request);
        /**
         * 排除指定路径
         */
        if ($this->excludePaths($request)) {
            return $response;
        }
        $data = [
            'code' => $response->getStatusCode(),
            'message' => 'ok',
            'time' => $this->timer,
            'dateTime' => date('Y-m-d H:i:s', $this->timer),
            'data' => new stdClass(),
        ];

        $exception = $response->exception;

        if ($exception !== null) {
            /**
             * 此处是为了处理 HTTP 响应错误
             */
            if ($exception instanceof HttpException) {
                $data['code'] = $response->getStatusCode();
                $data['message'] = ErrorConstant::HTTP_ERROR[$response->getStatusCode()] ?? HttpResponse::$statusTexts[$response->getStatusCode()];
                $response->setContent(Json::encode($data));

                return $response;
            }

            /**
             * 此处是为了处理自定义异常
             */
            if ($exception instanceof \Exception) {
                $data['code'] = $exception->getCode();
                $data['message'] = $exception->getMessage();
                $response->setContent(Json::encode($data));

                return $response;
            }
        } else {
            /**
             * 表单验证
             */
            if ((int) $response->getStatusCode() === 422) {
                $data['message'] = HttpResponse::$statusTexts[$response->getStatusCode()];
                $data['data'] = ['validators' => Json::decode($response->getContent())];
                $response->setContent(Json::encode($data));

                return $response;
            }

            $data['data'] = empty($response->original) ? new stdClass() : $response->original;
            $response->setContent(Json::encode($data));
        }

        return $response;
    }

    /**
     * 排除指定路由
     */
    private function excludePaths(Request $request): bool
    {
        foreach ($this->excludePaths as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
