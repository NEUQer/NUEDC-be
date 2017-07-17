<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/14
 * Time: 上午12:03
 */

namespace App\Repository\Eloquent;


use App\Repository\Traits\InsertWithIdTrait;

class ProblemRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\Problem";
    }

    use InsertWithIdTrait;
}