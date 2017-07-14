<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午4:13
 */

namespace App\Exceptions\Common;


use App\Exceptions\BaseException;

class MobileIllegalIException extends BaseException
{
    protected $code = 10002;
    protected $data = "mobile is Illegal";
}