<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午2:24
 */

namespace App\Repository\Models;


use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model
{
    protected $table = 'verify_code';
    public $timestamps = false;
}