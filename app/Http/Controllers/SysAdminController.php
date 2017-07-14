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
use App\Services\SysAdminService;
use Illuminate\Http\Request;

class SysAdminController extends Controller
{
    private $sysAdminService;

    public function __construct(SysAdminService $sysAdminService)
    {
        $this->sysAdminService = $sysAdminService;
        $this->middleware('token');
    }

    public function getAllContests(Request $request)
    {
        if (!Permission::checkPermission($request->user->id,['manage_contests'])) {
            throw new PermissionDeniedException();
        }

        $contests =  $this->sysAdminService->getContests();

        return response()->json([
            'code' => 0,
            'data' => [
                'contests' => $contests
            ]
        ]);
    }

    public function createContest(Request $request)
    {
        $contest = ValidationHelper::checkAndGet($request,[
            'title' => 'required|string|max:45',
            'description' => 'required',
            'status' => 'string|max:255',
            'register_start_time' => 'required|date',
            'register_end_time' => 'required|date',
            'problem_start_time' => 'required|date',
            'problem_end_time' => 'required|date',
            'add_on' => 'string'
        ]);

        if (!Permission::checkPermission($request->user->id,'manage_contest')) {
            throw new PermissionDeniedException();
        }

        $contestId = $this->sysAdminService->createContest($contest);

        return response()->json([
            'code' => 0,
            'data' => [
                'contest_id' => $contestId
            ]
        ]);
    }

    public function updateContest(Request $request,int $contestId)
    {
        $contest = ValidationHelper::checkAndGet($request,[
            'title' => 'required|string|max:45',
            'description' => 'required',
            'status' => 'string|max:255',
            'register_start_time' => 'required|date',
            'register_end_time' => 'required|date',
            'problem_start_time' => 'required|date',
            'problem_end_time' => 'required|date',
            'add_on' => 'string'
        ]);

        if (!Permission::checkPermission($request->user->id,'manage_contest')) {
            throw new PermissionDeniedException();
        }

        if (!$this->sysAdminService->updateContest(['id' => $contestId],$contest)) {
            throw new UnknownException('fail to update contest');
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteContest(Request $request,int $contestId)
    {
        ValidationHelper::validateCheck($request->all(),[
            'password' => 'required|string'
        ]);

        if (!Encrypt::check($request->password,$request->user->password)) {
            throw new PasswordWrongException();
        }

        if (!$this->sysAdminService->deleteContest(['id' => $contestId])) {
            throw new UnknownException('fail to delete contest');
        }

        return response()->json([
            'code' => 0
        ]);
    }
}