<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/13
 * Time: 下午1:09
 */

namespace App\Services;


use App\Common\Utils;
use App\Exceptions\Auth\UserExistedException;
use App\Exceptions\Auth\UserNotExistException;
use App\Exceptions\Common\VerifyCodeSendException;
use App\Exceptions\VerifyCode\VerifyCodeErrorException;
use App\Exceptions\VerifyCode\VerifyCodeTimeOutException;
use Sms;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\VerifyCodeRepository;
use App\Services\Contracts\VerifyCodeServiceInterface;

class VerifyCodeService implements VerifyCodeServiceInterface
{
    const BASE = "123456789";

    const SIZE = 4;

    private $userRepository;

    private $verifyCodeRepository;

    public function __construct(UserRepository $userRepository, VerifyCodeRepository $verifyCodeRepository)
    {
        $this->userRepository = $userRepository;
        $this->verifyCodeRepository = $verifyCodeRepository;
    }


    public function sendVerifyCode(string $mobile, int $type): string
    {
        $verifyCode = Utils::randomString(self::BASE, self::SIZE);

        $user = $this->userRepository->getBy('mobile', $mobile)->count();

        //1=>注册，2=>忘记密码,3=>修改手机
        if ($type == 1) {
            if ($user >= 1) {
                throw new UserExistedException("mobile has been registered");
            } else {
                if (Sms::sendVerifyCode($mobile, $verifyCode)[1] != "0")
                    throw new VerifyCodeSendException();
            }
        } else if ($type == 2) {
            if ($user != 1) {
                throw new UserNotExistException("mobile not Exist");
            } else {
                if (Sms::forgetPassword($mobile, $verifyCode)[1] != "0")
                    throw new VerifyCodeSendException();
            }
        } else if ($type == 3) {
            if ($user != 1) {
                throw new UserExistedException("mobile not Exist");
            } else {
                if (Sms::updateUserMobile($mobile, $verifyCode)[1] != "0")
                    throw new VerifyCodeSendException();
            }
        }

        return $this->updateVerifyCode($mobile, $type, $verifyCode);

    }

    public function updateVerifyCode(string $mobile, int $type, string $code): string
    {

        $verify = $this->verifyCodeRepository->getByMult(['mobile' => $mobile, 'type' => $type])->first();

        if ($verify == null) {
            $newVerify = [
                'mobile' => $mobile,
                'type' => $type,
                'code' => $code,
                'expires_at' => Utils::createTimeStamp() + 300000,
                'updated_at' => Utils::createTimeStamp()
            ];

            $this->verifyCodeRepository->insert($newVerify);
        } else {
            $this->verifyCodeRepository->updateWhere(['mobile' => $mobile, 'type' => $type], ['code' => $code, 'updated_at' => Utils::createTimeStamp(), 'expires_at' => Utils::createTimeStamp() + 300000]);
        }

        return $code;
    }

    function checkVerifyCode(string $mobile, int $type, string $code): bool
    {

        $verifyCode = $this->verifyCodeRepository->getByMult(['mobile' => $mobile, 'type' => $type, 'code' => $code])->first();

        if ($verifyCode == null)
            throw new VerifyCodeErrorException();
        //dd($verifyCode);
        if ($verifyCode['expires_at'] < Utils::createTimeStamp())
            throw new VerifyCodeTimeOutException();

        return true;
    }
}