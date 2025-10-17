<?php
//结算队列
namespace App\Jobs;

use App\Libs\Helper;
use App\Models\GameBet;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use DB;

class HandelSettle implements ShouldQueue{
	use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

	public $timeout = 60;       //定义队列执行超时时间,要比queue.php配置文件中的 retry_after 值小
	public $tries   = 2;        //最大失败次数，优先级高于命令行指定的数值
	private $betId;

	public function __construct($betId){
		$this->betId = $betId;
	}


	public function handle(){
		//队列执行开始
		Log::info('【结算队列】开始执行队列=》ID：'.$this->betId);
		$betInfo = GameBet::where('bet_id',$this->betId)->first();
		if(!$betInfo){
			Log::info('【队列结算失败】注单未找到，跳过=》ID：' . $this->betId);
			return false;
		}
		if($betInfo->bet_flag!=BET_SUCCESS&&$betInfo->bet_flag!=BET_FAILE_SETT){
			Log::info('【队列结算失败】注单已结算，跳过=》ID：' . $this->betId);
			return false;
		}
		/*if(!betSingn($betInfo)){
			Log::info('【队列结算失败】注单验签失败=》ID：' . $this->betId);
			return false;
		}*/

		DB::beginTransaction();
		try{
			$winLost = lhsSettle($this->betId);
			$res     = $water_res = $win_res = true;
			if($winLost==-1){
				$data['bet_flag'] = BET_SETT_ZERO;
				$res = $betInfo->update($data);
				$water_res = true;
			}elseif($winLost==0){
				$data['bet_flag']  = BET_SETT_LOST;
				$data['win_bonus'] = 0-$betInfo->action_amount;
				$res = $betInfo->update($data);
				$water_amount = $betInfo->water_amount;
				$member_id    = $betInfo->member_id;
				$bill_no      = $betInfo->bill_no;
				$water_res    = Helper::upMoney($member_id,$water_amount,'water_settle','结算返水',$bill_no);       //返水结算，记录金额变动明细
			}else{
				$data['bet_flag']  = BET_SETT_WIN;
				$data['win_bonus'] = ($betInfo->action_amount*$betInfo->action_rate)*$winLost;
				$res = $betInfo->update($data);
				$water_amount = $betInfo->water_amount;
				$member_id    = $betInfo->member_id;
				$bill_no      = $betInfo->bill_no;
				$water_res    = Helper::upMoney($member_id,$water_amount,'water_settle','结算返水',$bill_no);       //返水结算，记录金额变动明细
				$win_res      = Helper::upMoney($member_id,$data['win_bonus'],'win_money','中奖结算',$bill_no);     //中奖结算，记录金额变动明细
			}
			if($res&&$water_res&&$win_res){
				DB::commit();
			}
		}catch(\Exception $ex){
			Log::info('【结算队列】执行异常=》：'.$ex->getMessage());
			DB::rollBack();
			return false;
		}
	}

	public function failed(){
		$betInfo = GameBet::where('bet_id',$this->betId)->first();
		$betInfo->bet_flag = BET_FAILE_SETT;
		$betInfo->save();
		Log::info('【结算队列队列】执行失败=>bet_id：'.$this->betId);
	}
}
