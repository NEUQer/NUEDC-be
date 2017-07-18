<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午7:26
 */

namespace App\Services;

use App\Exceptions\ExcelStoreFailException;
use App\Services\Contracts\ExcelServiceInterface;
use App\Exceptions\ExcelExportFailException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExcelService implements ExcelServiceInterface
{
    /**
     * 导入excel
     *
     * @param UploadedFile $file
     * @return array
     * @throws ExcelStoreFailException
     */
    public function import(UploadedFile $file)
    {
        if ($file->isValid()) {
            // 获取文件相关信息
            $ext = $file->getClientOriginalExtension();     // 扩展名
            $realPath = $file->getRealPath();   //临时文件的绝对路径
            // 上传文件
            $filename = uniqid() . '.' . $ext;
            // 使用uploads本地存储空间（目录）
            $bool = Storage::disk('import')->put($filename, file_get_contents($realPath));

            if (!$bool) {
                throw new ExcelStoreFailException();
            }

            $filePath = 'storage/import/' . $filename;
        }

        $datas = null;
        Excel::load($filePath, function ($reader) use (&$datas) {
            $datas = $reader->get();
        }, 'UTF-8');
        $result = ['rows' => $datas];
        return $result;
    }

    public function export(string $name,array $rows)
    {
        Excel::create($name, function ($excel) use ($rows) {
            $excel->sheet('sheet1', function ($sheet) use ($rows) {
                $sheet->rows($rows);
            });
        })->download('xlsx',[
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }
}