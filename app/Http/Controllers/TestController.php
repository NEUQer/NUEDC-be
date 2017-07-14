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
use App\Services\ExcelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $this->excelService->export($cellData, $fileName);

        return response()->json([
            'code' => 0
        ]);
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

    public function test(Request $request)
    {
        echo new Carbon();
    }
}