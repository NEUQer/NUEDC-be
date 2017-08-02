<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 17/8/2
 * Time: 下午3:02
 */

namespace App\Exceptions\VerifyCode;


use App\Exceptions\BaseException;

class VerifyCodeTimeErrorException extends BaseException
{
    protected $code = "50004";
    protected $data = "Two verifyCode request too close";
}