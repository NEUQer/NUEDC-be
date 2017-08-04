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

    function getResultWithProblemTitle(array $condition,array $columns)
    {
        // 临时补充方法
        foreach ($columns as &$column){
            $column = 'contest_records.'.$column;
        }
        $columns[] = 'problems.title';

        $newCondition = [];
        foreach ($condition as $item => $value){
            $newCondition['contest_records.'.$item] = $value;
        }

        return $this->model
            ->where($newCondition)
            ->leftJoin('problems','contest_records.problem_selected','=','problems.id')
            ->select($columns)
            ->get();
    }

    function paginateWithProblemTitle(int $page = 1, int $size = 20, array $param = [], array $columns = ['*'], $orderBy = 'created_at', $order = 'desc')
    {
        // 临时补充方法
        foreach ($columns as &$column){
            $column = 'contest_records.'.$column;
        }
        $columns[] = 'problems.title';
        $newCondition = [];
        foreach ($param as $item => $value){
            $newCondition['contest_records.'.$item] = $value;
        }

        $orderBy = 'contest_records.'.$orderBy;

        if (!empty($param))
            return $this->model
                ->where($newCondition)
                ->leftJoin('problems','contest_records.problem_selected','=','problems.id')
                ->select($columns)
                ->orderBy($orderBy,$order)
                ->skip($size * --$page)
                ->take($size)
                ->get();
        else
            return $this->model
                ->leftJoin('problems','contest_records.problem_selected','=','problems.id')
                ->select($columns)
                ->skip($size * --$page)
                ->take($size)
                ->get();
    }

    function deleteWhereIn(string $param,array $values)
    {
        return $this->model->whereIn($param,$values)->delete();
    }

    function getResultedTeamIdsFrom(array $teamIds)
    {
        return $this->model->whereIn('id',$teamIds)->where('result_info','已审核')->get(['id']);
    }

    function getContestInfoAndSignStatus($userId)
    {
        return $this->model
            ->where('register_id',$userId)
            ->leftjoin('contests','contest_records.contest_id','=','contests.id')
            ->select('contests.*','contest_records.status as signUpStatus')->get();
    }

    function getRecentContestId($userId)
    {
        $record = $this->model
            ->where('register_id',$userId)
            ->where('status','已审核')
            ->orderBy('created_at','desc')
            ->take(1)
            ->get(['contest_id']);

        if ($record === null) {
            return -1;
        }else {
            return $record->contest_id;
        }
    }

    function getMaxTeamCode(int $contestId,string $schoolLevel)
    {
        return $this->model
            ->where('contest_id',$contestId)
            ->where('school_level',$schoolLevel)
            ->max('team_code');
    }

    function getPassedContests(int $userId)
    {
        return $this->model
            ->where('register_id',$userId)
            ->where('contest_records.status','已通过')
            ->leftJoin('contests','contests.id','=','contest_records.contest_id')
            ->select('contests.*','contest_records.team_code')
            ->get();
    }
}