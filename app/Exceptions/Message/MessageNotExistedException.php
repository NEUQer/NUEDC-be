<?php

/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:55
 */
namespace App\Exceptions\Message;

use App\Exceptions\BaseException;

class MessageNotExistedException extends BaseException
{
    protected $code = 70001;

    protected $data = "信息不存在";
}