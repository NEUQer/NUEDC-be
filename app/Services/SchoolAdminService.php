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
use App\Repository\Eloquent\ContestRecordsRepository;
use App\Services\Contracts\SchoolAdminServiceInterface;

class SchoolAdminService implements SchoolAdminServiceInterface
{

    private $contestRecordsRepo;

    public function __construct(ContestRecordsRepository $contestRecordsRepository)
    {
        $this->contestRecordsRepo = $contestRecordsRepository;
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
            return $this->contestRecordsRepo->getBy('school_id', $schoolId, [
                'id',
                'team_name',
                'school_id',
                'school_name',
                'contest_id',
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
        $row = $this->contestRecordsRepo->getBy('id', $schoolTeamId);

        if ($row < 1) {
            throw new SchoolTeamsNotExistedException();
        }

        return $this->contestRecordsRepo->update($schoolTeamInfo, $schoolTeamId) == 1;
    }

    function deleteSchoolTeam(int $schoolTeamId): bool
    {
        return $this->contestRecordsRepo->deleteWhere(['id', $schoolTeamId]);
    }

    function checkSchoolTeam(int $schoolTeamId): bool
    {
        $teamStatus = $this->contestRecordsRepo->getBy('id', $schoolTeamId, [
            'status'
        ]);

        if ('待审核' == $teamStatus) {
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

        $results = $this->contestRecordsRepo->getBy('school_id', $schoolId, [
            'id',
            'team_name',
            'school_id',
            'school_name',
            'contest_id',
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
}

?>