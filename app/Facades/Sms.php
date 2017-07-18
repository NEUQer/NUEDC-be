<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/18
 * Time: 22:26
 */
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Sms extends Facade
{
    public static function getFacadeAccessor()
    {
        return "App\Service\SmsService";
    }
}