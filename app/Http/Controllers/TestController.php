<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/2
 * Time: 下午9:35
 */

namespace App\Http\Controllers;


use App\Common\ValidationHelper;
use App\Services\SysAdminService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{
    private $sysAdminService;

    public function __construct(SysAdminService $sysAdminService)
    {
        $this->sysAdminService = $sysAdminService;
    }

    public function exportAllRecords(Request $request)
    {
        $condition = ValidationHelper::checkAndGet($request, [
            'contest_id' => 'integer|required',
        ]);

        $records = $this->sysAdminService->getAllRecords($condition);
//        dd($records);
        Excel::create('contest-record', function ($excel) use ($records) {
            $excel->sheet('sheet1', function ($sheet) use ($records) {
                $sheet->appendRow([
                    'id','参赛编号','队名', '所属学校名称','成员1姓名', '成员2姓名', '成员3姓名', '指导教师',
                    '联系电话', '邮件','所选题目名称','所得奖项', '现场赛相关信息']);
                foreach ($records as &$record) {
//                    dd($record);
                    $record = array_values($record->toArray());
//                    dd($record);
                    array_splice($record,11,0,$record[13]===null?'':$record[13]);
                    //去除problem_selected
                    unset($record[10]);
                    //去除偏移后的title
                    unset($record[14]);
//                    dd($record);
                    $sheet->appendRow($record);
                }
            });
        })->download('xlsx', [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }
}