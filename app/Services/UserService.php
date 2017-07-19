<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/6/30
 * Time: 下午3:20
 */

namespace App\Services;


use App\Common\Encrypt;
use App\Exceptions\Auth\PasswordWrongException;
use App\Exceptions\Auth\UserExistedException;
use App\Exceptions\Auth\UserNotExistException;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Contest\ContestNotExistException;
use App\Exceptions\Contest\ContestRegisterTimeError;
use App\Repository\Eloquent\ContestRecordRepository;
use App\Repository\Eloquent\ContestRepository;
use App\Repository\Eloquent\SchoolRepository;
use App\Repository\Eloquent\UserRepository;
use App\Services\Contracts\UserServiceInterface;
use Carbon\Carbon;
use SebastianBergmann\Environment\Console;

class UserService implements UserServiceInterface
{
    private $userRepository;
    private $tokenService;
    private $verifyCodeService;
    private $roleService;
    private $contestRecordRepo;
    private $contestRepo;
    private $schoolRepo;

    public function __construct(SchoolRepository $schoolRepository, ContestRepository $contestRepository, UserRepository $userRepository, ContestRecordRepository $recordRepository, TokenService $tokenService, VerifyCodeService $verifyCodeService, RoleService $roleService)
    {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        $this->verifyCodeService = $verifyCodeService;
        $this->roleService = $roleService;
        $this->contestRecordRepo = $recordRepository;
        $this->contestRepo = $contestRepository;
        $this->schoolRepo = $schoolRepository;
    }

    public function getRepository()
    {
        return $this->userRepository;
    }

    public function getSchools(int $page, int $size)
    {
        if ($size == -1) {
            //不分页
            $schools = $this->schoolRepo->all(['id', 'name', 'level']);
            $count = count($schools);
        } else {
            $schools = $this->schoolRepo->paginate($page, $size, [], ['id', 'name', 'level']);
            $count = $this->schoolRepo->getWhereCount();
        }

        return [
            'schools' => $schools,
            'count' => $count
        ];
    }

    /**
     * 注册
     * @param array $userInfo 用户信息
     * @return int 新注册的用户的id
     */

    public function register(array $userInfo): int
    {
        // 在这里设置需要检测的字段

        $uniques = [
            'name', 'mobile', 'email'
        ];

        foreach ($uniques as $unique) {
            if ($this->userRepository->getBy($unique, $userInfo[$unique])->count() >= 1) {
                throw new UserExistedException($unique);
            }
        }


        if ($this->verifyCodeService->checkVerifyCode($userInfo['mobile'], 1, $userInfo['code']))
            $userInfo['status'] = 1;


        $userInfo['password'] = Encrypt::encrypt($userInfo['password']); // 对密码加密

        $users = ['name' => $userInfo['name'], 'email' => $userInfo['email'],
            'mobile' => $userInfo['mobile'], 'password' => $userInfo['password'],
            'sex' => $userInfo['sex'], 'school_id' => $userInfo['schoolId'],
            'school_name' => $userInfo['schoolName'], 'status' => $userInfo['status']];

        $userId = $this->userRepository->insertWithId($users);

        $this->roleService->giveRoleTo($userId, 'student');

        return $userId;
    }

    /**
     * 在开启了注册验证的条件下，用于激活注册的用户
     * @param int $userId
     * @throws
     * @return bool
     */
    public function active(int $userId): bool
    {
        if (!config('user.register_need_check')) {
            return true;
        }

        $user = $this->userRepository->get($userId, ['id', 'status']);

        if ($user == null) {
            throw new UserNotExistException();
        }

        if ($user->status == 1) {
            return true;
        } else if ($user->status == 0) {
            return $this->userRepository->update(['status' => 1], $userId) == 1;
        }

        return false;
    }

    /**
     * @param string $param 登录的方式，可选mobile,email，用于在数据库中指定字段
     * @param string $identifier 用户输入的值
     * @param string $password 密码
     * @param string $ip
     * @return array
     */

    public function loginBy(string $param, string $identifier, string $password, string $ip, int $client)
    {
        // 在这里修改需要获取的字段

        $user = $this->userRepository->getBy($param, $identifier)->first();

        if ($user == null) {
            throw new UserNotExistException();
        }

        // 检查密码

        if (!Encrypt::check($password, $user->password)) {
            throw new PasswordWrongException();
        }

        return [
            'user' => $user,
            'token' => $this->tokenService->makeToken($user->id, $ip, $client)
        ];
    }

    public function login(int $userId, string $ip, int $client): string
    {

        $user = $this->userRepository->get($userId);

        if ($user == null) {
            throw new UserNotExistException();
        }

        return [
            'user' => $user,
            'token' => $this->tokenService->makeToken($userId, $ip, $client)
        ];
    }

    public function logout(int $userId, int $client)
    {
        $this->tokenService->destoryToken($userId, $client);
    }

    public function getUserInfo(array $condition)
    {
        return $this->userRepository->getByMult($condition)->first();
    }

    public function isUserExist(array $condition): bool
    {
        return $this->userRepository->getWhereCount($condition) == 1;
    }

    public function createUser(array $user): int
    {
        return $this->userRepository->insertWithId($user);
    }


    public function updateUserPassword(array $userInfo)
    {
        $user = $this->userRepository->get($userInfo['userId']);


        if ($user == null) {
            throw new UserNotExistException();
        }

        // 检查密码

        if (!Encrypt::check($userInfo['password'], $user->password)) {
            throw new PasswordWrongException();
        }

        return $this->userRepository->update(['password' => Encrypt::encrypt($userInfo['newPassword'])], $userInfo['userId']);
    }
}