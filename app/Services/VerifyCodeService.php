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
use App\Exceptions\VerifyCode\VerifyCodeErrorException;
use App\Exceptions\VerifyCode\VerifyCodeTimeOutException;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\VerifyCodeRepository;
use App\Services\Contracts\VerifyCodeServiceInterface;

class VerifyCodeService implements VerifyCodeServiceInterface
{
    const BASE = "123456789";

    const SIZE = 4;

    const MESSAGE = "欢迎使用本系统，您的验证码为";

    private $smsService;



    private $userRepository;

    private $verifyCodeRepository;

    public function __construct(SmsService $smsService,UserRepository $userRepository,VerifyCodeRepository $verifyCodeRepository)
    {
        $this->smsService = $smsService;
        $this->userRepository= $userRepository;
        $this->verifyCodeRepository = $verifyCodeRepository;
    }


    public function sendVerifyCode(string $mobile,int $type): string
    {
        $verifyCode = Utils::randomString(self::BASE,self::SIZE);

        $user = $this->userRepository->getBy('mobile',$mobile)->count();
        if ($type == 1){
            if ($user >= 1)
                throw new UserExistedException("mobile has been registered");
        }else if($type == 2){
            if ($user != 1)
                throw new UserNotExistException("mobile not Exist");
        }

        //TODO 测试阶段不发送验证码
//        if ($this->smsService->sendSms($mobile,self::MESSAGE.$verifyCode) != "0")
//            throw new VerifyCodeSendException();

         return $this->updateVerifyCode($mobile,$type,$verifyCode);

    }
    public function updateVerifyCode(string $mobile, int $type, string $code): string
    {

       $verify = $this->verifyCodeRepository->getByMult(['mobile'=>$mobile,'type'=>$type])->first();

       if ($verify == null){
         $newVerify = [
             'mobile'=>$mobile,
             'type'=>$type,
             'code'=>$code,
             'expires_at'=>Utils::createTimeStamp()+300000,
             'updated_at'=>Utils::createTimeStamp()
         ];

         $this->verifyCodeRepository->insert($newVerify);
       }
       else{
           $this->verifyCodeRepository->updateWhere(['mobile'=>$mobile,'type'=>$type],['code'=> $code,'updated_at'=>Utils::createTimeStamp(),'expires_at'=>Utils::createTimeStamp()+300000]);
       }

       return $code;
    }

    function checkVerifyCode(string $mobile, int $type, string $code): bool
    {

        $verifyCode = $this->verifyCodeRepository->getByMult(['mobile'=>$mobile,'type'=>$type,'code'=>$code])->first();

        if ($verifyCode == null)
            throw new VerifyCodeErrorException();
        //dd($verifyCode);
        if ($verifyCode['expires_at'] < Utils::createTimeStamp())
            throw new VerifyCodeTimeOutException();

        return true;
    }
}