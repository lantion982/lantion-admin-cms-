<?php
//API 地区限制，未启用
namespace App\Http\Middleware;

use App\Libs\Helper;
use Illuminate\Support\Facades\Redis;
use Closure;

class IndexMiddleware{

    public function handle($request,Closure $next){
        $ipaddr   = Helper::getClientIP();
        $is_white = Redis::SISMEMBER('api_white_set',$ipaddr);
        if(!$is_white){
            $ipBlackWhiteHost = Helper::getIpBlackWhiteHost($_SERVER['HTTP_HOST'],$ipaddr,'api','white');
            if(empty($ipBlackWhiteHost)){
                $res = \GeoIP::getLocation($ipaddr);
                $addrCountry = $res['country'];
                $country = "|台湾|菲律宾|香港|澳门|韩国|日本|马来西亚|越南|缅甸|新加坡|";
                if(strpos($country,$addrCountry)){
                    return response()->json(['status' => -999,'msg' => '对不起，暂未开放此区域地区!!!']);
                }else{
                    Redis::SADD('api_white_set',$ipaddr);
                }
            }
        }
        return $next($request);
    }
}
