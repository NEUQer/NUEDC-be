<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/14
 * Time: 下午6:24
 */

namespace App\Repository\Eloquent;


class SchoolRepository extends AbstractRepository
{
    function model()
    {
        return 'App\Repository\Models\School';
    }
}