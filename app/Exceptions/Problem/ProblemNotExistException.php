<?php

namespace App\Exceptions\Problem;
use App\Exceptions\BaseException;

/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/19
 * Time: 上午1:38
 */
class ProblemNotExistException extends BaseException
{
    protected $code = 90001;
    protected $data = 'Problem Not Exist';
}