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
            $teams = $this->contestRecordsRepo->getByMult($conditions,$columns);
            $count = count($teams);
        }else {
            $count = $this->contestRecordsRepo->getWhereCount($conditions);
            $teams = $this->contestRecordsRepo->paginate($page,$size,$conditions,$columns);
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
            $results = $this->contestRecordsRepo->getByMult($conditions,$columns);
            $count = count($results);
        }else {
            $count = $this->contestRecordsRepo->getWhereCount($conditions);
            $results = $this->contestRecordsRepo->paginate($page,$size,$conditions,$columns);
        }

        return [
            'results' => $results,
            'count' => $count
        ];
    }
}

?>