<?php
//权限中间键
namespace App\Http\Middleware;

use App\Libs\Helper;
use App\Models\Permission;
use Route,URL;
use Closure;
use Illuminate\Support\Facades\Auth;
use RBAC;

class EntrustAuthorize{

    public function handle($request,Closure $next,$guard = 'admin'){
        if(!Auth::guard($guard)->check()){
            return redirect('/manager/login');
        }
        $routeName = Route::currentRouteName();
        if(Helper::isAdmin() && $this->authType($routeName)){
            return $next($request);
        }
        if($routeName == 'dashboard'){
            return $next($request);
        }

        $previousUrl = URL::previous();

        if($routeName && !Auth::guard($guard)->user()->can($routeName)){
            if($request->ajax() && ($request->getMethod() != 'GET')){
                return response()->json([
                    'status' => -1,
                    'code'   => 403,
                    'msg'    => '您没有权限执行此操作'
                ]);
            }else{
                return response()->view('manager.errors.403',compact('previousUrl'));
            }
        }

        $response = $next($request);
        return $response;
    }

    public function authType($routeName){
        if(empty($routeName) || $routeName == 'dashboard'){
            return true;
        }
        $permission = Permission::where('name',$routeName)->first();
        if(empty($permission->auth_type)){
            return true;
        }
        if($permission->auth_type === 'common'){
            return true;
        }
        $isSuper = Helper::isSuper();
        if($permission->auth_type === 'company' && !$isSuper){
            return true;
        }elseif($permission->auth_type === 'super' && $isSuper){
            return true;
        }else{
            return false;
        }
    }
}
