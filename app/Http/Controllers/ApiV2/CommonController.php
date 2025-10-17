<?php
/*
|--------------------------------------------------------------------------
| 基础公用API
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\ApiV2;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class CommonController extends BaseController{

    //1.获取新闻公告信息
    public function getNews(Request $request){
        $newsType = strip_tags($request->get('newsType',''));
        $page     = intval($request->get('page',0));
        $pagesize = intval($request->get('pagesize',20));

        if ($newsType == '') {
            return response()->json(['status' => 1003,'content' => '','msg' => '未指定新闻公告类型！']);
        }
        $newsList = News::where('is_show',1)->orderBy('sorts','asc')->offset($page)->limit($pagesize)->get();
        return response()->json(['status'=>SUCCESS,'content'=>['data'=>$newsList,'msg'=>'success']]);
    }

    //2.获取用户IP
    public function getUserIp(){
        $IPaddress = getip();
        return response(['status' => SUCCESS,'content' => $IPaddress,'msg' => 'Success!']);
    }
}
