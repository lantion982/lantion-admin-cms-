<?php
//单点登录
namespace App\Http\Middleware;

use Route,URL;
use Closure;
use Redis;
use Auth;

class SingleMiddleware{

    public function handle($request,Closure $next,$guard = null){
        $previousUrl = URL::previous();
        $member      = Auth::guard($guard)->user();
        $singleToken = $request->headers->get('singleToken');
        if($singleToken){
            $redis   = \RedisServer::connection();
            $redisTime = $redis->get('STRING_SINGLETOKEN_' . $member->member_id);
            $ip     = $request->getClientIp();
            $secret = md5($ip . $member->member_id . $redisTime);
            if($singleToken != $secret){
                if($request->ajax()){
                    return response()->json([
                        'status' => -1,
                        'code'   => 401,
                        'msg'    => '您的帐号在另一个地点登录..'
                    ]);
                }else{
                    return response()->view('api_web.errors.401',compact('previousUrl'));
                }
            }
            return $next($request);
        }else{
            return $next($request);
        }
    }
}
