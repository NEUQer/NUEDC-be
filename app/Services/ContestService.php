<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午11:26
 */

namespace App\Services;


use App\Exceptions\Contest\ContestCloseException;
use App\Exceptions\Contest\ContestNotRegisterException;
use App\Exceptions\Contest\ContestNotStartException;
use App\Exceptions\Contest\ContestRegisterHaveNotPassException;
use App\Exceptions\Contest\ContestRegisterHavePassed;
use App\Repository\Eloquent\ProblemRepository;
use App\Services\Contracts\ContestServiceInterface;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Contest\ContestNotExistException;
use App\Exceptions\Contest\ContestRegisterTimeError;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\UserRepository;
use Carbon\Carbon;


class ContestService implements ContestServiceInterface
{
    private $userRepository;
    private $tokenService;
    private $verifyCodeService;
    private $roleService;
    private $contestRecordRepo;
    private $contestRepo;
    private $problemRepo;
    public function __construct(ProblemRepository $problemRepository,ContestRepository $contestRepository,UserRepository $userRepository, ContestRecordRepository $recordRepository,TokenService $tokenService,VerifyCodeService $verifyCodeService,RoleService $roleService)
    {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        $this->verifyCodeService = $verifyCodeService;
        $this->roleService = $roleService;
        $this->contestRecordRepo = $recordRepository;
        $this->contestRepo = $contestRepository;
        $this->problemRepo = $problemRepository;
    }
    function updateSignUpContest(int $userId,array $signInfo):array
    {
        $teamInfo = [
            'register_id'=>$userId,
            'team_name'=>$signInfo['teamName'],
            'school_id'=>$signInfo['schoolId'],
            'school_name'=>$signInfo['schoolName'],
            'contest_id'=>$signInfo['contestId'],
            'school_level'=>$signInfo['schoolLevel'],
            'member1'=>$signInfo['member1'],
            'member2'=>$signInfo['member2'],
            'member3'=>$signInfo['member3'],
            'teacher'=>$signInfo['teacher'],
            'contact_mobile'=>$signInfo['mobile'],
            'email'=>$signInfo['email'],
            'status'=>'待审核'
        ];

        $Info = $this->contestRecordRepo->getByMult(['register_id'=>$userId,'contest_id'=>$teamInfo['contest_id']])->first();
        //未报过名
        if ($Info == null){

            $time = $this->contestRepo->get($teamInfo['contest_id'],['register_start_time','register_end_time']);

            if ($time == null)
                throw new ContestNotExistException();

            $now = strtotime(Carbon::now());

            if (strtotime($time['register_start_time']) > $now || $now > strtotime($time['register_end_time'])){
                throw new ContestRegisterTimeError();
            }


            if ($this->contestRecordRepo->insert($teamInfo) < 1)
                throw new UnknownException("报名失败");
        }
        //不为空说明已经报过名
        else{
            //dd($Info);
            if ($Info['status'] == "已审核"){
                throw new ContestRegisterHavePassed();
            }

            $time = $this->contestRepo->get($teamInfo['contest_id'],['register_start_time','register_end_time']);

            $now = strtotime(Carbon::now());

            if (strtotime($time['register_start_time']) > $now || $now > strtotime($time['register_end_time'])){
                throw new ContestRegisterTimeError();
            }

            if ($this->contestRecordRepo->updateWhere(['register_id'=>$userId,'contest_id'=>$teamInfo['contest_id']],$teamInfo) < 1)
                throw new UnknownException("报名更新失败");
        }

        $signInfo =  ['status' => '报名成功待审核'];

        return $signInfo;
    }

    function getAllContest()
    {
        $now = strtotime(Carbon::now());
        $collection = $this->contestRepo->all();
        $data = [];
        foreach ($collection as $value){

            if (strtotime($value['register_start_time']) < $now && $now < strtotime($value['register_end_time'])){
                $data[] = $value;
            }
        }
        return $data;
    }

    function getContestSignUpStatus(int $userId, int $contestId)
    {
        $data = $this->contestRecordRepo->getByMult(['register_id'=>$userId,'contest_id'=>$contestId])->first();

        if ($data == null)
            throw new ContestNotRegisterException();

        return $data;
    }

    function abandonContest(int $userId, int $contestId): bool
    {
        return $this->contestRecordRepo->deleteWhere(['register_id'=>$userId,'contest_id'=>$contestId]) == 1;
    }

    function getContestProblemList(int $contestId,int $operatorId): array
    {
        $info = $this->contestRecordRepo->getByMult(['contest_id'=>$contestId,'register_id'=>$operatorId,'status'=>'已审核'],['problem_selected','problem_selected_at'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();

       $time = $this->contestRepo->get($contestId,['problem_start_time']);

       $now =  strtotime(Carbon::now());

       if ($now < strtotime($time['problem_start_time']))
       {
           throw new ContestNotStartException();
       }

       $problemList = $this->problemRepo->getBy('contest_id',$contestId,['id','contest_id','title']);

       $data = ['problemList' => $problemList,'problemSelectInfo'=>['problemId' =>$info['problem_selected'],'selectTime'=>$info['problem_selected_at']]];

       return $data;
    }


    function getAllPassContestList(int $userId): array
    {
       $contestIds = $this->contestRecordRepo->getByMult(['register_id'=>$userId,'status'=>"已审核"],['contest_id']);

       return $this->contestRepo->getIn('id',$contestIds);
    }

    function getProblemDetail(int $userId, array $key)
    {
        $info = $this->contestRecordRepo->getByMult(['contest_id'=>$key['contestId'],'register_id'=>$userId,'status'=>'已审核'],['problem_selected','problem_selected_at'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();

        $time = $this->contestRepo->get($key['contestId'],['problem_start_time']);

        $now =  strtotime(Carbon::now());

        if ($now < strtotime($time['problem_start_time']))
        {
            throw new ContestNotStartException();
        }

        $problemList = $this->problemRepo->get($key['problemId']);

        $data = ['problemList' => $problemList];

        return $data;
    }

    function updateProblemSelect(int $userId, array $key)
    {
        if ($this->problemRepo->get($key['problemId'])->first() == null){

    }
        $info = $this->contestRecordRepo->getByMult(['contest_id'=>$key['contestId'],'register_id'=>$userId,'status'=>'已审核'],['problem_selected','problem_selected_at'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();


        $time = $this->contestRepo->get($key['contestId'],['problem_start_time','problem_end_time']);

        $now =  strtotime(Carbon::now());

        if ($now < strtotime($time['problem_start_time']))
            throw new ContestNotStartException();


        if ($now > strtotime($time['problem_end_time']))
            throw new ContestCloseException();


        //无论选择与否都是更新对应行数据
        return $this->contestRecordRepo->updateWhere(['contest_id'=>$key['contestId'],'register_id'=>$userId,'status'=>'已审核'],['problem_selected'=>$key['problemId'],'problem_selected_at'=>Carbon::now()]);

    }

    function getContestResult(int $userId, int $contestId)
    {
        $row = $this->contestRecordRepo->getWhereCount(['contest_id'=>$contestId,'register_id'=>$userId,'status'=>'已审核']);
        if ($row == 0)
            throw new ContestRegisterHaveNotPassException();

        return $this->contestRecordRepo->getResult($contestId,$userId);
    }
}