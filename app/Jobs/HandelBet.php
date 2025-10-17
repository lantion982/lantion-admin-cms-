<?php
//投注队列
namespace App\Jobs;

use App\Libs\Helper;
use App\Models\GameBet;
use App\Models\Member;
use App\Models\QueueBet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use DB;

class HandelBet implements ShouldQueue{
	use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

	public $timeout = 60;       //定义队列执行超时时间,要比queue.php配置文件中的 retry_after 值小
	public $tries   = 2;        //最大失败次数，优先级高于命令行指定的数值
	private $queue_id;

	public function __construct($queue_id){
		$this->queue_id = $queue_id;
	}


	public function handle(){
		//队列执行开始
		Log::info('【投注队列】开始执行队列=》queue_id：'.$this->queue_id);
		$betQueue = QueueBet::where('id','=',$this->queue_id)->first();
		if(!$betQueue){
			Log::info('【投注队列】队列未找到，跳过=》queue_id：' . $this->queue_id);
			return false;
		}
		if($betQueue->queue_status !='queue_ready'){
			Log::info('【投注队列】队列已执行,跳过=》queue_id：'.$this->queue_id);
			return false;
		}
		$sign = '';
		$member_id = $betQueue->member_id;
		$member    = Member::where("member_id",$member_id)->first();
		$action_amount = $betQueue->action_amount;
		$action_rate   = $betQueue->action_rate;
		$game_id       = $betQueue->game_id;
		$zema_id       = $betQueue->zema_id;
		$agree_change  = $betQueue->agree_change;
		$pan_id        = $betQueue->action_pid;
		$group_code    = $betQueue->group_code;
		$action_key    = $betQueue->action_key;
		$bill_no       = $betQueue->bill_no;
		$bet_time      = date("Y-m-d H:i:s");
		$rateInfo      = getWateRate($group_code,$member_id,$game_id);
		$sigleMax      = $rateInfo['sigleMax']??SINGLE_MAX;
		$gameMax       = $rateInfo['gameMax']??GAME_MAX;
		$gameBetSum    = getSumMoney($member_id,$game_id,$betQueue->action_number);
		$data['bill_no']       = $bill_no;
		$data['queue_id']      = $this->queue_id;
		$data['member_id']     = $member_id;
		$data['login_name']    = $member->login_name;
		$data['nick_name']     = $member->nick_name;
		$data['game_id']       = $game_id;
		$data['zema_id']       = $zema_id;
		$data['group_code']    = $betQueue->group_code;
		$data['group_name']    = $betQueue->group_name;
		$data['action_amount'] = $action_amount;
		$data['action_number'] = $betQueue->action_number;
		$data['action_pid']    = $pan_id;
		$data['action_rate']   = $action_rate;
		$data['action_data']   = $betQueue->action_data;
		$data['action_key']    = $action_key;
		$data['action_info']   = $betQueue->action_info;
		$data['action_ip']     = $betQueue->action_ip;
		$data['bet_flag']      = $betQueue->bet_flag;
		$data['water_rate']    = getWaterRateByPid($pan_id,$rateInfo);
		$data['water_amount']  = $action_amount*$data['water_rate']/100;
		$data['is_mobile']     = $betQueue->is_mobile;
		$data['created_at']    = $bet_time;
		$data['updated_at']    = $bet_time;
		$signData      = "bill_no={$bill_no}&group_code={$group_code}&game_id={$game_id}&amount={$action_amount}&rate={$action_rate}";
		$signData      = $signData."&key={$action_key}&data={$betQueue->action_data}&bet_time={$bet_time}";
		$sign          = base64_encode(hash_hmac('sha1',$signData,BET_KEY,true));
		$data['sign']  = $sign;
		if($group_code=='LianWei'||$group_code=='LianMa'||$group_code=='LianXiao'||$group_code=='ZXBZ'){
			$realRate = $action_rate;
		}else{
			$realRate = getRateByKey($game_id,$pan_id,$group_code,$action_key,$zema_id);
		}
		$betQueue->queue_status  = 'queue_succeed';
		if($action_amount>$sigleMax){
			$betQueue->remarks = '单注投注额超过限额，投注额：'.$action_amount.'||单注限额：'.$sigleMax;
			$betQueue->save();
			log::info('【投注队列】单注投注额超过限额=》投注额：'.$action_amount.'||限额：'.$sigleMax);
			$data['bet_flag']  = BET_LIMIT_SIG;                //单注超限=》投注失败
			$data['remarks']   = '单注注额超过限额，投注失败！';
			$res = GameBet::create($data);
			return false;
		}
		if(($gameBetSum+$action_amount)>$gameMax){
			$betQueue->remarks = '单期注额超过限额，本期已投：'.$gameBetSum.'||投注额：'.$action_amount.'||限额：'.$gameMax;
			$betQueue->save();
			log::info('【投注队列】单期注额超过限额，本期已投：'.$gameBetSum.'||投注额：'.$action_amount.'||限额：'.$gameMax);
			$data['bet_flag']  = BET_LIMIT_MAX;                //单注超限=》投注失败
			$data['remarks']   = '单期注额超过限额，投注失败！';
			$res = GameBet::create($data);
			return false;
		}
		if($realRate!=$action_rate && $agree_change!=1){
			$betQueue->remarks = '赔率已变动=》新：'.$realRate.'||投：'.$action_rate.'，投注失败！';
			$betQueue->save();
			log::info('【投注队列】赔率已变动=》新：'.$realRate.'||投：'.$action_rate);

			$data['bet_flag']  = BET_FAILE_RATE;               //赔率变更=》投注失败
			$data['remarks']   = '赔率已变动，投注失败！';
			$res = GameBet::create($data);                     //投注失败，也加一条投注明细记录,标记为失败：6，
			return false;
		}elseif($realRate!=$action_rate&&$agree_change==1){
			$data['action_rate']  = $realRate;
			$data['remarks']      = '赔率已变动=》新：'.$realRate.'||投：'.$action_rate;
		}
		if($realRate!=$action_rate){
			$signData      = "bill_no={$bill_no}&group_code={$group_code}&game_id={$game_id}&amount={$action_amount}&rate={$realRate}";
			$signData      = $signData."&key={$action_key}&data={$betQueue->action_data}&bet_time={$bet_time}";
			$sign          = base64_encode(hash_hmac('sha1',$signData,BET_KEY,true));
			$data['sign']  = $sign;
		}
		if($member->balance<$action_amount){
			$betQueue->remarks = '会员帐户余额不足=》余额：'.$member->balance.'||投注：'.$action_amount.'，投注失败！';
			$betQueue->save();

			$data['bet_flag']  = BET_FAILE_MONEY;             //余额不足=》投注失败
			$data['remarks']   = '帐户余额不足，投注失败！';
			log::info('【投注队列】帐户余额不足=》余额：'.$member->balance.'||投注：'.$action_amount);
			$res = GameBet::create($data);                    //投注失败，也加一条投注明细记录,标记为失败：7，
			return false;
		}
		DB::beginTransaction();
		try{
			$result = Helper::upMoney($member_id,$action_amount,'bet_money','投注扣除',$bill_no);       //扣除投注金额，记录会员金额变动明细
			if(!$result){
				DB::rollBack();
				$betQueue->remarks = '扣除金额失败！';
				$betQueue->save();
				$data['bet_flag']  = BET_FAILE_PAY;             //扣款错误=》投注失败
				$data['remarks']   = '扣除投注金额失败=》投注失败！';
				log::info('【投注队列】扣除投注金额失败！');
				$res = GameBet::create($data);                  //投注失败，也加一条投注明细记录,标记为失败：8，
				return false;
			}

			$betQueue->queue_status = 'queue_succeed';
			$betQueue->remarks      = '投注成功！';
			$betQueue->save();
			$data['bet_flag'] = BET_SUCCESS;                    //未结算=》投注成功
			$res = GameBet::create($data);                      //创建投注明细
			if($res){
				DB::commit();
			}
		}catch(\Exception $ex){
			Log::info('【投注队列】执行异常=》：'.$ex->getMessage());
			DB::rollBack();
			return false;
		}
	}

	public function failed(){
		$betQueue = QueueBet::where('id','=',$this->queue_id)->first();
		$betQueue->queue_status = 'queue_faile';
		$betQueue->remarks      = '投注失败！';
		$betQueue->save();
		Log::info('【投注队列】执行失败=>queue_id：'.$this->queue_id);
	}
}
