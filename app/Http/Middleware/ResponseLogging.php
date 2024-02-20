<?php

namespace App\Http\Middleware;

use App\Facades\Json\Json;
use App\Facades\Logging\Log;
use Closure;


class ResponseLogging
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $responseData = [
            'params' => $request->all(),
            'response' => Json::decode($response->getContent() ?? '{}') ?? [],
        ];

        Log::info('请求日志', $responseData, 'response');

        return $response;

    }
}
