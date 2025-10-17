<?php

namespace App\Http\Controllers\Manager;

use App\Libs\Helper;
use App\Libs\UserHelper;
use App\Models\BankAccount;
use App\Models\PayOrder;
use App\Models\AdminLog;
use App\Models\Member;
use App\Models\MemberRate;
use App\Models\Level;
use App\Models\MoneyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Auth;

class FinanceController extends Controller{
	protected $adminId;

	public function __construct(){
		parent::__construct();
		$this->middleware(function($request,$next){
			$this->adminId = Auth::guard('admin')->user()->id;
			return $next($request);
		});
	}

	//电子钱包
	public function memberAssets(Request $request){
		$data         = $request->except(['_token','page']);
		$page         = $request->input('page',1);
		$page_count   = $request->input('page_count',20);
		$startDate    = $data['startDate']??'';
		$endDate      = $data['endDate']??'';
		$totalColumns = ['balance'];
		$results = UserHelper::memberAccount($data,$page_count,$totalColumns);
		$members = $results['paginate'];
		unset($results['paginate']);
		$page_title['type']    = 'Search';
		$page_title['content'] = 'manager.finance.memberSearch';
		$level = Level::orderBy('level_code')->get(['id','level_name']);
		
		if($request->ajax()){
            return view('manager.finance.memberList',compact('members','results','page_title','level','page','page_count','startDate','endDate'));
		}else{
			return view('manager.finance.member',compact('members','results','page_title','level','page','page_count','startDate','endDate'));
		}
	}
	
	//额度管理
	public function balanceInfo(Request $request){
	    $id = intval($request->input('id',0));
		$moveType = ['admin_money_inc'=>'管理增加','admin_money_dec'=>'管理减少'];
		$members  = Member::where('id',$id)->first();
		if(empty($members)){
			return response()->json(['status'=>FAILED,'msg'=>'会员信息未找到！']);
		}
		return view('manager.finance.balanceInfo',compact('members','moveType'));
	}

	//更新金额
	public function updateMemberMoney(Request $request){
        $id = intval($request->input('id',0));
		$money     = $request->input('money',0);
		$moveType  = $request->input('move_type','');
		$remark    = $request->input('commit','');
		if(!is_numeric($money) || $money<=0){
			return response()->json(['status'=>FAILED,'msg'=>'请输入正确的金额！']);
		}
		if(!($request['commit'])){
			return response()->json(['status'=>FAILED,'msg'=>'请输入相关备注！']);
		}
		$memberAccount = Member::where('id',$id)->first();
		if(empty($memberAccount)){
			return response()->json(['status'=>FAILED,'msg'=>'会员信息未找到！','url'=>'/manager/memberAssets']);
		}
		//每项操作都需备注
		$data['commit_type'] = 'member_remark';
		$data['commit']      = $remark;
		$data['member_id']   = $id;
		$commit = Helper::saveAdminCommit($data);
		if(!$commit){
			return response()->json(['status'=>FAILED,'msg'=>'备注添加失败！']);
		}

		//调整金额
        $billNo = Helper::getBillNo('M');
		$result = Helper::upMoney($id,$money,$moveType,$remark,$billNo,$this->adminId);
		if($result){
			Helper::addAdminLog($this->adminId,'【修改会员金额】金额=》'.$money.'，ID=》'.$id.'原因=》'.$remark,'update');
		}
		return response()->json($result?['status'=>SUCCESS,'msg'=>'更新成功！']:['status'=>FAILED,'msg'=>'更新失败！']);
	}

	//金额明细
	public function moneyMovement(Request $request){
		$data = $request->except(['_token','page']);
		$page_count = $request->input('page_count',20);
        $page_title['type']     = 'Search';
        $page_title['content']  = 'manager.finance.moneySearch';
        $page = $request->input('page',1);
		if(array_key_exists('keyword',$data) && $data['keyword'] != ''){
			$data['member_id'] = ['null'];
			$ids = Member::where(function($query) use ($data){
                $query->orWhere('login_name','like','%'.trim($data['keyword']).'%');
                $query->orWhere('phone','like','%'.trim($data['keyword']).'%');
                $query->orWhere('nick_name','like','%'.trim($data['keyword']).'%');
                $query->orWhere('register_ip','like','%'.trim($data['keyword']).'%');
                $query->orWhere('register_domain','like','%'.trim($data['keyword']).'%');
			})->pluck('member_id')->toArray();
			if($ids){
				$data['member_id'] = $ids;
			}
		}
		if(!isset($data['startDate'])){
			$data['startDate'] = date('Y-01-01 00:00:00');
		}
		if(!isset($data['endDate'])){
			$data['endDate'] = date('Y-m-d 23:59:59');
		}
		$startDate = $data['startDate']??'';
		$endDate   = $data['endDate']??'';
		$keyword   = $data['keyword']??'';
        $billNo    = $data['billNo']??'';
		$totalCol  = ['money_change'];
		$results   = Helper::getMoneyLog($page_count,$data,$totalCol);
		$list = $results['paginate'];
		unset($results['paginate']);
		
		$move_type[''] = '所有类型';
		$move_type = array_merge($move_type,config('enums.move_type'));

		if($request->ajax()){
            return view('manager.finance.moneyMvmtList',compact('list','page_title','page','page_count','results','move_type',
                'startDate','endDate','keyword','billNo'));
		}else{
			return view('manager.finance.moneyMvmt',compact('list','page_title','page','page_count','results','move_type',
                'startDate','endDate','keyword','billNo'));
		}
	}
}
