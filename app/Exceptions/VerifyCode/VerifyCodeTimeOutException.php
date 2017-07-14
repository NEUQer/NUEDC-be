<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午2:53
 */

namespace App\Exceptions\VerifyCode;


use App\Exceptions\BaseException;

class VerifyCodeTimeOutException extends BaseException
{
    protected $code = 50003;

    protected $data = "VerifyCode Time Out !";
}