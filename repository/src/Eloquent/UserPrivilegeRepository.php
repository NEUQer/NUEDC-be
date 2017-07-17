<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/8
 * Time: 上午10:21
 */

namespace App\Repository\Eloquent;


class UserPrivilegeRepository extends AbstractRepository
{
    function model()
    {
        return "App\Repository\Models\UserPrivilege";
    }

    public function checkUserPrivileges(int $userId,array $privileges)
    {
        $count = $this->model
            ->where('user_id',$userId)
            ->whereIn('privilege',$privileges)
            ->count();

        if ($count != count($privileges)) {
            return false;
        }

        return true;
    }

    public function getUserPrivilegeInfo(int $userId){
        return $this->model->where('user_id',$userId)->
        leftjoin('privileges','privileges.name','=','user_privileges.privilege')->
        select('privilege as privilegeName','display_name as displayName','description')
            ->get()->toArray();
    }
}