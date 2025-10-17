<?php
//代理登录验证中间键
namespace App\Http\Middleware;

use Closure;

class AgentAuthMiddleware{

    public function handle($request,Closure $next,$guard = 'agent'){
        if(\Auth::guard($guard)->guest()){
            if($request->ajax() || $request->wantsJson()){
                return response('Unauthorized.',401);
            }else{
                return redirect()->guest('agent/login');
            }
        }
        return $next($request);
    }
}
