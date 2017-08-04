<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/4
 * Time: 下午6:12
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ProblemSubmittedException extends BaseException
{
    protected $code = 80012;
    protected $data = 'Problem submitted';
}