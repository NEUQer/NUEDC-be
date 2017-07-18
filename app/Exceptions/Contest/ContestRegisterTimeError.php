<?php

/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午11:02
 */

namespace App\Exceptions\Contest;

use App\Exceptions\BaseException;
class ContestRegisterTimeError extends BaseException
{
    protected $code = 20001;

    protected $data = "Contest Register close or have not open !";
}