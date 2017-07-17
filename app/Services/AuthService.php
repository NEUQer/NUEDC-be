<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午12:59
 */

namespace App\Services;


use App\Exceptions\Auth\PrivilegeNameExisted;
use App\Exceptions\Permission\PrivilegeNotExistException;
use App\Repository\Eloquent\PrivilegeRepository;
use App\Repository\Eloquent\RolePrivilegeRepository;
use App\Repository\Eloquent\RoleRepository;
use App\Repository\Eloquent\UserPrivilegeRepository;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\DB;

class AuthService implements AuthServiceInterface
{

    private $privilegeService;

    private $roleService;

    private $privilegeRepo;

    private $roleRepo;

    private $rolePrivilegeRepo;

    private $userPrivilegeRepo;


    public function __construct(PrivilegeService $privilegeService, RoleService $roleService, PrivilegeRepository $privilegeRepo, RoleRepository $roleRepo, RolePrivilegeRepository $rolePrivilegeRepo, UserPrivilegeRepository $userPrivilegeRepo)
    {
        $this->privilegeService = $privilegeService;
        $this->roleService = $roleService;
        $this->privilegeRepo = $privilegeRepo;
        $this->roleRepo = $roleRepo;
        $this->rolePrivilegeRepo = $rolePrivilegeRepo;
        $this->userPrivilegeRepo = $userPrivilegeRepo;
    }

//    function addPrivilegeToDB(array $privilegeInfo)
//    {
//        $privilegeName = $privilegeInfo['privilegeName'];
//        $description   = $privilegeInfo['description'];
//        $displayName   = $privilegeInfo['displayName'];
//
//
//        if ($this->privilegeRepo->getBy('name',$privilegeName)->first() != null)
//            throw new PrivilegeNameExisted('name');
//
//        if ($this->privilegeRepo->getBy('display_name',$displayName)->first() != null)
//            throw new PrivilegeNameExisted('displayName');
//
//        $data = [
//            'name'=>$privilegeName,
//            'display_name'=>$displayName,
//            'description'=>$description
//        ];
//
//        return $this->privilegeRepo->insert($data);
//    }

    function updatePrivilegeAtDB(string $privilegeExistedName,string $description , string $displayName )
    {
        if ($this->privilegeRepo->getBy('name',$privilegeExistedName)->first() == null)
            throw new PrivilegeNotExistException();
        DB::transaction(function ()use($privilegeExistedName,$description,$displayName){
            if ($displayName != "NULL"){
                if ($this->privilegeRepo->getBy('display_name',$displayName)->first() != null)
                    throw new PrivilegeNameExisted('displayName');
                if ($description != "NULL")
                    $this->privilegeRepo->updateWhere(['name'=>$privilegeExistedName],['display_name'=>$displayName,'description'=>$description]);
                else
                    $this->privilegeRepo->updateWhere(['name'=>$privilegeExistedName],['display_name'=>$displayName]);
            }else{
                if ($description != "NULL")
                     $this->privilegeRepo->updateWhere(['name'=>$privilegeExistedName],['description'=>$description]);
            }
        });

        return $this->privilegeRepo->getBy('name',$privilegeExistedName)->toArray();
    }

    function updateUserPrivilege(int $userId,array $privileges)
    {
        $this->privilegeService->refreshUserPrivileges($userId,$privileges);

        $privileges = $this->userPrivilegeRepo->getBy('user_id',$userId,['privilege']);

        $data = [];

        foreach ($privileges as $privilege)
        {
            $data[] = $privilege['privilege'];
        }

        return $data;
    }

    function giveRoleTo(int $userId, string $roleName)
    {
        return $this->roleService->giveRoleTo($userId,$roleName);
    }

    function createRole(array $role, array $privileges)
    {
        // TODO: Implement createRole() method.
    }

    function updateRole(array $roleInfo)
    {
        $roleName = $roleInfo['roleName'];
        $role = $roleInfo['role'];
        $privileges = $roleInfo['privileges'];

        return $this->roleService->updateRole($roleName,$role,$privileges);
    }

    function deleteRole(string $roleName)
    {
        return $this->roleService->deleteRole($roleName);
    }

    function getAllPrivilege()
    {
        $privileges = $this->privilegeRepo->all();

        $data = [];

        foreach ($privileges as $privilege){
            $data[] = [
                'name'=>$privilege['name'],
                'displayName'=>$privilege['display_name'],
                'description'=>$privilege['description']
            ];
        }

        return $data;
    }

    function getAllRoleInfo()
    {
        $info = $this->rolePrivilegeRepo->getRolePriInfo()->toArray();

        $privilege = [];
        $data = [];
        for($i = 0; $i<count($info); $i++){

            $privilege[] = ['privilegeName' => $info[$i]['privilegeName'],
                'privilegeDisplayName' => $info[$i]['privilegeDisplayName'],
                'privilegeDescription'=>$info[$i]['privilegeDescription']];

           if ($i == count($info) -1){
               continue;
           }else if ($i != count($info) -1){

                if ($info[$i]['roleName'] == $info[$i+1]['roleName']){

                    if ($i+1 == count($info) -1){

                        $privilege[] = ['privilegeName' => $info[$i+1]['privilegeName'],
                            'privilegeDisplayName' => $info[$i+1]['privilegeDisplayName'],
                            'privilegeDescription'=>$info[$i+1]['privilegeDescription']];

                        $data[] = [
                            'roleName'=>$info[$i]['roleName'],
                            'roleDisplayName' => $info[$i]['roleDisplayName'],
                            'privilege'=> $privilege
                        ];

                    }else continue;

                }else {
                    $data[] = [
                        'roleName'=>$info[$i]['roleName'],
                        'roleDisplayName' => $info[$i]['roleDisplayName'],
                        'privilege'=> $privilege
                    ];
                    $privilege = [];
                }

                }
            }


        return $data;
    }

    function getUserPermissionInfo(int $userId)
    {
       return $this->userPrivilegeRepo->getUserPrivilegeInfo($userId);
    }
}