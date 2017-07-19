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

    public function deleteContest(int $id): bool
    {
        $flag = false;

        DB::transaction(function () use ($id, &$flag) {
            $this->contestRepo->deleteWhere(['id' => $id]);
            $this->recordRepo->deleteWhere(['contest_id' => $id]);
            $flag = true;
        });

        return $flag;
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

    public function generateSchoolAdmin(array $schoolIds)
    {
        $succeed = [];
        $failed = [];

        foreach ($schoolIds as $schoolId) {

            $school = $this->schoolRepo->get($schoolId, ['id', 'name', 'level', 'principal', 'principal_mobile']);

            if ($school === null || $school->principal === null || $school->principal_mobile === null) {
                $failed[] = $schoolId;
                break;
            }

            // 检查相应用户是否存在

            $preUser = $this->userService->getRepository()->getWhereCount(['mobile' => $school->principal_mobile]);

            if ($preUser !== 0) {
                $failed[] = $schoolId;
            }

            DB::transaction(function () use ($school, &$succeed) {
                $userId = $this->userService->getRepository()->insertWithId([
                    'school_id' => $school->id,
                    'name' => $school->principal,
                    'mobile' => $school->principal_mobile,
                    'school_name' => $school->name,
                    'password' => Encrypt::encrypt('NUEDC2017'),
                    'status' => 1,
                    'role' => 'school_admin'
                ]);
                $this->authService->giveRoleTo($userId, 'school_admin');
                $succeed[] = $school->id;
            });
        }

        return [
            'succeed' => $succeed,
            'failed' => $failed
        ];
    }

    public function createSchoolAdmins(array $user)
    {
        // users需要填入name,mobile,school_id,school_name

        $school = $this->schoolRepo->get($user['school_id']);

        if ($school === null) {
            throw new UnknownException('no such school');
        }

        $preUser = $this->userService->getRepository()->getWhereCount(['mobile' => $user['mobile']]);

        if ($preUser !== 0) {
            return -1;
        }

        $userId = -1;

        DB::transaction(function () use ($user, $school, &$userId) {
            $userId = $this->userService->getRepository()->insertWithId([
                'name' => $user['name'],
                'school_id' => $school->id,
                'school_name' => $school->name,
                'password' => Encrypt::encrypt($user['password']),
                'mobile' => $user['mobile'],
                'status' => 1,
                'role' => 'school_admin'
            ]);

            $this->authService->giveRoleTo($userId, 'school_admin');
        });

        return $userId;
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

    public function importSchools(array $schools)
    {
        foreach ($schools as &$school) {
            $schoolId = $this->schoolRepo->insertWithId($school);
            $school['school_id'] = $schoolId;
        }
        return $schools;
    }

    public function getSchools(int $page, int $size)
    {
        $schools = $this->schoolRepo->paginate($page, $size, [], [
            'id', 'name', 'level', 'address', 'post_code', 'principal', 'principal_mobile'
        ], 'name', 'asc');

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
        $flag = false;
        // 级联删除
        DB::transaction(function () use ($schoolId, &$flag) {
            $this->schoolRepo->deleteWhere(['id' => $schoolId]);
            $this->recordRepo->deleteWhere(['school_id' => $schoolId]);
            $this->userService->getRepository()->deleteWhere(['school_id' => $schoolId]);
            $flag = true;
        });

        return $flag;
    }

    // 参赛管理

    public function getRecords(int $page, int $size, array $condition)
    {
        // 如果没有填写contest_id，就默认取出数据库中最新的那个

        if ($condition['contest_id'] === -1) {
            $condition['contest_id'] = $this->contestRepo->getMaxId();
        }

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

    public function updateRecord(array $updates): bool
    {
        $flag = false;
        DB::transaction(function ()use($updates,&$flag){
            foreach ($updates as &$update) {
                $recordId = $update['record_id'];
                unset($update['record_id']);
                $this->recordRepo->update($update,$recordId);
            }
            $flag = true;
        });

        return $flag;
    }

    public function deleteRecord(array $recordIds): bool
    {
        return $this->recordRepo->deleteWhereIn('id',$recordIds) == count($recordIds);
    }

    public function updateResults(array $results): bool
    {
        // TODO: Implement updateResults() method.
    }
}