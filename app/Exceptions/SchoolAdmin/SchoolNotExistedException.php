<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/21
 * Time: 下午10:28
 */

namespace App\Exceptions\SchoolAdmin;


use App\Exceptions\BaseException;

class SchoolNotExistedException extends  BaseException
{
    protected $code = 40003;

    protected $data = "School Not Existed";
}