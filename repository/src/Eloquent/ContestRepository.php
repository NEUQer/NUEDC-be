<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/13
 * Time: 上午11:21
 */

namespace App\Repository\Eloquent;


use App\Repository\Traits\InsertWithIdTrait;

class ContestRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\Contest";
    }

    use InsertWithIdTrait;
}