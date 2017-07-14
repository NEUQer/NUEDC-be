<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午11:07
 */

namespace App\Exceptions\Contest;


use App\Exceptions\BaseException;

class ContestNotExistException extends BaseException
{
    protected $code = 30002;

    protected $data = "竞赛不存在";
}