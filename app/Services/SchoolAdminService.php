<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午3:28
 */

namespace App\Services;

use App\Common\Encrypt;
use App\Common\Utils;
use App\Exceptions\Auth\UserExistedException;
use App\Exceptions\Contest\ContestNotResultException;
use App\Exceptions\SchoolAdmin\SchoolTeamsNotExistedException;
use App\Facades\Permission;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\SchoolRepository;
use App\Repository\Eloquent\UserRepository;
use App\Services\Contracts\SchoolAdminServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchoolAdminService implements SchoolAdminServiceInterface
{

    private $contestRecordsRepo;
    private $contestRepo;
    private $excelService;
    private $userService;
    private $userRepo;
    private $schoolRepo;
    private $roleService;

    public function __construct(ContestRecordRepository $contestRecordsRepository,
                                ContestRepository $contestRepository,
                                ExcelService $excelService,
                                UserService $userService, UserRepository $userRepository, SchoolRepository $schoolRepository, RoleService $roleService)
    {
        $this->contestRecordsRepo = $contestRecordsRepository;
        $this->contestRepo = $contestRepository;
        $this->excelService = $excelService;
        $this->userService = $userService;
        $this->userRepo = $userRepository;
        $this->schoolRepo = $schoolRepository;
        $this->roleService = $roleService;
    }

    function getStartedContest()
    {
        $now = strtotime(Carbon::now());
        $collection = $this->contestRepo->all();
        $data = [];
        foreach ($collection as $value) {
            if (strtotime($value['register_start_time']) < $now) {
                $data[] = $value;
            }
        }
        return $data;
    }

    function login(string $loginName, string $password, string $ip, string $client)
    {
        return $this->userService->loginBy('mobile', $loginName, $password, $ip, $client);
    }

    function addSchoolTeam(array $schoolTeamInfo): bool
    {
        $tempUserId = $this->userRepo->getBy('mobile', $schoolTeamInfo['contact_mobile'], ['id'])->first();

        $flag = false;

        //当该用户已经存在时
        if ($tempUserId != null) {
            $flag = true;
            $user = [
                'id' => $tempUserId
            ];
        } else {
            $user = [
                'name' => $schoolTeamInfo['member1'],
                'mobile' => $schoolTeamInfo['contact_mobile'],
                'password' => Encrypt::encrypt("NUEDC2017"),
                'email' => $schoolTeamInfo['email'],
                'sex' => '男',
                'school_id' => $schoolTeamInfo['school_id'],
                'school_name' => $schoolTeamInfo['school_name'],
                'status' => 1
            ];
        }

        $bool = false;

        DB::transaction(function () use ($user, $schoolTeamInfo, $flag, &$bool) {
            if ($flag > 0) {
                // 用户存在
                $recordId = $this->contestRecordsRepo->getByMult([
                    'contact_mobile' => $schoolTeamInfo['contact_mobile'],
                    'contest_id' => $schoolTeamInfo['contest_id']
                ], ['id'])->first();

                if ($recordId != null) {
                    // 用户已经创建过队伍，更新其队伍信息
                    $currentTime = new Carbon();
                    $schoolTeamInfo['updated_at'] = $currentTime;
                    if ($this->contestRecordsRepo->update($schoolTeamInfo, $recordId->toArray()['id']) == 1) {
                        $bool = true;
                    }
                } else {
                    // 用户未创建队伍，新建队伍
                    $currentTime = new Carbon();
                    $schoolTeamInfo['created_at'] = $currentTime;
                    $schoolTeamInfo['updated_at'] = $currentTime;
                    //-1作为未选题的标识
                    $schoolTeamInfo['problem_selected'] = -1;
                    //中文字符标记状态
                    $schoolTeamInfo['status'] = '未审核';

                    $schoolTeamInfo['register_id'] = $flag;

                    if ($this->contestRecordsRepo->insert($schoolTeamInfo) == 1)
                        $bool = true;
                }
            } else {
                // 用户不存在
                $userId = $this->userRepo->insertWithId($user);
                $this->roleService->giveRoleTo($userId, 'student');
                $currentTime = new Carbon();
                $schoolTeamInfo['created_at'] = $currentTime;
                $schoolTeamInfo['updated_at'] = $currentTime;
                //-1作为未选题的标识
                $schoolTeamInfo['problem_selected'] = -1;
                //中文字符标记状态
                $schoolTeamInfo['status'] = '未审核';

                $schoolTeamInfo['register_id'] = $userId;

                if ($this->contestRecordsRepo->insert($schoolTeamInfo) == 1)
                    $bool = true;
            }

        });

        return $bool;
    }

    function getSchoolTeams(array $conditions, int $page, int $size)
    {
        if ($conditions['contest_id'] === -1) {
            $conditions['contest_id'] = $this->contestRepo->getMaxId();
        }

        $columns = [
            'contest_id',
            'id',
            'team_name',
            'school_id',
            'school_name',
            'school_level',
            'member1',
            'member2',
            'member3',
            'teacher',
            'contact_mobile',
            'email',
            'status'
        ];

        if ($size == -1) {
            $teams = $this->contestRecordsRepo->getByMult($conditions, $columns);
            $count = count($teams);
        } else {
            $count = $this->contestRecordsRepo->getWhereCount($conditions);
            $teams = $this->contestRecordsRepo->paginate($page, $size, $conditions, $columns);
        }

        return [
            'teams' => $teams,
            'count' => $count
        ];
    }

    function updateSchoolTeam(int $schoolTeamId, array $schoolTeamInfo): bool
    {
        $row = $this->contestRecordsRepo->getWhereCount(['id' => $schoolTeamId]);

        if ($row < 1) {
            throw new SchoolTeamsNotExistedException();
        }

        return $this->contestRecordsRepo->update($schoolTeamInfo, $schoolTeamId) == 1;
    }

    function deleteSchoolTeam(int $schoolTeamId): bool
    {
        $row = $this->contestRecordsRepo->getWhereCount(['id' => $schoolTeamId]);

        if ($row < 1) {
            throw new SchoolTeamsNotExistedException();
        }

        return $this->contestRecordsRepo->deleteWhere(['id' => $schoolTeamId]) == 1;
    }

    function checkSchoolTeam(int $schoolTeamId): bool
    {
        $row = $this->contestRecordsRepo->getWhereCount(['id' => $schoolTeamId]);

        if ($row < 1) {
            throw new SchoolTeamsNotExistedException();
        }

        return $this->contestRecordsRepo->updateWhere(['id' => $schoolTeamId], ['status' => '已审核']) == 1;
    }

    function getSchoolResults(array $conditions, int $page, int $size)
    {
        // 增加逻辑判断：比赛没出结果时会抛出异常

        $contest = $this->contestRepo->get($conditions['contest_id'], ['id', 'result_check']);

        if ($contest == null || $contest->result_check !== '已审核') {
            throw new ContestNotResultException();
        }

        $columns = [
            'contest_id',
            'id',
            'team_name',
            'school_id',
            'school_name',
            'school_level',
            'member1',
            'member2',
            'member3',
            'teacher',
            'contact_mobile',
            'email',
            'problem_selected',
            'problem_selected_at',
            'result',
            'result_info',
            'result_at',
            'onsite_info'   //现场赛信息
        ];

        if ($size == -1) {
            $results = $this->contestRecordsRepo->getByMult($conditions, $columns);
            $count = count($results);
        } else {
            $count = $this->contestRecordsRepo->getWhereCount($conditions);
            $results = $this->contestRecordsRepo->paginate($page, $size, $conditions, $columns);
        }

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    function getSchoolDetail($schoolId)
    {
        return $this->schoolRepo->get($schoolId);
    }
}


?>