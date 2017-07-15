<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午12:53
 */

namespace App\Services\Contracts;


interface AuthServiceInterface
{
    //function addPrivilegeToDB(array $privilegeInfo);

    function updatePrivilegeAtDB(string $privilegeExistedName,string $description = null,string $displayName = null);

    function updateUserPrivilege(int $userId,array $privileges);

    function giveRoleTo(int $userId,string $roleName);

    function createRole(array $role, array $privileges);

    function deleteRole(string $roleName);

    function updateRole(array $roleInfo);

    function getAllPrivilege();

    function getAllRoleInfo();

    function getUserPermissionInfo(int $userId);
}