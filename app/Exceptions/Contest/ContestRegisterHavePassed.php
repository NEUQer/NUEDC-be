<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午11:42
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestRegisterHavePassed extends BaseException
{
    protected $code = 80003;
    protected $data = "Contest SignUp Request Have Passed, Can't Modify Team Info";
}