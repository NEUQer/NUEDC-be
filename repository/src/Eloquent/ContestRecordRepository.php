<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午3:32
 */
namespace App\Repository\Eloquent;

class ContestRecordRepository extends AbstractRepository {

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

    function deleteWhereIn(string $param,array $values)
    {
        return $this->model->whereIn($param,$values)->delete();
    }

    function getResultedTeamIdsFrom(array $teamIds)
    {
        return $this->model->whereIn('id',$teamIds)->where('result_info','已审核')->get(['id']);
    }

    function getContestInfoAndSignStatus($userId){
        return $this->model
            ->where('register_id',$userId)
            ->leftjoin('contests','contest_records.contest_id','=','contests.id')
            ->select('contest.*','contest_records.status as signUpStatus');
    }

}