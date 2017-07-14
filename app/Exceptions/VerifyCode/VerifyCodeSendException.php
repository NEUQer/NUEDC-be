<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午1:25
 */

namespace App\Exceptions\Common;


use App\Exceptions\BaseException;

class VerifyCodeSendException extends BaseException
{
    protected $code = 50002;

    protected $data = "verifyCode send failure";

}