<?php

namespace App\Facades\Logging;

use App\Modules\Logging\Log as LogModule;
use Illuminate\Support\Facades\Facade;

/**
 * Class Log
 *
 * @method static void info(string $message, array $context = [], string $channel = 'local')
 * @method static void error(string $message, array $context = [], string $channel = 'local')
 * @method static void warning(string $message, array $context = [], string $channel = 'local')
 *
 * @see     \Illuminate\Log\Logger
 */
class Log extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LogModule::class;
    }
}
