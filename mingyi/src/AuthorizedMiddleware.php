<?php

namespace Mingyi\Common;

use Closure;

class AuthorizedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 检查是否登录
        if (!app('user')->authorized()) {
            abort(401);
        }
        return $next($request);
    }
}
