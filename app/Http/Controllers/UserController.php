<?php

namespace App\Http\Controllers;

use App\Common\Utils;
use App\Common\ValidationHelper;
use App\Exceptions\Common\MobileIllegalIException;
use App\Exceptions\Common\UnknownException;
use App\Exceptions\Contest\ProblemSelectTimeException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Repository\Models\Token;
use App\Services\ContestService;
use App\Services\PermissionService;
use App\Services\PrivilegeService;
use App\Services\ProblemService;
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

    private $problemService;


    public function __construct(
        UserService $userService, VerifyCodeService $verifyCodeService,
        TokenService $tokenService, PermissionService $permissionService,
        ContestService $contestService, ProblemService $problemService
    )
    {
        $this->userService = $userService;
        $this->verifyCodeService = $verifyCodeService;
        $this->tokenService = $tokenService;
        $this->permissionService = $permissionService;
        $this->contestService = $contestService;
        $this->problemService = $problemService;
    }

    public function getSchools(Request $request)
    {
        ValidationHelper::validateCheck($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1'
        ]);

        $page = $request->input('page', 1);
        $size = $request->input('size', -1);

        return response()->json([
            'code' => 0,
            'data' => $this->userService->getSchools($page, $size)
        ]);

    }

    public function getProblemAttach(Request $request, TokenService $tokenService, int $problemId)
    {
        $input = ValidationHelper::checkAndGet($request, [
            'token' => 'required|string',
            'download' => 'boolean'
        ]);

        $userId = $tokenService->getUserIdByToken($input['token']);
        $download = $request->input('download', false);

        // 首先检查用户是否参加了对应的比赛
        if (!$this->problemService->canUserAccessProblem($userId, $problemId)) {
            throw new ProblemSelectTimeException();
        }

        $problem = $this->problemService->getProblem($problemId, ['id', 'title', 'attach_path']);

        if ($problem->attach_path === null) {
            throw new UnknownException("no attachment to download!");
        }

        $path = storage_path('app/private/' . $problem->attach_path);

        $corsHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PATCH, PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ];

        if ($download) {
            return response()->download($path, null, $corsHeaders);
        }

        return response()->file($path, $corsHeaders);
    }

    public function perRegister(Request $request)
    {
        $rules = [
            'mobile' => 'required|mobile|max:100'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $data = ValidationHelper::getInputData($request, $rules);


        $this->verifyCodeService->sendVerifyCode($data['mobile'], 1);

        return response()->json(
            [
                'code' => 0,
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
            'sex' => 'required|max:4',
            'schoolId' => 'required',
            'code' => 'required|max:4',
            'schoolName' => 'required'
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
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        // 在此定制登录方式

        $identifier = $request->identifier;

//        if (Utils::isEmail($identifier)) {
//            $loginMethod = 'email';
//        } else if (Utils::isMobile($identifier)) {
//            $loginMethod = 'mobile';
//        } else {
//            $loginMethod = 'name';
//        }

        $data = $this->userService
            ->loginBy('mobile', $identifier, $request->password, $request->ip(), 1);

        // 在下面定制要取出的字段

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function logout(Request $request)
    {

        $this->tokenService->destoryToken($request->user->id, 1);

        return response()->json([
            'code' => 0
        ]);
    }

    public function updateUserPassword(Request $request)
    {
        $rules = [
            'oldPassword' => 'required|min:6|max:20',
            'newPassword' => 'required|min:6|max:20',
        ];

        $userPwd = ValidationHelper::checkAndGet($request, $rules);

        $userInfo = [
            'userId' => $request->user->id,
            'password' => $userPwd['oldPassword'],
            'newPassword' => $userPwd['newPassword']
        ];

        $this->userService->updateUserPassword($userInfo);

        return response()->json(
            ['code' => 0]
        );
    }

    public function getAllContest()
    {
        $data = $this->contestService->getAllContest();

        return response()->json(
            [
                'code' => 0,
                'data' => $data
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 报名和修改报名的统计接口
     */
    public function signUpContest(Request $request)
    {
        $rules = [
            'teamName' => 'required',
            'schoolId' => 'required|integer',
            'schoolName' => 'required',
            'contestId' => 'required|integer',
            'schoolLevel' => 'required',
            'member1' => 'required',
            'member2' => 'required',
            'member3' => 'required',
            'teacher' => 'required',
            'mobile' => 'required|max:45',
            'email' => 'required|max:100',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $signInfo = ValidationHelper::getInputData($request, $rules);

        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->updateSignUpContest($request->user->id, $signInfo)
            ]
        );
    }

    public function getContestSignUpStatus(Request $request, int $contestId)
    {
        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->getContestSignUpStatus($request->user->id, $contestId)
            ]
        );
    }


    public function abandonContest(Request $request, int $contestId)
    {

        $this->contestService->abandonContest($request->user->id, $contestId);

        return response()->json(
            [
                'code' => 0
            ]
        );
    }

    public function getAllPassContest(Request $request)
    {
        return response()->json(
            [
                'code' => 0,
                'data' => [
                    'contestList' => $this->contestService->getAllPassContestList($request->user->id)
                ]
            ]
        );
    }

    public function getContestProblemList(Request $request, int $contestId)
    {

        if (!$this->permissionService->checkPermission($request->user->id, ['sign_up_contest']))
            throw new PermissionDeniedException();

        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->getContestProblemList($contestId, $request->user->id)
            ]
        );

    }


    public function getContestProblemDetail(Request $request)
    {

        $rules = [
            'contestId' => 'required',
            'problemId' => 'required',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $key = ValidationHelper::getInputData($request, $rules);

        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->getProblemDetail($request->user->id, $key)
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 无论是否为初次选择都只要更新
     */
    public function updateContestProblemSelect(Request $request)
    {
        $rules = [
            'contestId' => 'required',
            'problemId' => 'required',
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $key = ValidationHelper::getInputData($request, $rules);

        if ($this->contestService->updateProblemSelect($request->user->id, $key) < 1)
            throw new UnknownException("选题更新失败");

        return response()->json([
            'code' => 0
        ]);

    }

    public function getContestResultStatus(Request $request, int $contestId)
    {
        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->getContestResult($request->user->id, $contestId)
            ]
        );
    }

    public function getSignedUpContest(Request $request)
    {
        return response()->json(
            [
                'code' => 0,
                'data' => $this->contestService->getSignedUpContest($request->user->id)
            ]
        );
    }

    public function getVerifyCode(Request $request)
    {
        $rules = [
            'mobile' => 'required|mobile|max:100',
            'type' => 'required|min:1|max:2'
        ];

        ValidationHelper::validateCheck($request->all(), $rules);

        $data = ValidationHelper::getInputData($request, $rules);


        $verifyCode = $this->verifyCodeService->sendVerifyCode($data['mobile'], $data['type']);

        return response()->json(
            [
                'code' => 0,
                'data' => [
                    'verifyCode' => $verifyCode
                ]
            ]
        );
    }

    public function forgetPassword(Request $request)
    {
        $rules = [
            'mobile' => 'required|mobile|max:100',
            'code' => 'required',
            'newPassword' => 'required|min:6|max:20'
        ];
        $data = ValidationHelper::checkAndGet($request, $rules);

        $this->userService->forgetPassword($data['mobile'], $data['newPassword'], $data['code']);

        return response()->json(
            [
                'code' => 0
            ]
        );
    }
}
