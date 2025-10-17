<?php
/*
|--------------------------------------------------------------------------
| 记录相关API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use App\Models\PayOrder;
use App\Models\GameBet;
use App\Models\Member;
use App\Models\MoneyLog;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class RecordController extends BaseController{
	protected $memberId;
	protected $member;

	public function __construct(){
		$this->middleware(function($request,$next){
			$this->member = $request->user();
			$this->memberId = $this->member->member_id;
			if($this->member){
				return $next($request);
			}else{
				return response()->json(['status'=>FAILED,'msg'=>'无法获取您的账户信息，请重新登录！']);
			}
		});
	}

	//0.金额变动明细
	public function getMvmtList(Request $request){
		$cond      = $request->except(['_token','page']);
		$page      = intval($request->input('page',1));
		$pageSize  = intval($request->input('pageSize',10));
		$offset    = $pageSize*($page-1);
		$totals    = $pageSum = $allSum = 0;
		$moveType  = config('enums.move_type');
		$list      = MoneyLog::where(['member_id'=>$this->memberId])->where(function($query) use($cond){
			if (array_key_exists('start_time',$cond)&&$cond['start_time']!=''){
				$query->where('created_at','>=',$cond['start_time']);
			}
			if (array_key_exists('end_time',$cond)&&$cond['end_time']!=''){
				$query->where('created_at','<=',$cond['end_time']);
			}
			if (array_key_exists('move_type',$cond)&&$cond['move_type']!='all'){
				$query->where('move_type',$cond['move_type']);
			}
		})->orderBy('created_at','desc')->orderBy('sorts','desc')->limit($pageSize)->offset($offset)->get();
		$totals    = MoneyLog::where(['member_id'=>$this->memberId])->where(function($query) use($cond){
			if (array_key_exists('start_time',$cond)&&$cond['start_time']!=''){
				$query->where('created_at','>=',$cond['start_time']);
			}
			if (array_key_exists('end_time',$cond)&&$cond['end_time']!=''){
				$query->where('created_at','<=',$cond['end_time']);
			}
			if (array_key_exists('move_type',$cond)&&$cond['move_type']!='all'){
				$query->where('move_type',$cond['move_type']);
			}
		})->count();
		$allSum    = MoneyLog::where(['member_id'=>$this->memberId])->where(function($query) use($cond){
			if (array_key_exists('start_time',$cond)&&$cond['start_time']!=''){
				$query->where('created_at','>=',$cond['start_time']);
			}
			if (array_key_exists('end_time',$cond)&&$cond['end_time']!=''){
				$query->where('created_at','<=',$cond['end_time']);
			}
			if (array_key_exists('move_type',$cond)&&$cond['move_type']!='all'){
				$query->where('move_type',$cond['move_type']);
			}
		})->sum('money_change');
		$pageSum   = $list->sum('money_change');
		foreach($list as $val){
			$val->sort_date = substr($val->created_at,5,11);
		}
		$data['listItem'] = $list;
		$data['moveType'] = $moveType;
		$data['totals']   = $totals;
		$data['allSum']   = mynumber($allSum);
		$data['pageSum']  = mynumber($pageSum);;
		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'success']);
	}
}
