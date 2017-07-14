<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/14
 * Time: 下午2:25
 */

namespace App\Http\Controllers;


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

    public function getContest(int $contestId)
    {

    }

    public function createContest(Request $request)
    {

    }

    public function updateContest(Request $request,int $contestId)
    {

    }

    public function deleteContest(Request $request,int $contestId)
    {

    }
}