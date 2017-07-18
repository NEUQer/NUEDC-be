<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午7:40
 */

namespace App\Exceptions\SchoolAdmin;

use App\Exceptions\BaseException;

class SchoolTeamsNotExistedException extends BaseException
{
    protected $code = 40001;
    protected $data = "该学校符合条件的队伍不存在";
}