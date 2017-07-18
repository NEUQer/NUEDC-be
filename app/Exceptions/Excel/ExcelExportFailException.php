<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 17:05
 */
namespace App\Exceptions\Excel;

use App\Exceptions\BaseException;

class ExcelExportFailException extends BaseException
{
    protected $code = 60002;
    protected $data = "excel导出失败";
}