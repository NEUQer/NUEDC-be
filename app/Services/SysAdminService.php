<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/7/13
 * Time: 上午11:23
 */

namespace App\Services;


use App\Common\Encrypt;
use App\Common\Utils;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\SchoolRepository;
use App\Services\Contracts\SysAdminServiceInterface;

class SysAdminService implements SysAdminServiceInterface
{
    private $contestRepo;
    private $userService;
    private $schoolRepo;

    public function __construct(ContestRepository $contestRepository,UserService $userService,SchoolRepository $schoolRepository)
    {
        $this->contestRepo = $contestRepository;
        $this->userService = $userService;
        $this->schoolRepo = $schoolRepository;
    }

    public function login(string $loginName, string $password, string $ip)
    {
        return $this->userService->loginBy('login_name',$loginName,$password,$ip,1);
    }

    public function getContests()
    {
        return $this->contestRepo->all([
            'id','title','status','can_register',
            'can_select_problem','can_select_problem','register_start_time',
            'register_end_time','problem_start_time','problem_end_time'
        ]);
    }

    public function createContest(array $contest): int
    {
        return $this->contestRepo->insertWithId($contest);
    }

    public function updateContest(array $condition, array $contest): bool
    {
        return $this->contestRepo->updateWhere($condition,$contest) == 1;
    }

    public function deleteContest(array $condition): bool
    {
        return $this->contestRepo->deleteWhere($condition) == 1;
    }

    function generateSchoolAdmin(int $schoolId, array $schoolAdmin)
    {
        $loginName = 'SCHOOL_'.$schoolId.Utils::randomString('',6);
        $password = Utils::randomString('',6);
        $school = $this->schoolRepo->get($schoolId,['id','name']);

        $userId = $this->userService->createUser([
            'login_name' => $loginName,
            'school_id' => $schoolId,
            'school_name' => $school->name,
            'password' => Encrypt::encrypt($password),
            'status' => 1
        ]);

        //todo 授权

        return $userId;
    }

    function updateSchoolAdmin(int $userId, array $data): bool
    {
        // TODO: Implement updateSchoolAdmin() method.
    }

    function deleteSchoolAdmin(int $userId): bool
    {
        // TODO: Implement deleteSchoolAdmin() method.
    }


}