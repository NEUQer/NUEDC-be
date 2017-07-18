<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/14
 * Time: 上午11:03
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestRegisterHaveNotPassException extends BaseException
{
    protected $code = 20005;

    protected $data = "Contest Sign Up Have Not Pass,Permission Denied";
}