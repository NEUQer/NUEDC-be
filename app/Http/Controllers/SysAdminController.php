<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/14
 * Time: 下午2:25
 */

namespace App\Http\Controllers;


use App\Common\Encrypt;
use App\Common\ValidationHelper;
use App\Exceptions\Auth\PasswordWrongException;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Facades\Permission;
use App\Services\SysAdminService;
use Illuminate\Http\Request;

class SysAdminController extends Controller
{
    private $sysAdminService;

    public function __construct(SysAdminService $sysAdminService)
    {
        $this->sysAdminService = $sysAdminService;
        $this->middleware('token')->except('login');
    }

    public function login(Request $request)
    {
        $data = ValidationHelper::checkAndGet($request,[
            'login_name' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->login($data['login_name'],$data['password'],$request->ip())
        ]);
    }

    public function getAllContests(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_contest'])) {
            throw new PermissionDeniedException();
        }

        $contests = $this->sysAdminService->getContests();

        return response()->json([
            'code' => 0,
            'data' => [
                'contests' => $contests
            ]
        ]);
    }

    public function createContest(Request $request)
    {
        $contest = ValidationHelper::checkAndGet($request, [
            'title' => 'required|string|max:45',
            'description' => 'required',
            'status' => 'string|max:255',
            'register_start_time' => 'required|date',
            'register_end_time' => 'required|date',
            'problem_start_time' => 'required|date',
            'problem_end_time' => 'required|date',
            'add_on' => 'string'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_contest'])) {
            throw new PermissionDeniedException();
        }

        if ($contest['status'] == null) {
            $contest['status'] = '未开始报名';
        }

        $contestId = $this->sysAdminService->createContest($contest);

        return response()->json([
            'code' => 0,
            'data' => [
                'colentest_id' => $contestId
            ]
        ]);
    }
    public function updateContest(Request $request, int $contestId)
    {
        $contest = ValidationHelper::checkAndGet($request, [
            'title' => 'string|max:45',
            'description' => 'string|max:255',
            'status' => 'max:255',
            'register_start_time' => 'date',
            'register_end_time' => 'date',
            'problem_start_time' => 'date',
            'problem_end_time' => 'date',
            'can_register' => 'integer|min:0|max:1',
            'can_select_problem' => 'integer|min:0|max:1',
            'add_on' => 'string'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_contest'])) {
            throw new PermissionDeniedException();
        }

        if ($contest['status'] == null) {
            unset($contest['status']);
        }

        if (!$this->sysAdminService->updateContest(['id' => $contestId], $contest)) {
            throw new UnknownException('fail to update contest');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteContest(Request $request, int $contestId)
    {
        ValidationHelper::validateCheck($request->all(), [
            'password' => 'required|string'
        ]);

        if (!Encrypt::check($request->password, $request->user->password)) {
            throw new PasswordWrongException();
        }

        if (!$this->sysAdminService->deleteContest(['id' => $contestId])) {
            throw new UnknownException('fail to delete contest');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    // 学校管理
    public function getSchools(Request $request)
    {
        ValidationHelper::validateCheck($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:500'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        $data = $this->sysAdminService->getSchools($page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);

    }

    public function createSchool(Request $request)
    {
        $data = ValidationHelper::checkAndGet($request,[
            'name' => 'required|string|max:100',
            'level' => 'required|string|max:45',
            'address' => 'string|max:255',
            'post_code' => 'string|max:45',
            'principal' => 'string|max:100',
            'principal_mobile' => 'string|max:45'
        ]);

        if (!Permission::checkPermission($request->user->id,['manage_schools'])) {
            throw new PermissionDeniedException();
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'school_id' => $this->sysAdminService->createSchool($data)
            ]
        ]);
    }

    public function updateSchool(Request $request, int $id)
    {
        // 因为学校的id和名称是绑定的，所以这里不允许修改name

        $data = ValidationHelper::checkAndGet($request, [
            'address' => 'string|max:255',
            'post_code' => 'string|max:45',
            'principal' => 'string|max:100',
            'principal_mobile' => 'string|max:45'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_schools'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->updateSchool($id, $data)) {
            throw new UnknownException('fail to update school');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteSchool(Request $request, int $id)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_schools'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->deleteSchool($id)) {
            throw new UnknownException('fail to delete school');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    // 校管理员

    public function getSchoolAdmins(Request $request)
    {
        ValidationHelper::validateCheck($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:500'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        if (!Permission::checkPermission($request->user->id, ['manage_school_admins'])) {
            throw new PermissionDeniedException();
        }

        $data = $this->sysAdminService->getSchoolAdmins($page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function generateSchoolAdmin(Request $request)
    {
        $input = ValidationHelper::checkAndGet($request, [
            'school_id' => 'required|integer'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_admins'])) {
            throw new PermissionDeniedException();
        }

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->generateSchoolAdmin($input['school_id'])
        ]);
    }

    public function updateUser(Request $request, int $userId)
    {
        $data = ValidationHelper::checkAndGet($request, [
            'name' => 'string|max:100|unique:users',
            'email' => 'string|max:100|unique:users',
            'mobile' => 'string|max:45|unique:users',
            'password' => 'string|max:6',
            'sex' => 'string|max:4',
            'add_on' => 'string|max:255',
            'status' => 'integer'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_users'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->updateUser($userId, $data)) {
            throw new UnknownException('fail to update user');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteUser(Request $request, int $userId)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_users'])) {
            throw new PermissionDeniedException();
        }

        return response()->json([
            'code' => 0
        ]);
    }

    // 竞赛记录管理部分

    public function getRecords(Request $request)
    {
        ValidationHelper::validateCheck($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:500',
            'contest_id' => 'integer',
            'status' => 'string|max:255',
            'result' => 'string|max:255',
            'school_id' => 'integer'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        $conditions = [];

        if ($request->input('contest_id',null) != null) {
            $conditions['contest_id'] = $request->input('contest_id');
        }

        if ($request->input('status',null) != null) {
            $conditions['status'] = $request->input('status');
        }

        if ($request->input('result',null) != null) {
            $conditions['result'] = $request->input('result');
        }

        if ($request->input('school_id',null) != null) {
            $conditions['school_id'] = $request->input('school_id');
        }

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->getRecords($page, $size, $conditions)
        ]);
    }

    public function updateRecord(Request $request, int $recordId)
    {
        $data = ValidationHelper::checkAndGet($request, [
            'team_name' => 'string|max:255',
            'school_id' => 'integer',
            'school_name' => 'string|max:100',
            'contest_id' => 'integer',
            'school_level' => 'string|max:45',
            'member1' => 'string|max:255',
            'member2' => 'string|max:255',
            'member3' => 'string|max:255',
            'teacher' => 'string|max:255',
            'contact_mobile' => 'string|max:45',
            'email' => 'string|max:100',
            'problem_selected' => 'integer',
            'status' => 'string|max:255',
            'result' => 'string|max:255',
            'result_info' => 'string|max:255',
            'onsite_info' => 'string|max:255',
            'problem_selected_at' => 'date',
            'result_at' => 'date'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->updateRecord($recordId, $data)) {
            throw new UnknownException('fail to update record');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteRecord(Request $request, int $recordId)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->deleteRecord($recordId)) {
            throw new UnknownException('fail to update record');
        }

        return response()->json([
            'code' => 0
        ]);
    }
}