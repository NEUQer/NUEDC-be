<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/11
 * Time: 下午11:54
 */

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;

class SmsService implements SmsServiceInterface
{

    //发送短信接口URL
    const API_SEND_URI = 'http://zapi.253.com/msg/HttpBatchSendSM';

    //SMSClient账号
    const API_ACCOUNT = '';

    //SMSClient密码
    const API_PASSWORD = '';

    /**
     * 发送短信
     * @param $mobile
     * @param $msg
     * @return mixed
     */
    public function sendSms(string $mobile, string $msg): string
    {
        //SMSClient接口参数
        $postArr = array(
            'account' => self::API_ACCOUNT,
            'pswd' => self::API_PASSWORD,
            'mobile' => $mobile,
            'msg' => $msg,
            'needstatus' => false
        );

        $result = $this->curlPost(self::API_SEND_URI, $postArr);

        return $this->execResult($result);
    }

    public function sendVerifyCode($mobile, $randStr)
    {
        $message = "您正在注册电子设计大赛系统，验证码是：$randStr.请注意保密，不要提供给他人使用。验证码五分钟内有效。";

        return $this->sendSms($mobile, $message);
    }

    public function forgetPassword($mobile, $randStr)
    {
        $message = "忘记密码，验证码是：$randStr.请注意保密，不要提供给他人使用。验证码五分钟内有效。";

        return $this->sendSms($mobile, $message);
    }

    /**
     * 处理返回值
     * @param string $result
     * @return string
     */
    public function execResult(string $result): string
    {
        $result = preg_split("/[,\r\n]/", $result);
        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param $url
     * @param $postFields
     * @return mixed
     */
    private function curlPost(string $url, array $postFields): string
    {
        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

//    public function __get($name)
//    {
//        return $this->$name;
//    }
//
//    public function __set($name, $value)
//    {
//        $this->$name = $value;
//    }
}

?>