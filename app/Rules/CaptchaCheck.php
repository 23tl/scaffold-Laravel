<?php

namespace App\Rules;

use App\Cache\CaptchaCache;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

class CaptchaCheck implements Rule, DataAwareRule
{
    /**
     * 即将进行验证的所有数据
     * @var array
     */
    protected array $data = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ((!$captcha = CaptchaCache::getCaptchaCacheKey($this->data['key'])) && ($captcha !== $value)) {
            return false;
        }

        CaptchaCache::delCaptchaCacheKey($this->data['key']);
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return '图形验证码不正确';
    }

    /**
     * 设置即将进行验证的所有数据。
     * @param array $data
     * @return $this|CaptchaCheck
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
