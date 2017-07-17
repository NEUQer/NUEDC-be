<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 17:05
 */
namespace App\Exceptions;

class ExcelExportFailException extends BaseException
{
    protected $code = 50002;
    protected $message = "excel导出失败";
}