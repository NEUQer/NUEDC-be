<?php

namespace App\Http\Controllers;

use App\Common\Utils;
use App\Common\ValidationHelper;
use App\Exceptions\Common\MobileIllegalIException;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Services\ContestService;
use App\Services\PermissionService;
use App\Services\PrivilegeService;
use App\Services\SmsService;
use App\Services\TokenService;
use App\Services\UserService;
use App\Services\VerifyCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 基本权限检查交给中间件
     */
    private $userService;

    private $verifyCodeService;

    private $tokenService;

    private $permissionService;

    private $contestService;


    public function __construct(UserService $userService,VerifyCodeService $verifyCodeService,TokenService $tokenService,PermissionService $permissionService,ContestService $contestService)
    {
        $this->userService = $userService;
        $this->verifyCodeService = $verifyCodeService;
        $this->tokenService = $tokenService;
        $this->permissionService = $permissionService;
        $this->contestService = $contestService;
    }


    public function perRegister(Request $request){
        $rules = [
            'mobile'=>'required|mobile|max:100'
        ];

        ValidationHelper::validateCheck($request->all(),$rules);

        $data = ValidationHelper::getInputData($request,$rules);


        $verifyCode = $this->verifyCodeService->sendVerifyCode($data['mobile'],1);

        return response()->json(
            [
                'code'=> 0,
                'data'=>[
                    'verifyCode' => $verifyCode
                ]
            ]
        );

    }
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'mobile' => 'required|mobile|max:45',
            'password' => 'required|min:6|max:20',
            'sex'=>'required|max:4',
            'schoolId'=>'required',
            'code'=>'required|max:4',
            'schoolName'=>'required'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $userInfo = ValidationHelper::getInputData($request, $rules);


        $userId = $this->userService->register($userInfo);

        return response()->json([
            'code' => 0,
            'data' => [
                'user_id' => $userId
            ]
        ]);
    }

    public function login(Request $request)
    {
        $rules = [
            'identifier' => 'required|string',
            'password' => 'required|min:6|max:20',
            'client' => 'required|min:1|max:2' // 登录设备标识符
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        // 在此定制登录方式

        $identifier = $request->identifier;

        if (Utils::isEmail($identifier)) {
            $loginMethod = 'email';
        } else if (Utils::isMobile($identifier)) {
            $loginMethod = 'mobile';
        } else {
            $loginMethod = 'name';
        }

        $data = $this->userService
            ->loginBy($loginMethod, $identifier, $request->password, $request->ip(),$request->client);

        // 在下面定制要取出的字段

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function logout(Request $request)
    {

        $this->tokenService->destoryToken($request->user->id,$request['client']);

        return response()->json([
            'code' => 0
        ]);
    }

    public function index(Request $request,PrivilegeService $privilegeService){
        return response()->json(
            [
                'code' => 0,
                'data'=>[
                    'privileges'=>$privilegeService->getUserPrivileges($request->user->id)
                ]
            ]
        );
    }

    public function getAllContest(){

        $data = $this->contestService->getAllContest();

        return response()->json(
            [
                'code'=>0,
                'data'=>$data
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 报名和修改报名的统计接口
     */
    public function signUpContest(Request $request){


        $rules = [
            'teamName'=>'required',
            'schoolId'=>'required|integer',
            'schoolName'=>'required',
            'contestId'=>'required|integer',
            'schoolLevel'=>'required',
            'member1'=>'required',
            'member2'=>'required',
            'member3'=>'required',
            'teacher'=>'required',
            'mobile'=>'required|max:45',
            'email'=>'required|max:100',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $signInfo = ValidationHelper::getInputData($request,$rules);

        return response()->json(
            [
                'code'=>0,
                'data'=> $this->contestService->updateSignUpContest($request->user->id,$signInfo)
            ]
        );
    }

    public function getContestSignUpStatus(Request $request,int $contestId){

        return response()->json(
            [
                'code'=> 0,
                'data'=> $this->contestService->getContestSignUpStatus($request->user->id,$contestId)
            ]
        );
    }


    public function abandonContest(Request $request,int $contestId){

        return response()->json(
            [
                'code'=> 0,
                'data'=> $this->contestService->abandonContest($request->user->id,$contestId)
            ]
        );
    }

    public function getAllPassContest(Request $request){


        return response()->json(
            [
                'code'=>0,
                'data'=>[
                    'contestList'=>$this->contestService->getAllPassContestList($request->user->id)
                ]
            ]
        );
    }

    public function getContestProblemList(Request $request,int $contestId){

        if (!$this->permissionService->checkPermission($request->user->id,['sign_up_contest']))
            throw new PermissionDeniedException();

        return response()->json(
            [
                'code'=>0,
                'data'=> $this->contestService->getContestProblemList($contestId,$request->user->id)
            ]


        );

    }

    public function getContestProblemDetail(Request $request){

        $rules = [
           'contestId'=>'required',
            'problemId'=>'required',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $key = ValidationHelper::getInputData($request,$rules);

        return response()->json(
            [
                'code'=>0,
                'data'=>$this->contestService->getProblemDetail($request->user->id,$key)
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 无论是否为初次选择都只要更新
     */
    public function updateContestProblemSelect(Request $request){
        $rules = [
            'contestId'=>'required',
            'problemId'=>'required',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $key = ValidationHelper::getInputData($request,$rules);
        if ($this->contestService->updateProblemSelect($request->user->id,$key) < 1)
            throw new UnknownException("选题更新失败");

        return response()->json([
                'code'=>0
            ]);

    }

    public function getContestResultStatus(Request $request,int $contestId){
        return response()->json(
            [
                'code'=>0,
                'data'=>$this->contestService->getContestResult($request->user->id,$contestId)
            ]
        );
    }
}
