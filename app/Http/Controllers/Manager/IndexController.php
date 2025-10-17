<?php

namespace App\Http\Controllers\Manager;

use App\Models\PayOrder;
use App\Models\Member;
use Illuminate\Http\Request;

class IndexController extends Controller{
    public function __construct(){
        parent::__construct();
    }

    //后台首页
    public function index(){
        return view('manager.index');
    }
	
    //我的桌面
    public function dashboard(Request $request){
        $day = date("Y-m-d",time());
        $members   = Member::all();
        $userCount = $members->count();
        $todayUserCount = $members->filter(function($v,$k) use ($day){
            return $v->register_time>=$day;
        })->count();
        $todayDeposit  = 3600;
        $todayDraw     = 9800;
        $todayTransfer = 6320;
        $active_member_count = 0;

        $employee = Auth('admin')->user();
        $dateid   = $request->input('dateid',0);
        $quota_amount = 9980000000;
        $remain_amount = 9980000000;
        $regain_amount = 9980000000;

        $_data["dateid"]    = $dateid;
        $_data["employee"]  = $employee;
        $_data["userCount"] = $userCount;
        $_data["todayDeposit"]  = $todayDeposit;
        $_data["todayDraw"]     = $todayDraw;
        $_data["todayTransfer"] = $todayTransfer;
        $_data["todayUserCount"]    = $todayUserCount;
        $_data['activeMemberCount'] = $active_member_count;
        $_data["quota_amount"]  = $quota_amount;
        $_data["remain_amount"] = $remain_amount;
        $_data["regain_amount"] = $regain_amount;

        return view('manager.mydesktop',$_data);
    }

    //定时查询存取款订单
    public function checkInfo(){
        $lists  = PayOrder::where('pay_status','apply')->orderBy('created_at','desc')->get();
        $_count = $lists->count();
        $htmls  = '';
	    return null;
    }
    
    //导出用户信息
    /*public function userexc(Request $request){
		$list = Member::where('first_deposit_time','<>','')->where('company_id','company_default')
			->orderBy('member_level_id','desc')->paginate(500);
		foreach($list as $var){
			$member_id = $var->member_id;
			//$dictuid   = $var->dictuid;
			$bankmoney = DepositApply::where('member_id',$member_id)
				->orderBy('created_at','desc')->first();
			
			if($bankmoney){
				$var->lastdes   = $bankmoney->created_at;
				$var->lastmoney = $bankmoney->deposit_money;
			}else{
				$var->lastdes   = '';
				$var->lastmoney = 0;
			}
		}
		return view('manager.userexc',compact('list'));
	}*/
 
}
