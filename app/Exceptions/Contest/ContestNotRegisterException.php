<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/16
 * Time: 下午2:43
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestNotRegisterException extends  BaseException
{
    protected $code = 30008;
    protected $data = "this contest have not your register record";
}