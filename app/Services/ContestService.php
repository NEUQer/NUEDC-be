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
use App\Exceptions\Contest\ContestNotResultException;
use App\Exceptions\Contest\ContestNotStartException;
use App\Exceptions\Contest\ContestProblemNotExist;
use App\Exceptions\Contest\ContestRegisterHaveNotPassException;
use App\Exceptions\Contest\ContestRegisterHavePassed;
use App\Exceptions\Contest\ProblemSubmittedException;
use App\Repository\Eloquent\ProblemCheckRepository;
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
    private $problemCheckRepo;

    public function __construct(ProblemCheckRepository $problemCheckRepository,ProblemRepository $problemRepository, ContestRepository $contestRepository, UserRepository $userRepository, ContestRecordRepository $recordRepository, TokenService $tokenService, VerifyCodeService $verifyCodeService, RoleService $roleService)
    {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        $this->verifyCodeService = $verifyCodeService;
        $this->roleService = $roleService;
        $this->contestRecordRepo = $recordRepository;
        $this->contestRepo = $contestRepository;
        $this->problemRepo = $problemRepository;
        $this->problemCheckRepo = $problemCheckRepository;
    }

    function updateSignUpContest(int $userId, array $signInfo): array
    {
        $teamInfo = [
            'register_id' => $userId,
            'team_name' => $signInfo['teamName'],
            'school_id' => $signInfo['schoolId'],
            'school_name' => $signInfo['schoolName'],
            'contest_id' => $signInfo['contestId'],
            'school_level' => $signInfo['schoolLevel'],
            'member1' => $signInfo['member1'],
            'member1_major' => $signInfo['member1Major'],
            'member1_year' => $signInfo['member1Year'],
            'member2' => $signInfo['member2'],
            'member2_major' => $signInfo['member2Major'],
            'member2_year' => $signInfo['member2Year'],
            'member3' => $signInfo['member3'],
            'member3_major' => $signInfo['member3Major'],
            'member3_year' => $signInfo['member3Year'],
            'teacher' => $signInfo['teacher'],
            'contact_mobile' => $signInfo['mobile'],
            'email' => $signInfo['email'],
            'status' => '未审核'
        ];


        $time = $this->contestRepo->get($teamInfo['contest_id'], ['can_register', 'register_start_time', 'register_end_time']);

        if ($time == null)
            throw new ContestNotExistException();

        if ($time['can_register'] == -1) {
            $now = strtotime(Carbon::now());

            if (strtotime($time['register_start_time']) > $now || $now > strtotime($time['register_end_time'])) {
                throw new ContestRegisterTimeError();
            }
        } else if ($time['can_register'] == 0) {
            throw new ContestRegisterTimeError();
        }


        $Info = $this->contestRecordRepo->getByMult(['register_id' => $userId, 'contest_id' => $teamInfo['contest_id']])->first();
        //未报过名
        if ($Info == null) {
            if ($this->contestRecordRepo->insert($teamInfo) < 1)
                throw new UnknownException("报名失败");
        } //不为空说明已经报过名
        else {

            if ($Info['status'] == "已通过") {
                throw new ContestRegisterHavePassed();
            }

            if ($this->contestRecordRepo->updateWhere(['register_id' => $userId, 'contest_id' => $teamInfo['contest_id']], $teamInfo) < 1)
                throw new UnknownException("报名更新失败");
        }

        $signInfo = ['status' => '报名成功待审核'];

        return $signInfo;
    }

    function getAllContest()
    {
        $now = strtotime(Carbon::now());
        $collection = $this->contestRepo->all();
        $data = [];
        foreach ($collection as $value) {

            if ($value['can_register'] == 1||($value['can_register']!= 0 && strtotime($value['register_start_time']) < $now && $now < strtotime($value['register_end_time']))) {
                $data[] = $value;
            }
        }
        return $data;
    }

    function getContestSignUpStatus(int $userId, int $contestId)
    {
        $data = $this->contestRecordRepo->getByMult(['register_id' => $userId, 'contest_id' => $contestId])->first();

        if ($data == null)
            throw new ContestNotRegisterException();

        return $data;
    }

    function abandonContest(int $userId, int $contestId): bool
    {
        return $this->contestRecordRepo->deleteWhere(['register_id' => $userId, 'contest_id' => $contestId]) == 1;
    }

    function getContestProblemList(int $contestId, int $operatorId): array
    {
        $info = $this->contestRecordRepo->getByMult(['contest_id' => $contestId, 'register_id' => $operatorId, 'status' => '已通过'], ['problem_selected', 'problem_selected_at'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();

        $time = $this->contestRepo->get($contestId, ['can_select_problem','problem_start_time']);

        if ($time['can_select_problem'] == -1){
            $now = strtotime(Carbon::now());

            if ($now < strtotime($time['problem_start_time'])) {
                throw new ContestNotStartException();
            }
        }elseif($time['can_select_problem'] == 0)
            throw new ContestCloseException();


        $problemList = $this->problemRepo->getBy('contest_id', $contestId);

        $data = ['problemList' => $problemList, 'problemSelectInfo' => ['problemId' => $info['problem_selected'], 'selectTime' => $info['problem_selected_at']]];

        return $data;
    }


    function getAllPassContestList(int $userId)
    {
//        $contestIds = $this->contestRecordRepo->getByMult(['register_id' => $userId, 'status' => "已通过"], ['contest_id'])->toArray();

//        $data = $this->contestRepo->getIn('id', $contestIds)->toArray();
//        $teamCodes = $this->contestRecordRepo->getTeamCodes($userId,$contestIds,['contest_id','team_code']);

        return $this->contestRecordRepo->getPassedContests($userId);
    }

    function getProblemDetail(int $userId, array $key)
    {
        $info = $this->contestRecordRepo->getByMult(['contest_id' => $key['contestId'], 'register_id' => $userId, 'status' => '已通过'], ['problem_selected', 'problem_selected_at'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();

        $time = $this->contestRepo->get($key['contestId'], ['problem_start_time']);

        $now = strtotime(Carbon::now());

        if ($now < strtotime($time['problem_start_time'])) {
            throw new ContestNotStartException();
        }

        $problemList = $this->problemRepo->get($key['problemId']);

        $data = ['problemList' => $problemList];

        return $data;
    }

    function updateProblemSelect(int $userId, array $key)
    {

        if ($this->problemRepo->get($key['problemId']) == null) {
            throw new ContestProblemNotExist();
        }

        $info = $this->contestRecordRepo->getByMult(['contest_id' => $key['contestId'], 'register_id' => $userId, 'status' => '已通过'], ['school_id','school_level','problem_selected', 'problem_selected_at','team_code','problem_submit'])->first();

        if ($info == null)
            throw new ContestRegisterHaveNotPassException();

//        $status = $this->problemCheckRepo->getByMult(['contest_id'=>$key['contestId'],'school_id'=>$info['school_id'],'status'=>'已审核'])->first();
//
//        if ($status != null)
//            throw new ContestCloseException();

        if ($info->problem_submit === '已提交') {
            throw new ProblemSubmittedException();
        }

        $time = $this->contestRepo->get($key['contestId'], ['prefix','can_select_problem', 'problem_start_time', 'problem_end_time']);

        if ($time['can_select_problem'] == -1) {
            $now = strtotime(Carbon::now());

            if ($now < strtotime($time['problem_start_time']))
                throw new ContestNotStartException();


            if ($now > strtotime($time['problem_end_time']))
                throw new ContestCloseException();

        } else if ($time['can_select_problem'] == 0) {
            throw new ContestCloseException();
        }

        // 此处根据库内数据动态生成TEAM_CODE，TEAM_CODE的两个分类依据：contest_id,school_level
        // 判定是否已经生成过team_code

        $teamCode = $info->team_code;

        if ($teamCode === null) {
            $lastCode = $this->contestRecordRepo->getMaxTeamCode($key['contestId'],$info['school_level']);
            $prefix = $time->prefix;
            if ($lastCode === null)  {
                $teamCode = $prefix.'001';
            }else {
                $lastCode = intval(substr($lastCode,1));
                $lastCode = sprintf("%03d",++$lastCode);
                $teamCode = $prefix.$lastCode;
            }
        }


        //无论选择与否都是更新对应行数据
        return $this->contestRecordRepo->updateWhere([
            'contest_id' => $key['contestId'],
            'register_id' => $userId, 'status' => '已通过'
        ], [
            'problem_selected' => $key['problemId'],
            'problem_selected_at' => Carbon::now(),
            'team_code' => $teamCode
            ]);

    }

    function getContestResult(int $userId, int $contestId)
    {
        $row = $this->contestRecordRepo->getWhereCount(['contest_id' => $contestId, 'register_id' => $userId, 'status' => '已通过']);
        if ($row == 0)
            throw new ContestRegisterHaveNotPassException();

        $contest = $this->contestRepo->get($contestId,['id','result_check']);

        if ($contest === null||$contest->result_check !== '已公布') {
            throw new ContestNotResultException();
        }

        return $this->contestRecordRepo->getResult($contestId, $userId);
    }

    function getSignedUpContest(int $userId)
    {
        return $this->contestRecordRepo->getContestInfoAndSignStatus($userId);
    }

    function getRecentContestId(int $userId):int
    {
        return $this->contestRecordRepo->getRecentContestId($userId);
    }
}