<?php
//报表

namespace App\Services\Report\Report;

use App\Models\Agent;
use App\Models\Company;
use App\Models\CompanySettle;
use App\Models\DataDaily;
use App\Models\DataDailyGameType;
use App\Models\DataDailyRoom;
use App\Models\DataDailyRoomUser;
use App\Models\DataDailyRoomUserGtype;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MoneyLog;
use App\Models\uc_import_record_details;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaseReport{

	//检查报表是否锁定
	public function checkDailyRoomIsLock($begin_time,$end_time){

		$dataDailyRoom = DataDailyRoom::where([
			['calc_date','>=',$begin_time],['calc_date','<=',$end_time],['company_id','company_super']
		])->get();

		$room = $dataDailyRoom->filter(function($item,$key){
			return $item->is_locked == 0;
		})->isEmpty();
		if(!$room){
			return false;
		}
		return true;
	}

	//检查是否已经生成过报表
	public function checkHasReportRecord($date = null){
		$date  = $date?:date('Y-m-d');
		$rooms = Room::where(['is_active' => 1])->pluck('room_code');
		$ret   = DataDailyRoom::where(['calc_date' => $date,'company_id' => 'company_super']);
		if($ret->count() === count($rooms)){
			return true;
		}
		DataDailyRoom::where(['calc_date' => $date])->delete();
		$data = [
			'company_id'  => 'company_super',
			'bet_count'   => 0,
			'money_total' => 0,
			'money_use'   => 0,
			'win_lose'    => 0,
			'is_locked'   => 0,
			'calc_date'   => $date
		];
		DB::beginTransaction();
		foreach($rooms as $room){
			$data['room_code'] = $room;
			$ret = DataDailyRoom::create($data);
			if(!$ret){
				DB::rollBack();
				return false;
			}
		}
		DB::commit();
		return true;
	}

	//重建报表
	public function rebulidReport($data_daily_room_id){
		set_time_limit(0);
		
		$report = DataDailyRoom::find($data_daily_room_id);
		if(!$report){
			log::info('重建日报表：数据未找到=>'.$data_daily_room_id);
			return false;
		}
		$calc_date = $report->calc_date;
		$room_code = $report->room_code;
		log::info($calc_date.'重建日报表【'.$room_code.'】=>ID：'.$data_daily_room_id);
		$_start    = $calc_date .' 00:00:00';
		$_end      = $calc_date .' 23:59:59';
		log::info($calc_date.'重建日报表【'.$room_code.'】=>准备取数据');
		$allImports = uc_import_record_details::where('bet_time','>=',$_start)->where('bet_time','<=',$_end)->get();    //当日所有投注明细
		log::info($calc_date.'重建日报表【'.$room_code.'】=>取数据结束');
		$imports   = $allImports->where('room_code',$room_code);                                                        //当日游戏厅所有投注明细
		//$imports = uc_import_record_details::where('bet_time','>=',$_start)->where('bet_time','<=',$_end)
			//->where('room_code',$room_code)->get();
		log::info($calc_date.'重建日报表【'.$room_code.'】=>选数据结束');
		$room_game_type = Room::query()->where('room_code',$room_code)->value('game_type');
		$all_game_type  = config('enums.game_type_code');
		unset($all_game_type['all'],$all_game_type['Default']);
		$all_game_type = array_keys($all_game_type);
		$data = [
			'bet_count'   => $imports->count(),
			'money_total' => $imports->sum('bet_amount'),
			'money_use'   => $imports->sum('valid_bet_amount'),
			'win_lose'    => $imports->sum('net_amount'),
		];
		//log::info($data);
		//先删除已经存在报表
		try{
			log::info('重建日报表：step1=>删除旧日报表');
			DataDailyRoom::where([
				['data_daily_room_id','!=',$data_daily_room_id],['room_code','=',$room_code],['calc_date','=',$calc_date]
			])->delete();
			DataDailyRoomUser::where([['room_code','=',$room_code],['calc_date','=',$calc_date]])->delete();
			DataDailyRoomUserGtype::where([['room_code','=',$room_code],['calc_date','=',$calc_date]])->delete();
			DataDailyGameType::where(['calc_date' => $calc_date])->delete();
			DataDaily::where(['calc_date' => $calc_date])->delete();
		}catch(\Exception $e){
			Log::error($e->getMessage());
		}
		
		try{
			DB::beginTransaction();
			$result1 = $report->update($data);
			$result2 = DataDaily::create([
				'company_id'  => 'company_super',
				'calc_date'   => $calc_date,
				'bet_count'   => $allImports->count(),
				'money_total' => $allImports->sum('bet_amount'),
				'money_use'   => $allImports->sum('valid_bet_amount'),
				'win_lose'    => $allImports->sum('net_amount'),
			]);
			if(!$result1||!$result2){
				log::info('重建日报表：step1=>rollBack');
				DB::rollBack();
				
				return false;
			}
			//log::info('重建日报表：step2=>创建平台GameType=》');
			//生成平台会员游戏类型报表
			foreach($all_game_type as $item){
				log::info('重建日报表：step2=>创建平台GameType=》'.$item);
				$game_type_data = $allImports->where('game_type',$item);
				//$game_type_data = uc_import_record_details::where('bet_time','>=',$_start)->where('bet_time','<=',$_end)
					//->where('game_type',$item)->get();
				$ret5 = DataDailyGameType::create([
					'company_id' => 'company_super',
					'calc_date' => $calc_date,
					'game_type_code' => $item,
					'bet_count' => $game_type_data->count(),
					'money_total' => $game_type_data->sum('bet_amount'),
					'money_use' => $game_type_data->sum('valid_bet_amount'),
					'win_lose' => $game_type_data->sum('net_amount'),
				]);
				//log::info('重建日报表：step2=>结果'.$ret5);
				if(!$ret5){
					log::info('重建日报表：step2=>创建GameType=》rollBack'.$item);
					DB::rollBack();
					return false;
				}
			}
			//生成子公司报表
			$companies = Company::where(['is_active' => 1])->pluck('company_id');
			//$memberIds = Member::all(['member_id','login_name','company_id']);
			
			foreach($companies as $key => $company){
				log::info('重建日报表：step3=>创建DataDaily=》'.$company);
				//$company_members = $memberIds->where('company_id',$company)->pluck('login_name')->toArray();
				//改为只获取在投注明细表中有记录的会员帐号进行统计运算，减少step6 step7 循环次数
				//log::info('重建日报表：step3=>获取用户数据');
				/*$hasRecordId = uc_import_record_details::where('bet_time','>=',$_start)->where('company_id',$company)
					->distinct('player_name')->where('bet_time','<=',$_end)
					->select('player_name')->get()->toArray();*/
				$hasRecordId = $imports->where('company_id',$company)->pluck('player_name')->toArray();
				$hasRecordId = Arr_unique($hasRecordId);
				log::info('重建日报表：step3=>获取用户数据帐号');
				log::info($hasRecordId);
				/*if(!empty($hasRecordId)){
					log::info($company.'=>hasRecordId:'.json_encode($hasRecordId));
				}*/
				$company_members = Member::where('company_id',$company)->whereIn('login_name',$hasRecordId)
					->pluck('login_name')->toArray();
				//log::info($company_members);
				//$company_imports = $allImports->whereIn('player_name',$company_members);
				//改为按公司ID 过滤获取分公司投注记录，优化sql 执行速度 =》分公司所有投注明细
				//$company_imports = uc_import_record_details::where('bet_time','>=',$_start)->where('bet_time','<=',$_end)
					//->where('company_id',$company)->get();
				$company_imports = $allImports->where('company_id',$company);
				$bet_amount  = $company_imports->count();
				$money_total = $company_imports->sum('bet_amount');
				$money_use   = $company_imports->sum('valid_bet_amount');
				$win_lose    = $company_imports->sum('net_amount');
				$ret = DataDaily::create([
					'company_id' => $company,
					'calc_date' => $calc_date,
					'bet_count' => $bet_amount,
					'money_total' => $money_total,
					'money_use' => $money_use,
					'win_lose' => $win_lose,
				]);
				/*$sum_bet_count = $sum_bet_count+$bet_amount;
				$sum_money_total = $sum_money_total+$money_total;
				$sum_money_use = $sum_money_use+$money_use;
				$sum_win_lose = $sum_win_lose+$win_lose;*/
				//$company_room_imports = $imports->whereIn('player_name',$company_members);
				//改为按公司ID 过滤获取分公司投注记录，优化sql 执行速度 =》分公司游戏厅投注明细
				$company_room_imports = $imports->where('company_id',$company);
				log::info('重建日报表：step4=>创建DataDailyRoom=》'.$company);
				$ret1 = DataDailyRoom::create([
					'company_id' => $company,
					'room_code' => $room_code,
					'calc_date' => $calc_date,
					'bet_count' => $company_room_imports->count(),
					'money_total' => $company_room_imports->sum('bet_amount'),
					'money_use' => $company_room_imports->sum('valid_bet_amount'),
					'win_lose' => $company_room_imports->sum('net_amount'),
				]);
				if(!$ret||!$ret1){
					log::info('重建日报表：step3|4=>创建DataDailyRoom=》rollBack，'.$company);
					DB::rollBack();
					return false;
				}
				log::info('重建日报表：step5=>创建分公司GameType=》');
				foreach($all_game_type as $item){
					//log::info('重建日报表：step5=>创建分公司GameType=》'.$item.'||'.$company);
					$company_game_type_data = $company_imports->where('game_type',$item);
					$ret6 = DataDailyGameType::create([
						'company_id' => $company,
						'calc_date' => $calc_date,
						'game_type_code' => $item,
						'bet_count' => $company_game_type_data->count(),
						'money_total' => $company_game_type_data->sum('bet_amount'),
						'money_use' => $company_game_type_data->sum('valid_bet_amount'),
						'win_lose' => $company_game_type_data->sum('net_amount'),
					]);
					if(!$ret6){
						log::info('重建日报表：step5=>创建分公司GameType=》rollBack||'.$item);
						DB::rollBack();
						return false;
					}
				}
				if($company_imports->isEmpty()){
					log::info('重建日报表：step6|7=>分公司没有数据跳过=》'.$company);
					continue;
				}
				
				log::info('重建日报表：step6=>创建分公司RoomUser=》');
				//每日游戏厅会员报表--以后可以考虑单独拆分计算。
				foreach($company_members as $member){
					//log::info('重建日报表：step6=>创建分公司RoomUser=》'.$member);
					$company_member_imports = $imports->where('player_name',$member);
					if($company_member_imports->isEmpty()){
						continue;
					}
					$ret2 = DataDailyRoomUser::create([
						'company_id' => $company,
						'room_code' => $room_code,
						'login_name' => $member,
						'calc_date' => $calc_date,
						'bet_count' => $company_member_imports->count(),
						'money_total' => $company_member_imports->sum('bet_amount'),
						'money_use' => $company_member_imports->sum('valid_bet_amount'),
						'win_lose' => $company_member_imports->sum('net_amount'),
					]);
					if(!$ret2){
						log::info('重建日报表：step6=>创建分公司RoomUser=》rollBack'.$member);
						DB::rollBack();
						return false;
					}
				}
				log::info('重建日报表：step7=>创建分公司RoomUserGtype=》');
				//每日游戏厅会员游戏类型报表--以后可以考虑单独拆分计算。
				foreach($company_members as $member){
					//log::info('重建日报表：step7=>创建分公司RoomUserGtype=》'.$member);
					$company_member_gtype_imports = $imports->where('player_name',$member)->where('game_type',$room_game_type);
					if($company_member_gtype_imports->isEmpty()){
						//log::info('重建日报表：step7=>创建分公司RoomUserGtype=》无数据跳过'.$member);
						continue;
					}
					$ret3 = DataDailyRoomUserGtype::create([
						'company_id' => $company,
						'room_code' => $room_code,
						'login_name' => $member,
						'game_type_code' => $room_game_type,
						'calc_date' => $calc_date,
						'bet_count' => $company_member_gtype_imports->count(),
						'money_total' => $company_member_gtype_imports->sum('bet_amount'),
						'money_use' => $company_member_gtype_imports->sum('valid_bet_amount'),
						'win_lose' => $company_member_gtype_imports->sum('net_amount'),
					]);
					if(!$ret3){
						log::info('重建日报表：step7=>创建分公司RoomUserGtype=》rollBack'.$member);
						DB::rollBack();
						return false;
					}
				}
				log::info('重建日报表：=>创建分公司RoomUserGtype=》结束=>count：'.count($company_members));
			}
			/*$result2 = DataDaily::create([
				'company_id' => 'company_super',
				'calc_date' => $calc_date,
				'bet_count' => $sum_bet_count,
				'money_total' => $sum_money_total,
				'money_use' => $sum_money_use,
				'win_lose' => $sum_win_lose,
			]);*/
			DB::commit();
		}catch(Exception $e){
			log::info($calc_date.'重建报表失败，原因：'.$e->getMessage());
			return false;
		}
        return true;
	}

	//平台
	public function dataGeneralSuper($data){
		$companies = Company::all(['company_id','name']);
		$members = Member::all(['member_id','agent_id','company_id','first_deposit_time','login_name','created_at']);
		$moneyMovementData = MoneyLog::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->get();
		$depositData = DepositApply::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->where('deposit_status','succeed')->get();
		$drawData = PayOrder::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->where('draw_status','success')->get();
		$allCompanyData = [];
		foreach($companies as $company){
			$companyMembers = $members->where('company_id',$company->company_id);
			$companyMovement = $moneyMovementData->where('company_id',$company->company_id);
			$companyDeposit = $depositData->where('company_id',$company->company_id);
			$companyDraw = $drawData->where('company_id',$company->company_id);
			$companyDailyRoomUser = DataDailyRoomUser::whereHas('member',function($query) use ($company){
				$query->where('company_id',$company->company_id);
			})->where([['calc_date','>=',$data['startDate']],['calc_date','<=',$data['endDate']]])->get();
			$allCompanyData[$company->company_id] = [
				'company_name' => $company->name,
				'data'         => $this->getGeneralDataByAgentData($data,$companyDailyRoomUser,$companyMembers,
					$companyMovement,$companyDeposit,$companyDraw)
			];
		}
		return $allCompanyData;
	}

	//总帐报表
	public function dataGeneral($companyId,$data){
		$companyMembers    = Member::where('company_id',$companyId)->get();
		$moneyMovementData = MoneyLog::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->where('company_id',$companyId)->get();
		$depositData = DepositApply::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->where('company_id',$companyId)
			->where('deposit_status','succeed')->get();
		$drawData = PayOrder::where('created_at','>=',$data['startDate'])
			->where('created_at','<=',$data['endDate'].' 23:59:59')->where('company_id',$companyId)
			->where('draw_status','success')->get();
		$dataDailyRoomUser = DataDailyRoomUser::whereHas('member',function($query) use ($companyId){
			$query->where('company_id',$companyId);
		})->where([['calc_date','>=',$data['startDate']],['calc_date','<=',$data['endDate'].' 23:59:59']])->get();

		$defaultMembers = $companyMembers->where('agent_id',$companyId);
		$defaultMoneyMovementData = $moneyMovementData->whereIn('member_agent_id',
			$defaultMembers->pluck('member_id')->toArray());
		$defaultDepositData = $depositData->whereIn('member_id',$defaultMembers->pluck('member_id')->toArray());
		$defaultDrawData    = $drawData->whereIn('member_agent_id',$defaultMembers->pluck('member_id')->toArray());
		$defaultDataDailyRoomUser = $dataDailyRoomUser->whereIn('login_name',
			$defaultMembers->pluck('login_name')->toArray());

		$defaultAgentData = $this->getGeneralDataByAgentData($data,$defaultDataDailyRoomUser,$defaultMembers,
			$defaultMoneyMovementData,$defaultDepositData,$defaultDrawData);

		//返佣代理
		$directAgents = Agent::where('is_prem',1)->where('company_id',$companyId)
			->where('agent_id','!=',$companyId)->get();
		$directAgentsMembers     = $companyMembers->whereIn('agent_id',$directAgents->pluck('agent_id')->toArray());
		$directDataDailyRoomUser = $dataDailyRoomUser->whereIn('login_name',
			$directAgentsMembers->pluck('login_name')->toArray());
		$directMoneyMovementData = $moneyMovementData->whereIn('member_agent_id',
			$directAgentsMembers->pluck('member_id')->toArray());
		$directDepositData = $depositData->whereIn('member_id',$directAgentsMembers->pluck('member_id')->toArray());
		$directDrawData    = $drawData->whereIn('member_agent_id',$directAgentsMembers->pluck('member_id')->toArray());
		$directAgentData   = $this->getGeneralDataByAgentData($data,$directDataDailyRoomUser,$directAgentsMembers,
			$directMoneyMovementData,$directDepositData,$directDrawData);

		//占成代理
		$insideAgents = Agent::where('is_prop',1)->where('company_id',$companyId)
			->where('agent_id','!=',$companyId)->get();
		$insideAgentsMembers     = $companyMembers->whereIn('agent_id',$insideAgents->pluck('agent_id')->toArray());
		$insideDataDailyRoomUser = $dataDailyRoomUser->whereIn('login_name',
			$insideAgentsMembers->pluck('login_name')->toArray());
		$insideMoneyMovementData = $moneyMovementData->whereIn('member_agent_id',
			$insideAgentsMembers->pluck('member_id')->toArray());
		$insideDepositData = $depositData->whereIn('member_id',$insideAgentsMembers->pluck('member_id')->toArray());
		$insideDrawData    = $drawData->whereIn('member_agent_id',$insideAgentsMembers->pluck('member_id')->toArray());
		$insideAgentData   = $this->getGeneralDataByAgentData($data,$insideDataDailyRoomUser,$insideAgentsMembers,
			$insideMoneyMovementData,$insideDepositData,$insideDrawData);

		return [
			'default' => $defaultAgentData,
			'is_prem' => $directAgentData,
			'is_prop'  => $insideAgentData
		];
	}

	//代理
	private function getGeneralDataByAgentData($data,$dataDailyRoomUser,$Members,$moneyMovementData,$depositData,
		$drawData){
		$agentData['active_member_count'] = $dataDailyRoomUser->unique('login_name')->count();
		$agentData['money_total'] = $dataDailyRoomUser->sum('money_total');
		$agentData['money_use'] = $dataDailyRoomUser->sum('money_use');
		$agentData['win_lose'] = $dataDailyRoomUser->sum('win_lose');
		$agentData['new_first_deposit_member'] = $Members->filter(function($v,$k) use ($data){
			return (($v->first_deposit_time>=$data['startDate'].' 00:00:00') &&
				($v->first_deposit_time<=$data['endDate'].' 23:59:59'));
		})->count();
		$agentData['new_member'] = $Members->filter(function($v,$k) use ($data){
			return (($v->created_at>=$data['startDate'].' 00:00:00') && ($v->created_at<=$data['endDate'].' 23:59:59'));
		})->count();
		$agentData['deposit_online_count'] = $depositData->where('payment_method_code','Online')->count();
		$agentData['deposit_online_total'] = $depositData->where('payment_method_code','Online')->sum('deposit_money');
		$agentData['deposit_manual_count'] = $depositData->whereIn('payment_method_code',['Manual','NCManual','Local'])->count();
		$agentData['deposit_manual_total'] = $depositData->whereIn('payment_method_code',['Manual','NCManual','Local'])->sum('deposit_money');
		$agentData['deposit_alipay_count'] = $depositData->where('payment_method_code','AliPay')->count();
		$agentData['deposit_alipay_total'] = $depositData->where('payment_method_code','AliPay')->sum('deposit_money');
		$agentData['deposit_weixin_count'] = $depositData->where('payment_method_code','WeiXin')->count();
		$agentData['deposit_weixin_total'] = $depositData->where('payment_method_code','WeiXin')->sum('deposit_money');
		$agentData['deposit_QQpay_count']  = $depositData->where('payment_method_code','QQPay')->count();
		$agentData['deposit_QQpay_total']  = $depositData->where('payment_method_code','QQPay')->sum('deposit_money');
		$agentData['deposit_counter_count']  = $depositData->where('payment_method_code','Counter')->count();
		$agentData['deposit_counter_total']  = $depositData->where('payment_method_code','Counter')->sum('deposit_money');
		$agentData['deposit_unionpay_count'] = $depositData->where('payment_method_code','UnionPay')->count();
		$agentData['deposit_unionpay_total'] = $depositData->where('payment_method_code','UnionPay')->sum('deposit_money');
		$agentData['deposit_fastpay_count']  = $depositData->where('payment_method_code','FastPay')->count();
		$agentData['deposit_fastpay_total']  = $depositData->where('payment_method_code','FastPay')->sum('deposit_money');
		$agentData['deposit_bankcode_count']  = $depositData->where('payment_method_code','BankCode')->count();
		$agentData['deposit_bankcode_total']  = $depositData->where('payment_method_code','BankCode')->sum('deposit_money');

		$agentData['admin_money_inc_count'] = $moneyMovementData->where('money_operate_type','admin_money_inc')
			->count();
		$agentData['admin_money_inc_total'] = $moneyMovementData->where('money_operate_type','admin_money_inc')
			->sum('money_change');
		$agentData['admin_money_dec_count'] = $moneyMovementData->where('money_operate_type','admin_money_dec')
			->count();
		$agentData['admin_money_dec_total'] = $moneyMovementData->where('money_operate_type','admin_money_dec')
			->sum('money_change');
		$agentData['gift_money_count'] = $moneyMovementData->where('money_operate_type','gift_money')->count();
		$agentData['gift_money_total'] = $moneyMovementData->where('money_operate_type','gift_money')
			->sum('money_change');
		$agentData['backwater']  = $moneyMovementData->where('money_operate_type','backwater_settle')
			->sum('money_change');
		$agentData['draw_count'] = $drawData->count();
		$agentData['draw_total'] = $drawData->sum('draw_money');

		return $agentData;
	}

	//公司包网费用结算
	public function companyCalcItemAdd($begin_date,$end_date){
		$companies = Company::where('is_active',1)->get();
		$room_game_type_data = DataDailyRoomUserGtype::where([
			['calc_date','>=',$begin_date],
			['calc_date','<=',$end_date],
		])
			->select(\DB::raw('data_daily_room_user_gtype_id,company_id,room_code,game_type_code, sum(win_lose) as win_lose'))
			->groupBy(['company_id','room_code','game_type_code'])
			->get();
		$company_settle_no = str_replace('-','',$begin_date.$end_date);
		try{
			DB::transaction(function() use ($companies,$room_game_type_data,$company_settle_no,$begin_date,$end_date){
				foreach($companies as $company){
					$company_data = $room_game_type_data->where('company_id',$company->company_id);
					$info_room_fee = $company->info_room_fee;
					$info_room_money = [];
					$info_net_amount = [];
					foreach($company_data as $company_datum){
						$rgtype = $company_datum->room_code.'-'.$company_datum->game_type_code;
						$info_net_amount[$rgtype] = $company_datum->win_lose;
						$info_room_money[$rgtype] = $company_datum->win_lose<0?sprintf("%.2f",
							-$company_datum->win_lose*($info_room_fee[$rgtype]??0)/100):'0.00';
					}
					$room_money = array_sum($info_room_money);
					$data = [
						'company_settle_no' => $company_settle_no,
						'company_id'        => $company->company_id,
						'room_money'        => $room_money,
						'info_net_amount'   => $info_net_amount,
						'info_room_money'   => $info_room_money,
						'info_room_fee'     => $info_room_fee,
						'begin_date'        => $begin_date,
						'end_date'          => $end_date
					];
					CompanySettle::create($data);
				}
			});
			Log::error('公司包网条目生成功，生成条目：'.$company_settle_no);
			return ['status' => SUCCESS,'msg' => 'success'];
		}catch(\Exception $e){
			Log::error('公司包网条目生成失败，条目：'.$company_settle_no.'原因：'.$e->getMessage());
			return ['status' => FAILED,'msg' => 'failed'];
		}
	}
}
