<?php
/**
 * Created by PhpStorm.
 * Auth: mark
 * Date: 17/6/28
 * Time: 下午9:28
 */

namespace App\Common;

use Illuminate\Support\Facades\Validator;

class Utils
{
    /**
     * 毫秒级时间戳生成工具
     * 返回当前时间的13位毫秒级时间戳
     * @return float
     */
    public static function createTimeStamp(): float
    {
        list($micro, $se) = explode(' ', microtime());
        return $se * 1000 + round($micro * 1000, 0);
    }

    /**
     * 邮箱正则判断
     * @param string $email
     * @return bool
     */
    public static function isEmail(string $email): bool
    {
        $patternEmail = '/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/';
        return preg_match($patternEmail, $email) == 1;
    }

    /**
     * 手机号正则判断
     * @param string $mobile
     * @return bool
     */
    public static function isMobile(string $mobile):bool
    {
        $patternMobile = '/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/';
        return preg_match($patternMobile, $mobile) == 1;
    }
}