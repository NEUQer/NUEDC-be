<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/18
 * Time: 下午11:32
 */

namespace App\Repository\Eloquent;


use App\Repository\Traits\InsertWithIdTrait;

class MessageRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\Message";
    }

    use InsertWithIdTrait;
}