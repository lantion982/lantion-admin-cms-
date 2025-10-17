<?php
//IP白名单检测
namespace App\Http\Middleware;

use App\Libs\Helper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class IndexWebMiddleware{
    public function handle($request,Closure $next){
        //开发模式直接放行
        if(config('app.env') == 'local'){
            return $next($request);
        }
        $ipAddr = Helper::getClientIP();
        //管理网只考虑白名单
        try{
            $ipBlackWhiteHost = Helper::getIpBlackWhiteHost($_SERVER['HTTP_HOST'],$ipAddr,'web','white');
            if(!$ipBlackWhiteHost){
                echo '域名：' . $_SERVER['HTTP_HOST'] . '<br>';
                echo '地址：' . $ipAddr;
                echo "<span style='color:red;'>该IP和域名不允许访问！</span>";
                exit();
            }
        }catch(\Exception $e){
            Log::error('网络异常：'.$e->getMessage());
            $html = "<h1 style='font-size:32px;line-height:32px;'>网站维护中！给您带来不便，敬请谅解</h1>";
            echo $html;
            exit;
        }

        return $next($request);
    }

}
