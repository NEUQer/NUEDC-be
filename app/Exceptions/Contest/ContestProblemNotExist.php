<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/14
 * Time: 下午2:20
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestProblemNotExist extends BaseException
{
    protected $code = 30007;

    protected $data = "Problem Not Exist";
}