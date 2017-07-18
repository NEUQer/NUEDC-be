<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/12
 * Time: 下午3:28
 */

namespace App\Services;

use App\Common\Utils;
use App\Exceptions\SchoolAdmin\SchoolTeamsNotExistedException;
use App\Exceptions\SchoolResultsNotExistedException;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Services\Contracts\SchoolAdminServiceInterface;
use Carbon\Carbon;

class SchoolAdminService implements SchoolAdminServiceInterface
{

    private $contestRecordsRepo;
    private $contestRepo;
    private $excelService;
    private $userService;

    public function __construct(ContestRecordRepository $contestRecordsRepository,
                                ContestRepository $contestRepository,
                                ExcelService $excelService,
                                UserService $userService)
    {
        $this->contestRecordsRepo = $contestRecordsRepository;
        $this->contestRepo = $contestRepository;
        $this->excelService = $excelService;
        $this->userService = $userService;
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
        return $this->userService->loginBy('login_name', $loginName, $password, $ip, $client);
    }

    function addSchoolTeam(array $schoolTeamInfo): bool
    {
        $currentTime = new Carbon();
        $schoolTeamInfo['created_at'] = $currentTime;
        $schoolTeamInfo['updated_at'] = $currentTime;
        //-1作为未选题的标识
        $schoolTeamInfo['problem_selected'] = -1;
        //中文字符标记状态
        $schoolTeamInfo['status'] = '未审核';

        $schoolTeamInfo['register_id'] = 1234;

        return $this->contestRecordsRepo->insert($schoolTeamInfo) == 1;
    }

    function getSchoolTeams(int $schoolId, int $contestId, int $page, int $size)
    {
        $count = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($count == 0) {
            throw new SchoolTeamsNotExistedException();
        }

        $teams = $this->contestRecordsRepo->paginate($page, $size, ['school_id' => $schoolId, 'contest_id' => $contestId], [
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
        ]);

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

        $teamStatus = $this->contestRecordsRepo->getBy('id', $schoolTeamId, [
            'status'
        ])->first();

        if ('未审核' == $teamStatus->status) {
            return $this->contestRecordsRepo->updateWhere(['id' => $schoolTeamId], ['status' => '已审核']) == 1;
        }

        return false;
    }

    function getSchoolResults(int $schoolId, int $contestId, int $page, int $size)
    {
        $count = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($count == 0) {
            throw new SchoolResultsNotExistedException();
        }

        $results = $this->contestRecordsRepo->paginate($page, $size, ['school_id' => $schoolId, 'contest_id' => $contestId], [
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
        ]);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    function exportSchoolTeams(int $schoolId, int $contestId)
    {
        $count = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($count == 0) {
            throw new SchoolTeamsNotExistedException();
        }

        $results = $this->contestRecordsRepo->getByMult(['school_id' => $schoolId, 'contest_id' => $contestId], [
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
        ])->all();


        $cellData = [['比赛编号', '队伍id', '队名', '学校编号', '学校名称', '学校等级', '成员1', '成员2', '成员3', '指导老师', '联系电话', '邮箱', '审核状态']];

        foreach ($results as $result) {
            $temp = array_values($result['attributes']);
            $cellData = array_merge($cellData, [$temp]);
        }

        $path = $this->excelService->export($cellData, 'data')['full'];

        return $path;
    }

    function exportSchoolResults(int $schoolId, int $contestId)
    {
        $count = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($count == 0) {
            throw new SchoolResultsNotExistedException();
        }

        $results = $this->contestRecordsRepo->getByMult(['school_id' => $schoolId, 'contest_id' => $contestId], [
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
            'result',   //获奖情况
            'result_info',  //审查状态
            'result_at',
            'onsite_info'   //现场赛信息
        ])->all();

        $cellData = [['比赛编号', '队伍id', '队名', '学校编号', '学校名称', '学校等级', '成员1', '成员2', '成员3', '指导老师', '联系电话', '邮箱',
            '所选题目', '选题时间', '获奖情况', '审查状态', '奖项确定时间', '现场赛相关信息']];

        foreach ($results as $result) {
            $temp = array_values($result['attributes']);
            $cellData = array_merge($cellData, [$temp]);
        }

        $path = $this->excelService->export($cellData, 'result')['full'];

        return $path;
    }
}

?>