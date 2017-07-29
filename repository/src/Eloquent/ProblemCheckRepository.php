<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/29
 * Time: 上午10:56
 */

namespace App\Repository\Eloquent;


class ProblemCheckRepository extends AbstractRepository
{
    function model()
    {
        return 'App\Repository\Models\ProblemCheck';
    }
}