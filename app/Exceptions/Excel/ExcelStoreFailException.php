<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/14
 * Time: 下午3:32
 */
namespace App\Exceptions\Excel;

use App\Exceptions\BaseException;

class ExcelStoreFailException extends BaseException
{
    protected $code = 60001;
    protected $data = "excel文件储存失败";
}