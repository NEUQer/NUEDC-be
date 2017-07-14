<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午8:07
 */

namespace App\Repository\Eloquent;


class ContestRecordRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\ContestRecord";
    }

    function getResult(int $contestId,int $userId){
        return $this->model
            ->where('register_id',$userId)
            ->where('contest_records.contest_id',$contestId)
            ->leftjoin('contests','contest_records.contest_id','=','contests.id')
            ->leftjoin('problems','contest_records.problem_selected','=','problems.id')->
            select('contest_records.*','contests.title as contestTitle','problems.title as problemTitle')->get();
    }
}