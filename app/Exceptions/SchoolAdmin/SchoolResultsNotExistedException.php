<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 23:17
 */
namespace App\Exceptions;

class SchoolResultsNotExistedException extends BaseException
{
    protected $code = 40002;
    protected $message = "学校队伍获奖信息不存在";
}