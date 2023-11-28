<?php

namespace App\Modules\Logging;

use Illuminate\Support\Facades\Log as LogFacades;

class Log
{
    public function info(string $message, array $context = [], string $channel = 'local'): void
    {
        $context['channel'] = $channel;
        LogFacades::info($message, $context);
    }

    public function error(string $message, array $context = [], string $channel = 'local'): void
    {
        $context['channel'] = $channel;
        LogFacades::error($message, $context);
    }

    public function warning(string $message, array $context = [], string $channel = 'local'): void
    {
        $context['channel'] = $channel;
        LogFacades::warning($message, $context);
    }
}
