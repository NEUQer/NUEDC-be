<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/19
 * Time: 上午1:52
 */

namespace App\Exceptions\Contest;

use App\Exceptions\BaseException;
class ProblemSelectTimeException extends BaseException
{
    protected $code = 20009;

    protected $data = "Problem select close or have not open !";
}