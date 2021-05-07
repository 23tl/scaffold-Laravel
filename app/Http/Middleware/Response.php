<?php


namespace App\Http\Middleware;

use Closure;
use stdClass;
use App\Facades\Json\Json;
use Illuminate\Http\Request;
use App\Constants\ErrorConstant;
use App\Exceptions\BaseException;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Response
{
    /**
     * @var
     */
    public $timer;

    public function __construct()
    {
        $this->timer = time();
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $data = [
            'code' => $response->getStatusCode(),
            'message' => 'ok',
            'time' => $this->timer,
            'dateTime' => date('Y-m-d H:i:s', $this->timer),
            'data' => new stdClass()
        ];

        $exception = $response->exception;

        if ($exception !== null) {
            /**
             * 此处是为了处理 HTTP 响应错误
             */
            if ($exception instanceof HttpException) {
                $data['code'] = $response->getStatusCode();
                $data['message'] = ErrorConstant::HTTP_ERROR[$response->getStatusCode()] ?? HttpResponse::$statusTexts[$response->getStatusCode()];
                $response->setContent($data);
                return $response;
            }

            /**
             * 此处是为了处理自定义异常
             */
            if ($exception instanceof BaseException) {
                $data['code'] = $exception->getCode();
                $data['message'] = $exception->getMessage();
                $response->setContent($data);
                return $response;
            }
        } else {
            /**
             * 表单验证
             */
            if ((int)$response->getStatusCode() === 422) {
                $data['message'] = HttpResponse::$statusTexts[$response->getStatusCode()];
                $data['data'] = ['validators' => Json::decode($response->getContent())];
                $response->setContent(Json::encode($data));
                return $response;
            }

            $data['data'] = empty($response->original) ? new stdClass() : $response->original;
            $response->setContent($data);
        }
        return $response;
    }
}