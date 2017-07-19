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
use App\Services\ExcelService;
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

    public function __construct(ExcelService $excelService, SysAdminService $sysAdminService, UserService $userService)
    {
        $this->excelService = $excelService;
        $this->sysAdminService = $sysAdminService;
        $this->userService = $userService;
    }

    public function export(Request $request)
    {
        $condition = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'integer',
            'school_id' => 'integer',
            'result' => 'string|max:255',
            'status' => 'string|max:255'
        ]);

        $records = $this->sysAdminService->getRecords(1, -1, $condition)['records'];

        Excel::create('contest-record', function ($excel) use ($records) {
            $excel->sheet('sheet1', function ($sheet) use ($records) {
                $sheet->appendRow(['队伍编号', '创建人编号', '队伍名称', '学校编号', '学校名称', '竞赛编号','学校类别', '成员1姓名', '成员2姓名', '成员3姓名', '指导教师', '联系电话', '邮件', '所选题目编号', '队伍状态', '所得奖项',
                    '评奖状态', '现场赛相关信息', '选题时间', '评奖时间', '创建时间', '更新时间']);
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

    public function import(Request $request)
    {
        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $data = $this->excelService->import($file);
        }
        dd($data);
        return response()->json([
            'code' => 0,
            'data' => $data
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

    public function getSchoolListTemplateFile(ExcelService $excelService){
        $name = "学校信息导入模板";

        $rows =[['学校名称','学校等级','学校通信地址','学校邮编','学校负责人姓名','负责人手机号']] ;

        $excelService->export($name,$rows);
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
            $this->Sms::sendSms($mobile, $message);
        }

        return response()->json([
            'code' => 0
        ]);
    }
}