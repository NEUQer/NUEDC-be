<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/17
 * Time: 23:17
 */

namespace App\Exceptions\SchoolAdmin;

use App\Exceptions\BaseException;

class SchoolResultsNotExistedException extends BaseException
{
    protected $code = 40002;
    protected $data = "符合条件的学校队伍获奖信息不存在";
}