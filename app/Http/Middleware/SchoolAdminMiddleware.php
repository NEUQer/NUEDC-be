<?php
/**
 * Created by PhpStorm.
 * User: Hotown
 * Date: 17/7/13
 * Time: 下午4:14
 */

namespace App\Http\Middleware;

use App\Exceptions\Permission\PermissionDeniedException;
use App\Services\RoleService;
use Closure;

class SchoolAdminMiddleware
{
    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user;
        if (!$this->roleService->hasRole($user['id'],'school_admin')) {
            throw new PermissionDeniedException();
        } else {
            return $next($request);
        }
    }
}