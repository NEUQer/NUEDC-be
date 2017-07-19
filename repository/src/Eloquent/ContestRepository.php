<?php
/**
 * Created by PhpStorm.
<<<<<<< Updated upstream
 * User: mark
 * Date: 17/7/13
 * Time: 上午11:21
=======
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午9:23
>>>>>>> Stashed changes
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

    function getMaxId():int
    {
        return $this->model->max('id');
    }
}