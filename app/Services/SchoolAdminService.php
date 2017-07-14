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
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Services\Contracts\SchoolAdminServiceInterface;

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

    function getStartedContent()
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
        $currentTime = Utils::createTimeStamp();
        $schoolTeamInfo['created_at'] = $currentTime;
        $schoolTeamInfo['updated_at'] = $currentTime;
        //-1作为未选题的标识
        $schoolTeamInfo['problem_selected'] = -1;
        //中文字符标记状态
        $schoolTeamInfo['status'] = '待审核';

        $schoolTeamInfo['register_id'] = 1234;

        return $this->contestRecordsRepo->insert($schoolTeamInfo) == 1;
    }

    function getSchoolTeams(int $schoolId, int $contestId)
    {
        $rows = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($rows == 0) {
            throw new SchoolTeamsNotExistedException();
        } else {
            return $this->contestRecordsRepo->getByMult(['school_id' => $schoolId, 'contest_id' => $contestId], [
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
        }
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
        $teamStatus = $this->contestRecordsRepo->getBy('id', $schoolTeamId, [
            'status'
        ])->first();

        if ('待审核' == $teamStatus->status) {
            return $this->contestRecordsRepo->updateWhere(['id' => $schoolTeamId], ['status' => '已审核']) == 1;
        }

        return false;
    }

    function getSchoolResults(int $schoolId, int $contestId)
    {
        $rows = $this->contestRecordsRepo->getWhereCount([
            'school_id' => $schoolId,
            'contest_id' => $contestId
        ]);

        if ($rows == 0) {
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
            'problem_selected',
            'problem_selected_at',
            'result',   //审查情况
            'result_info',  //获得奖项
            'result_at',
            'onsite_info'   //现场赛信息
        ]);

        return $results;
    }

    function exportSchoolTeams(int $schoolId, int $contestId)
    {
        $result = $this->contestRecordsRepo->getByMult(['school_id' => $schoolId, 'contest_id' => $contestId], [
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

        $cellData = ['比赛编号', '队伍id', '队名', '学校编号', '学校名称', '学校等级', '成员1', '成员2', '成员3', '指导老师', '联系电话', '邮箱', '审核状态'];
        array_merge($cellData, $result);

        $this->excelService->export($cellData, 'data');

        return true;
    }

    function exportSchoolResults(int $schoolId, int $contestId)
    {
        $result = $this->contestRecordsRepo->getByMult(['school_id' => $schoolId, 'contest_id' => $contestId], [
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
            'result',   //审查情况
            'result_info',  //获得奖项
            'result_at',
            'onsite_info'   //现场赛信息
        ]);

        $cellData = ['比赛编号', '队伍id', '队名', '学校编号', '学校名称', '学校等级', '成员1', '成员2', '成员3', '指导老师', '联系电话', '邮箱', '审核状态'];

        array_merge($cellData, $result);
        $this->excelService->export($cellData, 'result');

        return true;
    }
}

?>