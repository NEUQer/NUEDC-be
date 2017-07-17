<?php

namespace App\Http\Controllers;

use App\Common\ValidationHelper;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Permission\PermissionDeniedException;
use Permission;
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
     * 校管理员登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'identifier' => 'required|string',
            'password' => 'required|min:6|max:20',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $loginName = $request->get("identifier");
        $password = $request->get("password");
        $ip = $request->ip();
        $client = 1;

        $data = $this->schoolAdminService->login($loginName, $password, $ip, $client);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 创建学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function addSchoolTeam(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

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
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function updateSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }
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

        $schoolTeamData = ValidationHelper::getInputData($request, $rules);

        if ($this->schoolAdminService->updateSchoolTeam($id, $schoolTeamData)) {
            return response()->json([
                'code' => 0
            ]);
        }
    }

    /**
     * 删除学校队伍
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function deleteSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->schoolAdminService->deleteSchoolTeam($id)) {
            throw new UnknownException("fail to delete school team.");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 获取本校队伍信息（比赛前）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function getSchoolTeams(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $schoolId = $request->user['school_id'];
        $contestId = $request->get("contest_id");

        $page = $request->input('page', 1);
        $size = $request->input('size', 2);

        $data = $this->schoolAdminService->getSchoolTeams($schoolId, $contestId, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 查看本校队伍获奖情况
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     */
    public function getSchoolResults(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['view_school_results'])) {
            throw new PermissionDeniedException();
        }
        $schoolId = $request->user['school_id'];
        $contestId = $request->get('contest_id');

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        $data = $this->schoolAdminService->getSchoolResults($schoolId, $contestId, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 审核本校队伍
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function checkSchoolTeam(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->schoolAdminService->checkSchoolTeam($id)) {
            throw new UnknownException("check fail");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    /**
     * 导出学校队伍
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function exportSchoolTeams(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $schoolId = $request->user['school_id'];
        $contestId = $request->get("contest_id");

        $path = $this->schoolAdminService->exportSchoolTeams($schoolId, $contestId);

        return response()->download($path);
    }

    /**
     * 导出学校获奖记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws PermissionDeniedException
     * @throws UnknownException
     */
    public function exportSchoolResults(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $schoolId = $request->user['school_id'];
        $contestId = $request->get("contest_id");

        $path = $this->schoolAdminService->exportSchoolResults($schoolId, $contestId);

        return response()->download($path);
    }

    public function getStartedContest(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_school_teams'])) {
            throw new PermissionDeniedException();
        }

        $data = $this->schoolAdminService->getStartedContest();

        return response()->json([
            'code' => 0,
            'data' => [
                'contests' => $data
            ]
        ]);
    }
}
