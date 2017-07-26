<?php

namespace App\Services\Contracts;

interface SmsServiceInterface
{
    function sendSms(string $mobile, string $msg);

    function execResult(string $result);

    function sendVerifyCode($mobile, $randStr);

    function forgetPassword($mobile, $randStr);

}

?>