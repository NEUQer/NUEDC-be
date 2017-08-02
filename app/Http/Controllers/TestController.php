<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午7:35
 */

namespace App\Http\Controllers;

use App\Common\Encrypt;
use App\Common\Utils;
use App\Common\ValidationHelper;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Facades\Permission;
use App\Services\AuthService;
use App\Services\ContestService;
use App\Services\ExcelService;
use App\Services\PrivilegeService;
use App\Services\RoleService;
use App\Services\SchoolAdminService;
use App\Services\SysAdminService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Excel;
use Sms;

class TestController extends Controller
{
    private $excelService;

    private $sysAdminService;

    private $userService;

    private $schoolAdminService;

    public function __construct(ExcelService $excelService, SysAdminService $sysAdminService, UserService $userService, SchoolAdminService $schoolAdminService)
    {
        $this->excelService = $excelService;
        $this->sysAdminService = $sysAdminService;
        $this->userService = $userService;
        $this->schoolAdminService = $schoolAdminService;
    }

    public function export(Request $request)
    {
        $conditions = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'required|integer',
            'status' => 'string|max:255'
        ]);

        $conditions['school_id'] = 1;

        $data = $this->schoolAdminService->getSchoolTeams($conditions, 1, -1)['teams']->toArray();

        foreach ($data as &$datum) {
            unset($datum['contest_id']);
        }

        $rows = [];
        $rows[] = ['队伍编号', '队伍名称', '学校编号', '学校名称', '学校类别', '成员1姓名', '成员2姓名', '成员3姓名', '指导教师', '联系电话', '邮件', '审核状态'];

        foreach ($data as $item) {
            $rows[] = array_values($item);
        }

        $this->excelService->export('报名情况', $rows);
    }

    public function import(Request $request)
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

            if ($this->sysAdminService->createSchool($condition)) {
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

    public function test(Request $request, AuthService $service)
    {
        $userId = DB::table('users')->insertGetId([
            'login_name' => 'admin2',
            'password' => Encrypt::encrypt('123456'),
            'status' => 1,
        ]);

        $service->giveRoleTo($userId, 'system_admin');

        return $userId;
    }

    public function getSchoolListTemplateFile(ExcelService $excelService)
    {
        $name = "学校信息导入模板";

        $rows = [['学校名称', '学校等级', '学校通信地址', '学校邮编', '学校负责人姓名', '负责人手机号']];

        $excelService->export($name, $rows);
    }

    public function sendMessages(Request $request)
    {
        $params = ValidationHelper::checkAndGet($request, [
            'user_ids' => 'required|array',
            'message' => 'required|string|max:70'
        ]);


        $userIds = $params['user_ids'];
        $message = $params['message'];

        foreach ($userIds as $userId) {
            $mobile = $this->userService->getUserInfo(['id' => $userId])->toArray()['mobile'];
            //TODO: 增加验证逻辑
           // $this->Sms::sendSms($mobile, $message);
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function generate(Request $request,RoleService $service)
    {

        $service->giveRoleTo(1,'system_admin');

        return response()->json([
            'code' => 0
        ]);
    }
}