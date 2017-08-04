<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/4
 * Time: 下午4:54
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestSubmitEndedException extends BaseException
{
    protected $code = 80011;
    protected $data = 'Contest submit time passed';
}