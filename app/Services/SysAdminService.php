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
use App\Exceptions\Common\UnknownException;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\SchoolRepository;
use App\Services\Contracts\SysAdminServiceInterface;
use Illuminate\Support\Facades\DB;

class SysAdminService implements SysAdminServiceInterface
{
    private $contestRepo;
    private $userService;
    private $schoolRepo;
    private $authService;
    private $recordRepo;

    public function __construct(
        ContestRepository $contestRepository, UserService $userService,
        SchoolRepository $schoolRepository, AuthService $authService,
        ContestRecordRepository $contestRecordRepository
    )
    {
        $this->contestRepo = $contestRepository;
        $this->userService = $userService;
        $this->schoolRepo = $schoolRepository;
        $this->authService = $authService;
        $this->recordRepo = $contestRecordRepository;
    }

    public function login(string $loginName, string $password, string $ip)
    {
        return $this->userService->loginBy('login_name', $loginName, $password, $ip, 1);
    }

    // 竞赛管理

    public function getContests()
    {
        return $this->contestRepo->all([
            'id', 'description', 'title', 'status', 'can_register',
            'can_select_problem', 'can_select_problem', 'register_start_time',
            'register_end_time', 'problem_start_time', 'problem_end_time'
        ]);
    }

    public function createContest(array $contest): int
    {
        return $this->contestRepo->insertWithId($contest);
    }

    public function updateContest(array $condition, array $contest): bool
    {
        return $this->contestRepo->updateWhere($condition, $contest) == 1;
    }

    public function deleteContest(array $condition): bool
    {
        return $this->contestRepo->deleteWhere($condition) == 1;
    }

    // 学校管理员

    public function getSchoolAdmins(int $page, int $size)
    {
        $schoolAdmins = $this->userService->getRepository()->paginate($page, $size, ['role' => 'school_admin'], [
            'id', 'name', 'email', 'mobile', 'school_id', 'school_name', 'sex', 'status', 'role', 'created_at', 'login_name'
        ]);

        $count = $this->userService->getRepository()->getWhereCount(['role' => 'school_admin']);

        return [
            'school_admins' => $schoolAdmins,
            'count' => $count
        ];
    }

    public function generateSchoolAdmin(int $schoolId)
    {
        $loginName = 'SCHOOL_' . $schoolId . '_' . Utils::randomString('', 6);
        $password = Utils::randomString('', 6);
        $school = $this->schoolRepo->get($schoolId, ['id', 'name']);

        $userId = -1;

        DB::transaction(function () use (&$userId, $password, $school, $loginName) {
            $userId = $this->userService->getRepository()->insertWithId([
                'login_name' => $loginName,
                'school_id' => $school->id,
                'school_name' => $school->name,
                'password' => Encrypt::encrypt($password),
                'status' => 1,
                'role' => 'school_admin'
            ]);
            $this->authService->giveRoleTo($userId, 'school_admin');
        });

        return [
            'user_id' => $userId,
            'login_name' => $loginName,
            'password' => $password
        ];
    }

    // 这个方法不仅可以修改学校管理员，对于任意用户都是可用的

    public function updateUser(int $userId, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Encrypt::encrypt($data['password']);
        }

        return $this->userService->getRepository()->updateWhere(['id' => $userId], $data) == 1;
    }

    public function deleteUser(int $userId): bool
    {
        return $this->userService->getRepository()->deleteWhere(['id' => $userId]) == 1;
    }

    public function getSchools(int $page, int $size)
    {
        $schools = $this->schoolRepo->paginate($page, $size, [], [
            'id', 'name', 'level', 'address', 'post_code', 'principal', 'principal_mobile'
        ]);

        $count = $this->schoolRepo->getWhereCount([]);

        return [
            'schools' => $schools,
            'count' => $count
        ];
    }

    public function createSchool(array $data): int
    {
        return $this->schoolRepo->insertWithId($data);
    }

    public function updateSchool(int $schoolId, array $data): bool
    {
        return $this->schoolRepo->update($data, $schoolId) == 1;
    }

    public function deleteSchool(int $schoolId): bool
    {
        return $this->schoolRepo->deleteWhere(['id' => $schoolId]) == 1;
    }

    // 参赛管理

    public function getRecords(int $page, int $size, array $condition)
    {
        if ($size == -1) {
            $records = $this->recordRepo->getByMult($condition);
            $count = count($records);
        } else {
            $records = $this->recordRepo->paginate($page, $size, $condition);
            $count = $this->recordRepo->getWhereCount($condition);
        }

        return [
            'records' => $records,
            'count' => $count
        ];
    }

    public function updateRecord(int $recordId, array $data): bool
    {
        return $this->recordRepo->update($data, $recordId) == 1;
    }

    public function deleteRecord(int $recordId): bool
    {
        return $this->recordRepo->deleteWhere(['id' => $recordId]) == 1;
    }

    public function updateResults(array $results): bool
    {
        // TODO: Implement updateResults() method.
    }
}