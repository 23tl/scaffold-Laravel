<?php

if (! function_exists('generate_order_no')) {
    /**
     * 生成16位订单号码
     */
    function generate_order_no(): string
    {
        return date('Ymd').substr(
            implode(null, array_map('ord', str_split(substr(uniqid('', true), 7, 13), 1))),
            0,
            8
        );
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * 获取用户真实 IP
     *
     * @return mixed|string|null
     */
    function get_client_ip(): mixed
    {
        $ip = 'unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = array_shift($ips);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}

if (! function_exists('hide_phone')) {
    /**
     * 隐藏手机号中间四位
     *
     * @return array|string|string[]
     */
    function hide_phone($phone): array|string
    {
        return substr_replace($phone, '****', 3, 4);
    }
}

if (! function_exists('uuid')) {
    // 创建 唯一 uuid
    function uuid(): array|string
    {
        return str_replace(['.', '-', '_'], '', uniqid('', true));
    }
}
