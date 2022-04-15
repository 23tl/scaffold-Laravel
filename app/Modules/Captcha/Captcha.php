<?php


namespace App\Modules\Captcha;

use App\Cache\CaptchaCache;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class Captcha
{

    /**
     * 生成图形验证码
     * @param int $length 验证码长度
     * @param string $charset   验证码字符集
     * @return array
     */
    #[ArrayShape(['key' => "string", 'code' => "string", 'image' => "string"])]
    public function store(int $length = 5, string $charset = 'abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'): array
    {
        $key = Str::random(15);
        $captcha = (new CaptchaBuilder(null, new PhraseBuilder($length, $charset)))->build();
        $code = (string)$captcha->getPhrase();
        CaptchaCache::setCaptchaCacheKey($key, $code, CaptchaCache::FIVE_MINUTE);
        return [
            'key' => $key,
            'code' => $code,
            'image' => $captcha->inline(),
        ];
    }
}
