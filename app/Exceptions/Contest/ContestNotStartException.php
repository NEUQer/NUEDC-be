<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/14
 * Time: 上午10:57
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestNotStartException extends BaseException
{
    protected $code = 20004;

    protected $data = "Contest Not Start,No Permission To see Problem";
}