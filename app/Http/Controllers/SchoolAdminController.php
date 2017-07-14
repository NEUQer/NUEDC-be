<?php

namespace App\Http\Controllers;

use App\Common\ValidationHelper;
use App\Services\SchoolAdminService;
use Illuminate\Http\Request;

class SchoolAdminController extends Controller
{
    private $schoolAdminService;

    public function __construct(SchoolAdminService $schoolAdminService)
    {
        $this->schoolAdminService = $schoolAdminService;
    }

    /**
     * 创建学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSchoolTeam(Request $request)
    {
        $rules = [
            'team_name' => 'required|string|max:255',
            'school_id' => 'required|integer|max:11',
            'school_name' => 'required|string|max:255',
            'contest_id' => 'required|integer|max:11',
            'school_level' => 'required|string|max:45',
            'member1' => 'required|string|max:255',
            'member2' => 'required|string|max:255',
            'member3' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:45',
            'email' => 'required|email'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $schoolTeamInfo = ValidationHelper::getInputData($request, $rules);

        if ($this->schoolAdminService->addSchoolTeam($schoolTeamInfo)) {
            return response()->json([
                'code' => 0
            ]);
        }
    }

    /**
     * 更新学校队伍信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSchoolTeam(Request $request)
    {
        $rules = [
            'team_name' => 'required|string|max:255',
            'school_id' => 'required|integer|max:11',
            'school_name' => 'required|string|max:255',
            'contest_id' => 'required|integer|max:11',
            'school_level' => 'required|string|max:45',
            'member1' => 'required|string|max:255',
            'member2' => 'required|string|max:255',
            'member3' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:45',
            'email' => 'required|string|max:email'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $schoolTeamData = ValidationHelper::getInputData($request, $rules);

        $schoolTeamId = $request->get('school_team_id');

        if ($this->schoolAdminService->updateSchoolTeam($schoolTeamId, $schoolTeamData)) {
            return response()->json([
                'code' => 0
            ]);
        }
    }

    /**
     * 删除学校队伍
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSchoolTeam(Request $request)
    {
        if ($this->schoolAdminService->deleteSchoolTeam($request->get('school_team_id'))) {
            return response()->json([
                'code' => 0
            ]);
        }
    }

    /**
     * 获取本校队伍信息（比赛前）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchoolTeams(Request $request)
    {
        $schoolId = $request->user->schoolId;
        $contestId = $request->get("contestId");
        $data = $this->schoolAdminService->getSchoolTeams($schoolId, $contestId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 查看本校队伍获奖情况
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchoolResults(Request $request)
    {
        $schoolId = $request->user->schoolId;
        $contestId = $request->get('contestId');
        $data = $this->schoolAdminService->getSchoolResults($schoolId, $contestId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 审核本校队伍
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSchoolTeam(Request $request)
    {
        $schoolTeamId = $request->get("school_team_id");
        if ($this->schoolAdminService->checkSchoolTeam($schoolTeamId)) {
            return response()->json([
                'code' => 0
            ]);
        }
    }


    public function exportSchoolTeams(Request $request)
    {

    }

    public function exportSchoolResults(Request $request)
    {

    }
}
