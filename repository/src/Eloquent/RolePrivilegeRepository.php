<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/8
 * Time: 上午10:19
 */

namespace App\Repository\Eloquent;


class RolePrivilegeRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\RolePrivilege";
    }

    function getRolePriInfo(){

        return $this->model
            ->leftjoin('privileges as privilege','privilege_name','=','privilege.name')
            ->leftjoin('roles as role','role_name','=','role.name')
            ->select('role_name.name as roleName','role.display_name as roleDisplayName',
                'privilege.name as privilegeName', 'privilege.display_name as privilegeDisplayName','privilege.description as privilegeDescription')
            ->get();
    }
}