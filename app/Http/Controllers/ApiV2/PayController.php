<?php
/*
|--------------------------------------------------------------------------
| 支付相关API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use Act;
use App\Libs\Helper;
use App\Libs\RoomHelper;
use App\Models\Activity;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\DepositApply;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Models\PaymentAcctLocalMuti;
use App\Models\PaymentMethodActivity;
use App\Models\Room;
use App\Services\Act\Activity\ActivityStepRuleAct\RuleActClient;
use App\Services\Pay\Pay\PayClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Pay;

class PayController extends BaseController{
	protected $memberId;
	protected $member;
	protected $payClient;
	
	public function __construct(PayClient $payClient){
		$this->middleware(function($request,$next){
			$member = $request->user();
			if(!empty($member)){
				$this->memberId = $member->member_id;
				$this->member = $member;
				return $next($request);
			}else{
				return response()->json(['status' => FAILED,'msg' => '无法获取您的账户信息，请重新登录！']);
			}
		},['except' => ['thirdCallBack']]);
		$this->payClient = $payClient;
	}

    public function getAvailablePayMethod(Request $request) {
        $this->payClient->memberId = $this->memberId;
	    $member_level_id = $this->member->member_level_id;
        $ret = Pay::getPaymentAccountMethod($this->payClient);
        if($ret['result']===false){
            return response()->json(['status' => SUCCESS, 'content' => '', 'msg' => $ret['message']]);
        }
        $payment_methods = $ret['content'];
        $payments = [];
	    //Log::info($payment_methods);
        foreach ($payment_methods as $key =>$payment_method){
	        Log::info('【'.$key.'】');
        	if($key==='Local'){
		        $where  = [
			        'is_allow_deposit'    => 1,
			        'payment_account_id'  => $payment_method->payment_account_id,
			        //'payment_method_code' => 'Local'
		        ];
		
		        $paymentBankAcct = PaymentAcctLocalMuti::where($where)->where(function($query) use($member_level_id){
			        $query->where('member_level_id',$member_level_id)->orWhere('member_level_id','0');
		        })->first();
		        if(empty($paymentBankAcct)){
		        	Log::info('【'.$key.'--'.$member_level_id.'】未找到本地银行存款卡信息！');
			        continue;
		        }
	        }
            $payments[$key] = [
                'payment_name' => config('enums.payment_method_code')[$payment_method->payment_method_code],
                'payment_account_id' => $payment_method->payment_account_id,
                'payment_method_code' => $key,
                'is_money_fixed' => $payment_method->is_money_fixed,
                'money_list' => explode(',',$payment_method->money_list),
            ];
            //如果是在线网银，需要传银行卡
            $payments[$key]['bankList'] = [];
            if($key==='OnLine'){
                $paymentPlatformBanks = $payment_methods['OnLine']->paymentAccount->paymentPlatformBank
                    ->where('is_active',1);
                $bankLists = Bank::where(['is_allow_deposit' => '1'])->whereIn('bank_code',$paymentPlatformBanks
                    ->pluck('bank_code')->toArray())
                    ->get(['bank_code','bank_name','bank_icon']);
                foreach($bankLists as &$bankList){
                    $bankList->bank_code = $paymentPlatformBanks->where('bank_code',$bankList->bank_code)
                        ->first()->platform_bank_code;
                }
                foreach($bankLists as $k2 =>$val){
                    $bankLists[$k2]->bank_icon = "/images/img/banks/".$val->bank_icon.'.png';
                }
                $payments[$key]['bankList'] = $bankLists;

            }

            $bankBind = $this->member->bankAccounts->where('is_locked',0)->where('is_allow_deposit',1);
            if($key==='Local'||$key==='Manual'||$key==='NCManual'){
                $bankLists = Bank::where(['is_allow_deposit' => '1'])->get(['bank_code','bank_name','bank_icon']);
                $payments[$key]['bankList'] = $bankLists;
                $bankName = Bank::where(['is_allow_deposit' => '1'])->pluck('bank_name','bank_code')->toArray();
                $_data['bankHistory'][0] = ['id'=>'','title'=>'不使用历史银行卡','bank_name'=>'','bank_number'=>''];
                $i = 1;
                foreach($bankBind as $k => $v){
                    $account_No = $v->bank_account_number;
                    $bank_title = '['.$bankName[$v->bank_code].']';
                    $bank_name  = '['.mb_substr($v->bank_account_name,0,1,'utf-8').'***'.']';
                    $bank_No    = '['.mb_substr($account_No,0,4).'****'.mb_substr($account_No,-4,4).']';
                    $_data['bankHistory'][$i]['id']          = $v->bank_account_id;
                    $_data['bankHistory'][$i]['title']       = $bank_title.'-'.$bank_name.'-'.$bank_No;
                    $_data['bankHistory'][$i]['bank_name']   = $bank_name;
                    $_data['bankHistory'][$i]['bank_number'] = $bank_No;
                    $_data['bankHistory'][$i]['bank_code']   = $v->bank_code;
                    $i = $i +1 ;
                }
                $payments[$key]['bankHistory'] = $_data['bankHistory'];
            }
        }

        $data['payment'] = array_values($payments);
        //所有存款活动
        $activities  = Act::getDepositActivitiesByMember($this->member);
        $data['activity'][0] = [
            'id' => 0,'activity_name' => '不参加优惠','allow_transfer'=>0,
            'rooms'=>[],'is_limit_pay_method'=>0,'limit_pay_method'=>[]
        ];

        //显示流水任务
        $activityMember = $this->member->memberActivities()->whereHas('activity',function($query){
            $query->where('activity_type','=','deposit');
        })->whereIn('activity_status',['applied','transfer_freeze','accepted'])->first();
        if(!empty($activityMember)) {
            $data['is_activity'] = true;
        }
        $room_id_agent = RoomHelper::getRoomsArrayByMember($this->member);
        $rooms = Room::where(['is_allow_transfer_in' => 1,'is_active'=>1])
            ->whereIn('room_code',$room_id_agent)
            ->where('room_code','<>','Wallet')
            ->orderBy('room_sort')->pluck('room_description','room_code')->toArray();

        //获取进行活动中的游戏厅，已有活动不能选择
        $activityHasRooms = Act::getMemberActivityRooms($this->memberId);
        $ai = 1;
        foreach($activities as $item){
            $room = array();
            if($item->is_limit_room==1){
                foreach($item->rooms as $val){
                    if(!in_array($val,$activityHasRooms)&&key_exists($val,$rooms)){
                        $room[$val] = $rooms[$val];
                    }
                }
            }else{
                foreach($rooms as $key => $val){
                    if(!in_array($key,$activityHasRooms)){
                        $room[$key] = $rooms[$key];
                    }
                }
            }
            if(count($room)){
                $i = 0;
                $_room = array();
                foreach ($room as $key => $val){
                    $_room[$i]['room_code']=$key;
                    $_room[$i]['room_name']=$val;
                    $i +=1;
                }
                if($item->is_limit_payment_method==1){
                    $limit_pay_methods = $item->paymentMethods;
                }else{
                    $limit_pay_methods = [];
                }
                $data['activity'][$ai] = [
                    'id' => $item->activity_id,
                    'activity_name'  => $item->activity_name,
                    'allow_transfer' => $item->is_allow_transfer_out,
                    'rooms' => $_room,
                    'is_limit_pay_method'=>$item->is_limit_payment_method,
                    'limit_pay_methods'=> $limit_pay_methods
                ];
                $ai +=1;
            }
        }

	    //获取存款方式的默认活动
	    $paymentMethodActivities = PaymentMethodActivity::query()
		    ->where('company_id','=',$this->member->company_id)->get();
	    $payActList = [];
	    foreach($paymentMethodActivities as $methodActivity){
		    $payActList[$methodActivity->payment_method_code] = $methodActivity->activity_id;
	    }
	    $data['payActList'] = $payActList;
	    return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);

    }

	//5.充值支付
	public function payDeposit(Request $request){
        $aid       = strip_tags($request->input('activity_id',''));
        $bank_id   = strip_tags($request->input('bank_id',''));
        $dmoney    = intval($request->input('money',0));;
        $bankCode  = strip_tags($request->input('bank_code',''));
        $roomCode  = strip_tags($request->input('room_code',''));
        $isMobile  = strip_tags($request->input('equipment','pc')) == 'pc'?0:1;
        $bank_account = strip_tags($request->input('bank_account',''));
        $bank_number  = strip_tags($request->input('bank_number',''));
		$bank_opening = strip_tags($request->input('bank_opening',''));
        $paymentMethodCode  = strip_tags($request->input('payment_method_code',''));
        $paymentAccountId   = strip_tags($request->input('payment_account_id',''));
        $request['deposit_money']       = $dmoney;
        $request['bank_account_name']   = $bank_account;
        $request['bank_account_number'] = $bank_number;
        $despApply = DepositApply::where('member_id',$this->memberId)->where('deposit_status','applied')->count();
        if($despApply>0){
	        return response()->json(['status' => 9001,'msg' => '您还有充值订单未付款，请先付款或取消该订单再提交新的订单!']);
        }
        if(!preg_match("/^[0-9]+$/",$dmoney)){
            return response()->json(['status' => 1001,'content' => "",'msg' => '充值金额只能为正整数!',]);
        }
        if($dmoney<=0){
            return response()->json(['status' => 1001,'msg' => '充值金额不能为空!']);
        }
        if(empty($paymentMethodCode)){
            return response()->json(['status' => 10020,'msg' => '充值方式不能为空!']);
        }

        if(!key_exists($paymentMethodCode,config('enums.payment_method_code'))){
            return response()->json(['status' => 10021,'msg' => '充值方式不存在!']);
        }

        if(empty($paymentAccountId)){
            return response()->json(['status' => 1003,'msg' => '支付商户号ID不能为空!']);
        }
        if($paymentMethodCode==='OnLine'&&empty($bankCode)){
            return response()->json(['status' => 10061,'msg' => '请选择支付银行!']);
        }
        if(($paymentMethodCode==='Manual' || $paymentMethodCode==='NCManual')&&empty($bankCode) &&empty($bank_id)){
            return response()->json(['status' => 1006,'msg' => '请选择汇款银行!']);
        }

        $billNo = Pay::getValidBillNo('C');
		
		$this->payClient->domain = $this->member->domain;
        $this->payClient->paymentMethodCode = $paymentMethodCode;
        $this->payClient->paymentAccountId  = $paymentAccountId;
        $this->payClient->depositMoney = $dmoney;
        $this->payClient->bankCode  = $bankCode;
        $this->payClient->companyId = $this->member->company_id;
        $this->payClient->memberId  = $this->memberId;
        $this->payClient->login_name = $this->member->login_name;
        $this->payClient->roomCode  = $roomCode;
        $this->payClient->isMobile  = $isMobile;
        $this->payClient->billNo    = $billNo;
        $this->payClient->ip = Helper::getClientIP();
        $this->payClient->remittanceInfo = Pay::remittance();

        if(!empty($bank_id)){
            $bankAcc = BankAccount::where('bank_account_id',$bank_id)
                ->where('member_agent_id',$this->memberId)
                ->where('member_agent_type','App\Models\Member')->first();
            if(!$bankAcc){
                return response()->json(['status' => 1007,'msg' => '未找到该历史银行账户!']);
            }
            $this->payClient->bankAccountId      = $bank_id;
            $this->payClient->bankAccountNumber  = $bankAcc->bank_account_number;
            $this->payClient->bankAccountName    = $bankAcc->bank_account_name;
        }
        
		if($paymentMethodCode==='AP2Bank'||$paymentMethodCode==='WX2Bank'){
			if(empty($bank_account)){
				return response()->json(['status' => 1008,'msg' => '请输入汇款人姓名!']);
			}
			$this->payClient->bankAccountName = $bank_account;
		}
		
        if(($paymentMethodCode==='Local'||$paymentMethodCode==='Manual'||$paymentMethodCode==='NCManual')&&empty($bank_id)){
            if(empty($bank_account)||empty($bank_number)){
                return response()->json(['status' => 1008,'msg' => '请输入汇款人姓名和银行卡号!']);
            }
            $Bankdata = [
                'bank_code'           => $bankCode,
                'company_id'          => $this->member->company_id,
                'member_agent_id'     => $this->memberId,
                'member_agent_type'   => Member::class,
                'bank_account_name'   => $bank_account,
                'bank_account_number' => $bank_number,
                'opening_address'     => $bank_opening,
                'is_allow_draw'       => 0,
                'is_allow_deposit'    => 1
            ];
            $newBankAcc = BankAccount::where('bank_account_number',$bank_number)
                ->where('member_agent_id',$this->memberId)
                ->where('member_agent_type','App\Models\Member')->first();
            if(!$newBankAcc) {
                $newBankAcc = BankAccount::create($Bankdata);
            }

            $this->payClient->bankAccountId     = $newBankAcc->bank_account_id;
            $this->payClient->bankAccountNumber = $newBankAcc->bank_account_number;
            $this->payClient->bankAccountName   = $newBankAcc->bank_account_name;
        }

        if(!empty($aid)){
            $activity = Activity::find($aid);
            if(empty($roomCode)&&!$activity->is_allow_transfer_out){
                return response()->json(['status' => 1009,'msg' => '选择优惠必须选择游戏厅!']);
            }
            //活动检查
            $ret = Act::getResultByActivityAndDepositMoney($aid,$this->memberId,$dmoney);
            if($ret->result==false){
                return response()->json(['status' => 1010,'msg' => $ret->message]);
            }
            //如果活动不允许同时存在
            if($roomCode && $activity->is_allow_join_ots==0){
                $ret = RoomHelper::transferActivityCheck($this->member,$request['room_code'],'in');
                if(!$ret) {
                    return response()->json(['status' => 1011,'该游戏厅正在进行打水活动，请选择别的活动厅!']);
                }
                //把该厅游戏余额回收后再进行转入操作
                RoomHelper::recycleFromRoomsForManager($this->member,[$request['room_code']]);
            }
            $this->payClient->activityId = $aid;
        }

        $ret = Pay::getPaymentAccountByPayClient($this->payClient);

        if(!$ret['result']){
            Log::error('支付失败：'.$ret['message']);
            //Log::info('$$$$$$$$$$$$$$$$$$$$$$$$16003$$$$$$$$$$$$$$$$$$$$$$$$$$');
            return response()->json(['status' => 1012,'msg' => $ret['message']]);
        }
        try{
            $paymentAccount = $ret['content'];
            $paymentPlatform = \App::make($paymentAccount->payment_platform_code);
            $ret = $paymentPlatform->Pay($this->payClient,$paymentAccount);
            return response()->json($ret);
        }catch(\Exception $e){
            Log::error('支付提交失败：msg=>'.$e->getMessage());
            Log::error('支付提交失败：filename=>'.$e->getFile());
            Log::error('支付提交失败：line=>'.$e->getLine());
            //Log::info(json_encode($request->all()));
            //Log::info('$$$$$$$$$$$$$$$$$$$$$$$$16004$$$$$$$$$$$$$$$$$$$$$$$$$$');
            return response()->json(['status' => 1013,'msg' => '网络错误，支付失败，请稍后重试！']);
        }
	}
	
	//5.1取消存款申请
	public function cannelDeposit(Request $request){
		$bill_no = strip_tags($request->input('bill_no',''));
		$depositApply = DepositApply::where('bill_no',$bill_no)->first();
		if(!$depositApply){
			return response()->json(['status' => 1001,'msg' => '存款记录未找到！']);
		}
		if($depositApply->deposit_status!=='applied'){
			return response()->json(['status' => 1002,'msg' => '该笔存款当前状态下不能取消！']);
		}
		$res = $depositApply->update(['deposit_status'=>'cancel','description'=>'会员取消存款！']);
		if(!$res){
			return response()->json(['status' => 1003,'msg' => '网络错误!']);
		}
		$memberActivity = $depositApply->memberActivity;
		if($memberActivity){
			$memberActivity->update(['activity_status'=>'reject','description'=>'存款失败！']);
		}
		return response()->json(['status' => SUCCESS, 'msg' => '存款取消成功！']);
	}
	
	//6.第三方回调
	public function thirdCallBack(Request $request,$payment_platform_code){
		$paymentPlatform = \App::make($payment_platform_code);
		return $paymentPlatform->thirdCallBack($request);
	}
	
	//7.活动申请记录
	public function listApply(Request $request){
		$rooms = Room::where([['is_allow_display','1'],['is_active','1']])
				 ->orderBy('room_sort')->pluck('room_description','room_code')->toArray();
		/*$activityApply = $this->member->memberActivities()->whereHas('activity',function($query){
			$query->where('activity_type','!=','deposit');
		})->orderBy('created_at','desc')->limit(10)->get();*/
        $activityApply = $this->member->memberActivities()->whereHas('activity')->orderBy('created_at','desc')->limit(10)->get();
		
		$task = [];
		foreach($activityApply as $key => $val){
			//更新活动中的流水
			if($val->activity_status=='accepted'){
				Act::checkMemberActivity($val);
				$val = MemberActivity::find($val->member_activity_id);
			}
			//获取参加活动的信息
			$task['sort'] = $key+1;
			$task['id']   = $val->member_activity_id;
			$task['billNo']    = $val->bill_no;
			$task['activity_name'] = $val->activity->activity_name;

			if(empty($val->room_code)||$val->room_code==''){
                $task['room_name']     = '无';
            }else{
                $task['room_name']     = $rooms[$val->room_code];
            }
			$task['add_time']      = $val->occur_time;
			$task['apply_status']  = $val->activity_status;
			$task['apply_statusn'] = config('enums.activity_status')[$val->activity_status];
			$task['remark'] = $val->description;
			$data['list'][$key] =$task;
		}
		$data['counts'] = $activityApply->count();
		//$data['list'][] = $task;
		return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);
	}
	
	//8.活动申请-获取活动列表&房间列表
	public function getApply(Request $request){
		$data['billNo'] = Pay::getValidBillNo('A');
		$activities = Act::getManualActivities($this->member);
		//获取进行活动中的游戏厅，已有活动不能选择
		$activityHasRooms = Act::getMemberActivityRooms($this->memberId);

        $room_id_agent = RoomHelper::getRoomsArrayByMember($this->member);
        $rooms = Room::where(['is_allow_transfer_in' => 1,'is_active'=>1])
            ->whereIn('room_code',$room_id_agent)
            ->where('room_code','<>','Wallet')
            ->orderBy('room_sort')->pluck('room_description','room_code')->toArray();
		
		$data['activity'] = [];
		$ai = 0;
		foreach($activities as $key => $item){
			$room = [];
			if($item->is_limit_room==1){
				foreach($item->rooms as $keys => $val){
					if(!in_array($val,$activityHasRooms)&&key_exists($val,$rooms)){
						$room[$keys]['roomCode'] = $val;
						$room[$keys]['roomName'] = $rooms[$val];
					}
				}
			}else{
				$i = 0;
				foreach($rooms as $keys => $val){
					if(!in_array($keys,$activityHasRooms)){
						$room[$i]['roomCode'] = $keys;
						$room[$i]['roomName'] = $val;
						$i +=1;
						//$room[$keys] = $rooms[$key];
					}
				}
			}
			if(count($room)){
				$data['activity'][$ai] = [
					'id' => $item->activity_id,
					'activity_name' => $item->activity_name,
					'allow_transfer' => $item->is_allow_transfer,
					'rooms' => $room
				];
			}else{
				$data['activity'][$ai] = [
					'id' => $item->activity_id,
					'activity_name' => $item->activity_name,
					'allow_transfer' => $item->is_allow_transfer,
					'rooms' => [],
				];
			}
			$ai+=1;
		}
		foreach($rooms as $key => $val){
			$data['room'][] = ['roomCode' => $key,'roomName' => $val];
		}
		
		return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);
	}
	
	//9.提交活动申请
	public function addApply(Request $request){
		$applys = $this->member->memberActivities()->whereHas('activity',function($query){
		    $query->where('activity_type','!=','deposit');
        })->where('created_at','>=',date('Y-m-d'))->get();
		if($applys->count()>=6){
			return response()->json(['status' => 1001,'msg' => '每天只有6次申请机会，请明天再试!']);
		}
		$aid      = strip_tags($request->input('id',''));
		$roomCode = strip_tags($request->input('roomCode',''));
		$bill_no  = strip_tags($request->input('billNo',''));
		$gameNO   = strip_tags($request->input('gameNO',''));
        $remark   = strip_tags($request->input('remark',''));
		/*if($gameNO<>'') {
			$remark = '当局局号：'.$gameNO."，".$remark;
		}*/
		$bill_no_start = strip_tags($request->input('billNo_start',''));
		$bill_no_end   = strip_tags($request->input('billNo_end',''));

		if(empty($aid)) {
			return response()->json(['status' => 1002,'msg' => '请选择需要参加的活动!']);
		}
        $activity = Activity::findOrFail($aid);
		if(!$activity){
            return response()->json(['status' => 1009,'msg' => '选择参加活动不存在!']);
        }
        if($activity->is_allow_transfer==0){
            if(empty($roomCode)) {
                return response()->json(['status' => 1003,'msg' => '请选择参加活动的游戏厅!']);
            }
        }
		if(empty($bill_no)) {
			return response()->json(['status' => 1004,'msg' => '申请单号不能为空!']);
		}

        //如果活动不允许同时存在
        if($roomCode && $activity->is_compatibility==0){
            $ret = RoomHelper::transferActivityCheck($this->member,$request['room_code'],'in');
            if(!$ret->result) return response()->json(['status'=>FAILED,'该游戏厅正在进行打水活动，请选择别的活动厅']);
            //把该厅游戏余额回收后再进行转入操作
            RoomHelper::recycleFromRoomsForManager($this->member,[$roomCode]);
        }
		//活动是否已过期
        $ruleActClient = new RuleActClient();
        $ruleActClient->memberId = $this->memberId;
        $ruleActClient->roomCodeIn = $roomCode;
        $ret = Act::getResultByActivityPaymentCheck($aid,$ruleActClient);
		if(!$ret->result) {
			return response()->json(['status' => 1005,'msg' => "该活动已经失效!"]);
		}
		$activityMemberApplyInsert['activity_id'] = $aid;
		$activityMemberApplyInsert['bill_no']     = $bill_no;
		$activityMemberApplyInsert['member_id']   = $this->memberId;
		$activityMemberApplyInsert['room_code']   = $roomCode;
        $activityMemberApplyInsert['game_no']     = $gameNO;
		$activityMemberApplyInsert['bill_no_start'] = $bill_no_start;
		$activityMemberApplyInsert['bill_no_end']   = $bill_no_end;
		$activityMemberApplyInsert['description']   = $remark;
		$activityMemberApplyInsert['occur_time']    = date('Y-m-d H:i:s');
		$activityMemberApplyInsert['activity_status'] = 'applied';
		$activityMemberApplyInsert['deposit_money']   = 0;
		$activityMemberApplyInsert['ip'] = Helper::getClientIP();
		$memberActivity = MemberActivity::create($activityMemberApplyInsert);
		try{
			$applys['billNo'] = $request['bill_no'];
			$applys['applyDescribe'] = '手工申请';
			return response()->json(['status' => SUCCESS,'msg' => '申请提交成功，请等待相关人员审核！']);
			//event(new \App\Events\BroadcastActivityApply($activityApply)); 广播取消
		}catch(\Exception $e){
			return response()->json(['status' => 1006,'msg' => '申请提交失败，请稍后再试!']);
		}
		
	}
	
	//10.取消活动申请
	public function cancelApply(Request $request){
		$aid  = strip_tags($request->input('id',''));
		if(empty($aid)){
			return response()->json(['status' => 1001,'content' => "",'msg' => '活动申请ID不能为空！']);
		}

        $ret = Act::memberGiveUpActivity($aid,true,'cancel');
		if($ret){
			$info = MemberActivity::find($aid);
			$data['status'] = [
				'apply_statusn' => config('enums.activity_status')[$info->activity_status],
				'apply_status'  => $info->activity_status
			];
			return response()->json(['status' => SUCCESS,'content' => $data,'msg' => '活动申请取消成功!']);
		}
		
		return response()->json(['status' => 1002,'content' => "",'msg' => '活动申请取消失败!']);
	}
}
