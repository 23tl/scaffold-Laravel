<?php

namespace App\Facades\Json;

use App\Modules\Json\Json as JsonModule;
use Illuminate\Support\Facades\Facade;

/**
 * Class Json
 *
 * @method static encode($obj)
 * @method static decode(string $str)
 */
class Json extends Facade
{
    public static function getFacadeAccessor()
    {
        return JsonModule::class;
    }
}
