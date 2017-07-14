<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/14
 * Time: 下午3:32
 */
namespace App\Exceptions;

class ExcelStoreFailException extends BaseException
{
    protected $code = 50001;
    protected $message = "excel文件储存失败";
}