<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignRequestId
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string)Str::uuid();
        Log::shareContext([
            'request-id' => $requestId,
        ]);
        $request->headers->set('Request-Id', $requestId);

        return $next($request)->header('Request-Id', $requestId);
    }
}
