<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午7:35
 */

namespace App\Http\Controllers;

use App\Services\ExcelService;
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
            // 文件是否上传成功
//            if ($file->isValid()) {
//                // 获取文件相关信息
//                $originalName = $file->getClientOriginalName(); // 文件原名
//                $ext = $file->getClientOriginalExtension();     // 扩展名
//                $realPath = $file->getRealPath();   //临时文件的绝对路径
//                $type = $file->getClientMimeType();     // image/jpeg
//                // 上传文件
//                $filename = uniqid() . '.' . $ext;
//                // 使用我们新建的uploads本地存储空间（目录）
//                $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
//                $filePath = 'storage/app/uploads/' . $filename;
//                $data = $this->excelService->import($filePath);
//            }
            $data = $this->excelService->import($file);
        }
        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }
}