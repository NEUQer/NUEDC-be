<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/14
 * Time: 上午11:55
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestCloseException extends BaseException
{
    protected $code = 80006;

    protected $data = "Contest Closed,Can't Select Problem";
}