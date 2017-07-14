<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午3:32
 */
namespace App\Repository\Eloquent;

class ContestRecordsRepository extends AbstractRepository {

    function model()
    {
        return "App\Repository\Models\ContestRecord";
    }
}