<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午1:07
 */

namespace App\Services\Contracts;


interface VerifyCodeServiceInterface
{
    function sendVerifyCode(string $mobile,int $type):string ;

    function updateVerifyCode(string $mobile,int $type,string $code):string ;

    function checkVerifyCode(string $mobile,int $type,string $code):bool ;
}