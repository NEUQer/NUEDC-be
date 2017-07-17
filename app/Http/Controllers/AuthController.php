<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午12:36
 */

namespace App\Http\Controllers;


use App\Common\ValidationHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{


    private $authService;

    /**
     * AuthController constructor.
     * @param $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

//    public function addPrivilegeToSys(Request $request){
//        $rules = [
//            'privilegeName'=>'required|max:100',
//            'displayName' => 'required|max:100',
//            'description' => 'required|max:255'
//        ];
//
//        ValidationHelper::validateCheck($request->all(), $rules);
//
//        $privilegeInfo = ValidationHelper::getInputData($request,$rules);
//
//        $this->authService->addPrivilegeToDB($privilegeInfo);
//
//        return response()->json(
//            [
//                'code'=>0
//            ]
//        );
//    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 考虑到代码中用权限名检查权限，权限一旦创建应该不允许修改权限名字
     */
    function updateSysPrivilege(Request $request){
        $rules = [
            'privilegeName'=>'required',
            'description'=>'required|string',
            'displayNewName'=>'required|string'
        ];

        $privileges = ValidationHelper::checkAndGet($request,$rules);




        return response()->json(
            [
                'code'=>0,
                'data'=> ['privilege'=>$this->authService->updatePrivilegeAtDB($privileges['privilegeName'],$privileges['description'],$privileges['displayNewName'])]
            ]
        );
    }

    public function updateUserPrivileges(Request $request){
        $rules = [
            'NowPrivileges'=>'required|array',
            'customId'=>'required'
        ];

        $privilegeInfo = ValidationHelper::checkAndGet($request,$rules);

        return response()->json([
            'code'=>0,
            'data'=>[
                'privileges'=>$this->authService->updateUserPrivilege($privilegeInfo['customId'],$privilegeInfo['NowPrivileges'])
            ]
        ]);
    }

    public function createRole(Request $request){
        $rules = [
            'role'=>'required|array',
            'privileges'=>'required|array'
        ];

        $RoleInfo = ValidationHelper::checkAndGet($request,$rules);

        $this->authService->createRole($RoleInfo['role'],$RoleInfo['privileges']);

        return response()->json(
            ['code'=>0]
        );
    }
    public function giveRoleToCustom(Request $request){
        $rules = [
            'customId'=>'required',
            'roleName'=>'required|max:100'
        ];

        $roleInfo = ValidationHelper::checkAndGet($request,$rules);

        $this->authService->giveRoleTo($roleInfo['customId'],$roleInfo['roleName']);

        return response()->json(
            ['code'=>0]
        );
    }

    public function updateRoleInfo(Request $request){
        $rules = [
            'roleName'=>'required',
            'role'=>'required|array',
            'privileges'=>'required|array'
        ];

        $roleInfo = ValidationHelper::checkAndGet($request,$rules);

        $this->authService->updateRole($roleInfo);

        return response()->json(
            ['code'=>0]
        );
   }

   public function deleteRole(Request $request){
        $rules = [
            'roleName'=>'required'
        ];

        $role = ValidationHelper::checkAndGet($request,$rules);


        $this->authService->deleteRole($role['roleName']);

        return response()->json(['code'=>0]);
   }


   /*
    * 下面为一些展示的接口
    */

    /*
     * 展示权限表中的所有权限的基本信息
     */
   public function getAllPrivilegeInfo(Request $request){
        return response()->json(
            [
                'code'=>0,
                'data'=>[
                    'privilegeInfo'=>$this->authService->getAllPrivilege()
                ]
            ]
        );
   }
   /*
    * 展示角色的基本信息和关联的权限
    */
   public function getAllRoleInfo(Request $request){
       return response()->json(
           [
               'code'=>0,
               'data'=>['roleInfo'=>$this->authService->getAllRoleInfo()]
           ]
       );
   }

    /*
     * 展示用户的当前权限
     */
   public function getUserPermissionInfo(Request $request){
       $rules = [
           'customId'=>'required'
       ];
       $custom = ValidationHelper::checkAndGet($request,$rules);

       return response()->json(
           [
               'code'=>0,
               'data'=>['userId'=>$custom['customId'],'privileges'=>$this->authService->getUserPermissionInfo($custom['customId'])]
           ]
       );
   }
}