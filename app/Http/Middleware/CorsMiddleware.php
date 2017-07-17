<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With, Content-Disposition');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, DELETE, PATCH, PUT, OPTIONS');
        $response->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
