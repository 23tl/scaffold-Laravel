<?php


namespace App\Modules\Json;


class Json
{
    /**
     * @param $obj
     *
     * @return false|string
     */
    public function encode($obj)
    {
        return json_encode($obj);
    }

    /**
     * @param $str
     *
     * @return array|mixed
     */
    public function decode($str)
    {
        if (empty($str)) {
            return [];
        }
        return json_decode($str, true, 512);
    }
}