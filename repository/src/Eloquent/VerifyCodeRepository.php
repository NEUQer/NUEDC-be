<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午2:23
 */

namespace App\Repository\Eloquent;


class VerifyCodeRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\VerifyCode";
    }
}