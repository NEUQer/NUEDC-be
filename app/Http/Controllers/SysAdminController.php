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
use App\Services\ExcelService;
use App\Services\SysAdminService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SysAdminController extends Controller
{
    private $sysAdminService;

    private $excelService;

    private $userService;

    public function __construct(SysAdminService $sysAdminService, ExcelService $excelService, UserService $userService)
    {
        $this->sysAdminService = $sysAdminService;
        $this->excelService = $excelService;
        $this->userService = $userService;
        $this->middleware('token')->except('login');
    }

    public function login(Request $request)
    {
        $data = ValidationHelper::checkAndGet($request, [
            'identifier' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->login($data['identifier'], $data['password'], $request->ip())
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

        if (!isset($contest['status'])) {
            $contest['status'] = '未开始报名';
        }

        $contestId = $this->sysAdminService->createContest($contest);

        return response()->json([
            'code' => 0,
            'data' => [
                'contest_id' => $contestId
            ]
        ]);
    }

    public function updateContest(Request $request, int $contestId)
    {
        $contest = ValidationHelper::checkAndGet($request, [
            'title' => 'string|max:45',
            'description' => 'string|max:255',
            'register_start_time' => 'date',
            'register_end_time' => 'date',
            'problem_start_time' => 'date',
            'problem_end_time' => 'date',
            'can_register' => 'integer|min:-1|max:1',
            'can_select_problem' => 'integer|min:-1|max:1',
            'add_on' => 'string'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_contest'])) {
            throw new PermissionDeniedException();
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

        if (!$this->sysAdminService->deleteContest($contestId)) {
            throw new UnknownException('fail to delete contest');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    // 学校管理
    public function importSchools(Request $request)
    {
        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $data = $this->excelService->import($file);
        }

        $contestRecords = $data['rows'];

        //去掉表头
        array_pull($contestRecords, 0);
        //用于保存成功与失败记录
        $success = [];
        $fail = [];

        foreach ($contestRecords as $contestRecord) {
            //根据模板填充信息
            $condition = [
                'name' => $contestRecord[0],
                'level' => $contestRecord[1],
                'address' => $contestRecord[2],
                'post_code' => $contestRecord[3],
                'principal' => $contestRecord[4],
                'principal_mobile' => $contestRecord[5]
            ];

            if ($this->sysAdminService->createSchool($condition) > 0) {
                $success[] = $contestRecord;
            } else {
                $fail[] = $contestRecord;
            }
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'success' => $success,
                'fail' => $fail
            ]
        ]);
    }

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

    public function getSchoolListTemplateFile(ExcelService $excelService)
    {
        $name = "学校信息导入模板";

        $rows = [['学校名称', '学校等级', '学校通信地址', '学校邮编', '学校负责人姓名', '负责人手机号']];

        $excelService->export($name, $rows);
    }

    public function createSchool(Request $request)
    {
        $data = ValidationHelper::checkAndGet($request, [
            'name' => 'required|string|max:100',
            'level' => 'required|string|max:45',
            'address' => 'string|max:255',
            'post_code' => 'string|max:45',
            'principal' => 'string|max:100',
            'principal_mobile' => 'string|max:45'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_schools'])) {
            throw new PermissionDeniedException();
        }

        $schoolId = $this->sysAdminService->createSchool($data);

        if ($schoolId < 1) {
            throw new UnknownException("fail to create school, maybe your school is exist");
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'school_id' => $schoolId
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
            'size' => 'integer|min:1|max:500',
            'school_id' => 'integer'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);
        $schoolId = $request->input('school_id', -1);

        if (!Permission::checkPermission($request->user->id, ['manage_school_admins'])) {
            throw new PermissionDeniedException();
        }

        $data = $this->sysAdminService->getSchoolAdmins($schoolId, $page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function addSchoolAdmin(Request $request)
    {
        $inputs = ValidationHelper::checkAndGet($request, [
            'school_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:45|unique:users',
            'email' => 'string|max:100',
            'password' => 'required|string|min:6',
            'sex' => 'string|max:4',
            'add_on' => 'string|max:255'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_admins'])) {
            throw new PermissionDeniedException();
        }

        $userId = $this->sysAdminService->createSchoolAdmins($inputs);

        if ($userId === -1) {
            throw new UnknownException('Fail to add school admin');
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'user_id' => $userId
            ]
        ]);

    }

    public function generateSchoolAdmin(Request $request)
    {
        $input = ValidationHelper::checkAndGet($request, [
            'school_ids' => 'required|array'
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_school_admins'])) {
            throw new PermissionDeniedException();
        }

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->generateSchoolAdmin($input['school_ids'])
        ]);
    }

    public function updateUser(Request $request, int $userId)
    {
        $data = ValidationHelper::checkAndGet($request, [
            'name' => 'string|max:100',
            'email' => 'string|max:100',
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

        if (!$this->sysAdminService->deleteUser($userId)) {
            throw new UnknownException('fail to delete user');
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

        $conditions['contest_id'] = $request->input('contest_id', -1);

        if ($request->input('status', null) != null) {
            $conditions['status'] = $request->input('status');
        }

        if ($request->input('result', null) != null) {
            $conditions['result'] = $request->input('result');
        }

        if ($request->input('school_id', null) != null) {
            $conditions['school_id'] = $request->input('school_id');
        }

        return response()->json([
            'code' => 0,
            'data' => $this->sysAdminService->getRecords($page, $size, $conditions)
        ]);
    }

    public function updateRecord(Request $request)
    {
        // 究极表单验证！

        $inputs = ValidationHelper::checkAndGet($request, [
            'updates' => 'array|required'
        ])['updates'];

        $rules = [
            'record_id' => 'required|integer',
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
        ];

        foreach ($inputs as $update) {
            ValidationHelper::validateCheck($update, $rules);
        }

        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->updateRecord($inputs)) {
            throw new UnknownException('fail to update record');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteRecord(Request $request)
    {
        $recordIds = ValidationHelper::checkAndGet($request, [
            'record_ids' => 'required|array'
        ])['record_ids'];

        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->deleteRecord($recordIds)) {
            throw new UnknownException('fail to update record');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function exportRecord(Request $request)
    {
        $condition = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'integer|required',
        ]);

        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        $records = $this->sysAdminService->getResults($condition);


        Excel::create('contest-record', function ($excel) use ($records) {
            $excel->sheet('sheet1', function ($sheet) use ($records) {
                $sheet->appendRow([
                    '队伍编号', '队伍名称', '学校名称', '成员1姓名', '成员2姓名', '成员3姓名', '指导教师',
                    '联系电话', '邮件', '所选题目编号', '所得奖项', '评奖状态', '现场赛相关信息']);
                foreach ($records as $record) {
                    $sheet->appendRow(array_values($record->toArray()));
                }
            });
        })->download('xlsx', [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }

    public function importRecord(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        if ($request->isMethod('post')) {
            $file = $request->file('data');
            $data = $this->excelService->import($file);
        }

        $contestRecords = $data['rows'];
        //去除表头
        array_pull($contestRecords, 0);

        // 获取所有已审核的
        $checkedIds = [];

        foreach ($contestRecords as $record) {
            $checkedIds[] = intval($record[0]);
        }

        $checkedIds = $this->sysAdminService->getResultedTeamIdsFrom($checkedIds);

        //创建数组用于保存记录
        $success = [];
        $fail = [];

        $current = Carbon::now();

        foreach ($contestRecords as $contestRecord) {
            $condition = [
                'record_id' => $contestRecord[0],
                'result' => $contestRecord[10],
                'result_info' => $contestRecord[11],
            ];

            if (!isset($checkedIds[$contestRecord[0]]) && $contestRecord[11] == '已审核') {
                $condition['result_at'] = $current;
            }

            //适应updateRecord接口
            $updates = [$condition];

            if (!$this->sysAdminService->updateRecord($updates)) {
                $fail[] = $condition['record_id'];
            } else {
                $success[] = $condition['record_id'];
            }
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'success' => $success,
                'fail' => $fail
            ]
        ]);
    }

    public function updateResults(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['manage_all_teams'])) {
            throw new PermissionDeniedException();
        }

        $rules = [
            'results' => 'required|array'
        ];

        $condition = ValidationHelper::checkAndGet($request, $rules);

        if (!$this->sysAdminService->updateResults($condition['results'])) {
            throw new UnknownException("fail to update results");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    //短信群发

    public function sendMessages(Request $request)
    {
        if (!Permission::checkPermission($request->user->id, ['send_message'])) {
            throw new PermissionDeniedException();
        }

        $params = ValidationHelper::checkAndGet($request, [
            'user_ids' => 'required|array',
            'message' => 'required|string|max:70'
        ]);

        $userIds = $params['user_ids'];
        $message = $params['message'];


        $fail = [];
        $success = [];
        foreach ($userIds as $userId) {
            $mobile = $this->userService->getUserInfo(['id' => $userId])->toArray()['mobile'];
            //TODO: 根据sendSms的返回码做状态判断，用fail和success数组保存
            if (Sms::sendSms($mobile, $message)[1] != '0') {
                $fail[] = $mobile;
            } else {
                $success[] = $mobile;
            }
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'success' => $success,
                'fail' => $fail
            ]
        ]);
    }

    public function checkContestResult(Request $request, int $contestId)
    {
        $input = ValidationHelper::getInputData($request, [
            'password' => 'required|string',
            'result_check' => 'required|string' // 已审核，未审核
        ]);

        // 检查密码

        if (!Encrypt::check($input['password'], $request->user->password)) {
            throw new PasswordWrongException();
        }

        if (!Permission::checkPermission($request->user->id, ['manage_contest'])) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->checkContestResult($contestId, $input['result_check'])) {
            throw new UnknownException("无法审核竞赛结果");
        }

        return response()->json([
            'code' => 0
        ]);
    }
}