<?php
//后台登录验证中间键
namespace App\Http\Middleware;

use Closure;

class AdminAuthMiddleware{
    public function handle($request,Closure $next,$guard = 'admin'){
        if(\Auth::guard($guard)->guest()){
            if($request->ajax() || $request->wantsJson()){
                return response('Unauthorized.',401);
            }else{
                return redirect()->guest('manager/login');
            }
        }
        return $next($request);
    }
}
