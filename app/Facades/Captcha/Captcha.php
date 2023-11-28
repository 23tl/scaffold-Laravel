<?php

namespace App\Facades\Captcha;

use App\Modules\Captcha\Captcha as CaptchaModule;
use Illuminate\Support\Facades\Facade;

/**
 * Class Captcha
 *
 * @method static store(int $length = 5, string $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789')
 */
class Captcha extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return CaptchaModule::class;
    }
}
