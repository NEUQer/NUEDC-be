<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/26
 * Time: 下午5:25
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestNotResultException extends BaseException
{
    protected $code = 80010;
    protected $data = 'Contest result is not checked.';
}