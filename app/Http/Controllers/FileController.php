<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/7/18
 * Time: 下午11:42
 */

namespace App\Http\Controllers;


use App\Common\Utils;
use App\Common\ValidationHelper;
use App\Exceptions\Common\FormValidationException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Facades\Permission;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct()
    {
//        $this->middleware('token');
    }

    public function uploadPublic(Request $request)
    {
//        if (!Permission::checkPermission($request->user->id,['manage_files'])) {
//            throw new PermissionDeniedException();
//        }

        if (!$request->hasFile('upload')) {
            throw new FormValidationException(['upload filed is required']);
        }

//        $ext = $request->upload->extension();
//        $filename = 'upload_'.Utils::createTimeStamp();
        $path = $request->upload->store('upload','public');

        return response([
            'code' => 0,
            'data' => [
                'url' => url('/storage/'.$path)
            ]
        ]);
    }

    public function uploadPrivate(Request $request)
    {
        if (!$request->hasFile('upload')) {
            throw new FormValidationException(['upload filed is required']);
        }

        $path = $request->upload->store('upload','private');

        return response([
            'code' => 0,
            'data' => [
                'path' => $path
            ]
        ]);
    }

    public function getPrivate(Request $request)
    {
        $input = ValidationHelper::checkAndGet($request,[
            'path' => 'required|string'
        ]);

        $path = storage_path('app/private/'.$input['path']);

        return response()->file($path,[
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }
}