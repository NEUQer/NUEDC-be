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
use App\Services\AuthService;
use App\Services\ExcelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    private $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function export(Request $request)
    {
        $fileName = "测试文件";
        $cellData = [
            ['学号', '班级', '姓名'],
            ['2152308', '21523', '罗宏涛'],
            ['2152311', '21523', '黄文锋']
        ];
        $path = $this->excelService->export($cellData, $fileName)['full'];

        return response()->download($path);
    }

    public function import(Request $request)
    {
        if ($request->isMethod('post')) {
            $file = $request->file('file');
            $data = $this->excelService->import($file);
        }
        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function test(Request $request,AuthService $service)
    {
       $userId =  DB::table('users')->insertGetId([
            'login_name' => 'admin2',
            'password' => Encrypt::encrypt('123456'),
            'status' => 1,
        ]);

        $service->giveRoleTo($userId,'system_admin');

        return $userId;
    }

    public function getSchoolListTemplateFile(ExcelService $excelService){
        $name = "学校信息导入模板";

        $rows =[['学校名称','学校等级','学校通信地址','学校邮编','学校负责人姓名','负责人手机号']] ;

        $excelService->export($name,$rows);
    }
}