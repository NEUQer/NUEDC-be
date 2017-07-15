<?php
/**
 * Created by PhpStorm.
 * User: yinzhe
 * Date: 17/7/15
 * Time: 上午12:47
 */

namespace App\Http\Middleware;

use App\Common\Utils;
use App\Exceptions\Auth\NeedLoginException;
use App\Exceptions\Auth\TokenExpiredException;
use App\Exceptions\Auth\UserNotActivatedException;
use App\Exceptions\Permission\PermissionDeniedException;
use App\Repository\Eloquent\TokenRepository;
use App\Repository\Eloquent\UserRepository;
use App\Services\PermissionService;
use Closure;
class AuthMiddleware
{

    /**
     * 在中间件中检查是否能管理权限
     */

    protected $userRepository;
    protected $tokenRepository;
    protected $permissionService;

    public function __construct(UserRepository $ur, TokenRepository $tr,PermissionService $permissionService)
    {
        $this->userRepository = $ur;
        $this->tokenRepository = $tr;
        $this->permissionService = $permissionService;
    }

    public function handle($request, Closure $next)
    {

        $time = Utils::createTimeStamp();

        if(!$request->hasHeader('token'))
            throw new NeedLoginException();

        $tokenStr = $request->header('token');

        $token = $this->tokenRepository->getBy('token',$tokenStr)->first();

        if($token === null)
            throw new NeedLoginException();

        if($token->expires_at < $time)
            throw new TokenExpiredException();
        $user = $this->userRepository->get($token->user_id)->first();

        if (config('user.register_need_check')) {
            if($user->status == 0)
                throw new UserNotActivatedException();
        }

        $request->user = $user;

        if (!$this->permissionService->checkPermission($user->id,['manage_privilege']))
            throw new PermissionDeniedException();

        return $next($request);
    }
}