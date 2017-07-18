<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午7:23
 */

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface ExcelServiceInterface
{
    function import(UploadedFile $file);

    function export(string $name,array $rows);
}

?>