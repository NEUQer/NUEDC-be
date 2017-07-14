<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午2:49
 */

namespace App\Exceptions\VerifyCode;


use App\Exceptions\BaseException;

class VerifyCodeErrorException extends BaseException
{
    protected $code = 50001;

    protected $data = "VerifyCode Error !";
}