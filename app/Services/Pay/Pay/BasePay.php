<?php

namespace App\Services\Pay\Pay;

use App\Jobs\CheckMemberActivity;
use App\Jobs\HandelDeposit;
use App\Jobs\HandelDraw;
use App\Jobs\HandelDrawThirdCallBack;
use App\Jobs\HandelThirdCallBack;
use App\Libs\Helper;
use App\Libs\PayHelper;
use App\Libs\RoomHelper;
use App\Models\Agent;
use App\Models\Bank;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Models\PaymentAccount;
use App\Models\PaymentAccountMethod;
use App\Models\PaymentAcctLocalMuti;
use App\Models\PaymentMethod;
use App\Models\PaymentQueue;
use App\Models\RoomAccountTransfer;
use App\Models\uc_third_payment_callback;
use App\Services\Act\Activity\ActivityStepRuleAct\RuleActClient;
use Carbon\Carbon;
use Complex\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BasePay implements IBasePay{
    //父类定义公共的支付渠道属性
    public $request;
    protected $member;
    protected $payClient;
    protected $payResult;
    protected $repAgent;
    protected $paymentMethod;
    protected $bank;
    protected $depositApply;
    protected $drawApply;
    protected $paymentQueue;
    protected $memberActivity;
    protected $moneyMvmtMember;
    protected $paymentAccount;
    protected $paymentAccountMethod;
    protected $paymentPlatform;

    public function __construct(PayClient $payClient,PayResult $payResult){
        $this->payClient = $payClient;
        $this->payResult = $payResult;
    }

    public function getAllMember(){
        $members = Member::all();
        return $members;
    }

    public function pay(PayClient $payClient,$paymentAccount){
        //所有支付都要实现这个方法
    }

    public function payOut($drawApplyId,$paymentAccount){

    }

    //组合成 payClient 对象
    public function assembleClient(){
        $this->payClient->depositTime = Carbon::now();
    }

    public function assembleClientByDraw(Request $request){
        $this->payClient->memberId = $request->user()->member_id;
        $this->payClient->companyId = $request->user()->company_id;
        $this->payClient->billNo = $request['withdrawBillNo'];
        $this->payClient->drawMoney = $request['money'];
        $this->payClient->drawTime = Carbon::now();
        $this->payClient->bankAccountId = $request['bank_account_id'];
        $this->payClient->ip = Helper::getClientIP();
    }

    //检查单号是否唯一，已弃用
    public function checkBillNo(){ }

    //创建存款申请记录
    public function genDepositApply(){
		
    }

    //获取支付方式手续费
    public function getDepositFeeRatio($payClient){
	    $pam = PaymentAccountMethod::query()->where([
		    ['payment_account_id',$payClient->paymentAccountId],['payment_method_code',$payClient->paymentMethodCode]
	    ])->first();
        return ($pam->fee_ratio??0)/100;
    }

    public function genDrawApply(Request $request){
        //先冻结取款金额
        $this->assembleClientByDraw($request);
        $memberAgent = Member::find($this->payClient->memberId);
        $drawFee = 0;
        if(Helper::getSetting('IS_DRAW_FEE','company_super') && $this->payClient->drawMoney<500){

            $draw = PayOrder:: where('member_agent_id',$this->payClient->memberId)
                ->where('created_at','>=',date('Y-m-d') . ' 00:00:00')
                ->where('draw_money','<','500')->where('draw_status','success')->first();
            if($draw){
                $drawFee = $this->payClient->drawMoney * Helper::getSetting('DRAW_FEE','company_super');
            }
        }

        DB::beginTransaction();
        $ret1 = Helper::upMoney($this->payClient->memberId,'App\Models\Member',$this->payClient->drawMoney,
            'money_freeze',null,null,$this->payClient->billNo);
        $ret2 = $this->freezeDrawMoney($this->payClient->memberId,$this->payClient->drawMoney);

        $data = [
            'bill_no'           => $this->payClient->billNo,
            'company_id'        => $memberAgent->company_id,
            'draw_money'        => $this->payClient->drawMoney,
            'draw_fee'          => $drawFee,
            'member_agent_id'   => $this->payClient->memberId,
            'member_agent_type' => 'App\Models\Member',
            'bank_account_id'   => $this->payClient->bankAccountId,
            'draw_time'         => $this->payClient->drawTime,
            'draw_status'       => 'apply'
        ];
        try{
            PayOrder::create($data);
        }catch(\Exception $e){
            DB::rollback();
            return false;
        }

        if(!$ret1 || !$ret2){
            DB::rollback();
            return false;
        }else{
            DB::commit();
            return $ret2;
        }

    }

    //API取消取款申请
    public function cancelDrawApplyToApi($bill_no){
        $draw = PayOrder::where(['bill_no' => $bill_no])->first();
        if(!$draw || $draw->draw_status != 'apply'){
	        Log::error('【取消取款】失败=>订单不存在或订单状态已改变，BillNO'.$bill_no);
            return false;
        }
	    $ret1 = $ret2 = $ret3 = false;
        DB::beginTransaction();
        $ret1 = $this->unfreezeDrawMoney($draw->member_agent_id,$draw->draw_money);
        if($ret1){
	        $ret2 = Helper::upMoney($draw->member_agent_id,'App\Models\Member',$draw->draw_money,'money_unfreeze','会员取消取款申请',null,$bill_no);
        }
        $ret3 = $draw->update(['draw_status' => 'cancel']);
        if($ret1 && $ret2 && $ret3){
	        DB::commit();
	        return true;
        }
	    DB::rollback();
	    return false;
    }

    //取消取款申请
    public function cancelDrawApplyToManager($bill_no){
	    $drawApply = PayOrder::where(['bill_no' => $bill_no])->first();
	    if(!$drawApply){
		    Log::error('【取消取款】失败=>订单不存在，BillNO'.$bill_no);
		    return false;
	    }

	    DB::beginTransaction();
	    try{
		    $user = Member::where('member_id',$drawApply->member_agent_id)->lockForUpdate()->first();
		    if(empty($user)){
			    Log::error('【取消取款】失败=>用户不存在，Mid:'.$drawApply->member_agent_id.'，BillNO'.$bill_no);
			    return false;
		    }
		    Log::info('【取消取款】解冻开始：BillNO'.$bill_no);
		    if($user->freeze_draw<$drawApply->draw_money){
			    DB::rollBack();
			    Log::error('【取消取款】失败=>解冻金额不足，冻结金额:'.$user->freeze_draw.'，订单金额:'.$drawApply->draw_money.'，BillNO'.$bill_no);
			    return false;
		    }
		    $freeze_draw = $user->freeze_draw;
		    $res1 = $user->decrement('freeze_draw',$drawApply->draw_money);
		    $res2 = Helper::upMoney($drawApply->member_agent_id,$drawApply->member_agent_type,$drawApply->draw_money,
			    'money_unfreeze','提款取消/驳回',null,$bill_no);

		    if($res2&&$res1){
			    DB::commit();
			    Log::info('【取消取款】解冻上分成功=》BillNO'.$bill_no);
			    return true;
		    }
		    DB::rollBack();
		    Log::error('【取消取款】解冻金额失败，冻结金额:'.$freeze_draw.'，订单金额:'.$drawApply->draw_money.'，BillNO'.$bill_no);
		    return false;
	    }catch(\Exception $e){
		    DB::rollBack();
		    Log::info('【取消取款】失败=>BillNO'.$bill_no.'，'.$e->getMessage());
		    return false;
	    }
    }

    //会员存款活动申请列表
    public function genMemberActivity(){ }

    //排除会员不允许的支付方式，返回该会员允许的支付方式
    public function getPaymentMethodsByMemberId($memberId){
        $paymentMethods = $this->getValidPaymentMethods();
        $member         = Member::find($memberId);
        $exceptPaymentMethods = $member->exceptPaymentMethods()->get();
        // 计算差集
        $paymentMethods = $paymentMethods->diff($exceptPaymentMethods);
        return $paymentMethods;
    }

    public function getAllowDepositBanks(){
        $depositBanks = Bank::where(['is_allow_deposit' => '1'])->orderBy('bank_sort','asc')->get();
        return $depositBanks;
    }

    public function getAllowDrawBanks(){
        $drawBanks = Bank::where(['is_allow_draw' => '1'])->orderBy('bank_sort','asc')->get();

        return $drawBanks;
    }

    //会员已经提交的汇款申请
    public function getAppliedBankDepositByMemberId($memberId){
        $member = Member::find($memberId);
        $appliedDeposit = $member->depositApplies()->where('deposit_status','applied')
            ->where('payment_platform_code','LocalPay')->first();
        return $appliedDeposit;
    }

    //可用的收款方式
    public function getValidPaymentMethods(){
        $paymentMethods = PaymentMethod::where(['is_active' => '1'])->orderBy('payment_method_sort','ASC')->get();
        return $paymentMethods;
    }

    //生成订单号
    public function getValidBillNo($businessCode){
        $yCode = array('A','B','C','D','E','F','G','H','I','J');
        $orderSn =
            $businessCode
            . $yCode[intval(date('Y'))-2011]
            . date('mdHi')
            . substr(microtime(),2,3)
            . sprintf('%02d',rand(0,99));
        return $orderSn;
    }

    // 存款附言 6位随机大写字母
    public function remittance(){
        $randStr = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        $rand = substr($randStr,0,6);
        return $rand;
    }

    //决定收款账号的公共方法
    public function getPaymentAccountByPayClient(PayClient $payClient){
        $paymentAccountId = $payClient->paymentAccountId;
        $where[] = ['in_out_type','=','in'];
        $where[] = ['payment_method_code','=',$payClient->paymentMethodCode];
        $where[] = ['payment_account_id','=',$paymentAccountId];
        if($payClient->paymentMethodCode == 'QRCode'){
            $where[] = ['qr_type','=',$payClient->QRType];
        }
        $paymentAccountMethod = PaymentAccountMethod::with('paymentAccount')
            ->whereHas('paymentAccount',function($query){
                $query->where('is_allow_deposit',1);
            })->where($where)->first();
        if(!$paymentAccountMethod){
            return ['result' => false,'content' => '','message' => '收款账号不存在！'];
        }
        $paymentAccount = $paymentAccountMethod->paymentAccount;
        $deposit_money  = $payClient->depositMoney;
        $is_money_fixed = $paymentAccountMethod->is_money_fixed;
        $money_list     = explode(',',$paymentAccountMethod->money_list);
        if($is_money_fixed == 0){
            if($deposit_money<$money_list[0]){
                return ['result' => false,'message' => '存款低于最低金额'];
            }
            if($deposit_money>$money_list[1]){
                return ['result' => false,'message' => '存款高于最高金额'];
            }
        }else{
            if(!in_array($deposit_money,$money_list)){
                return ['result' => false,'message' => '存款金额只允许在' . $paymentAccountMethod->money_list . '金额'];
            }
        }
        return ['result' => true,'content' => $paymentAccount,'message' => ''];
    }

    //取收款账号
    public function getPaymentAccountMethod(PayClient $payClient){
        $memberId    = $payClient->memberId;
        $member      = Member::find($memberId);
        $company_id  = $member->company_id;
	    $level_id    = $member->member_level_id;
        //新会员不显示银行转帐方式的存款方式
        $desicount   = DepositApply::where('member_id',$member->member_id)->where('deposit_status','succeed')->count();
        if($company_id == 'company_default'){          //wbet 开启新会员禁用手工转帐存款方式
	        /*if($level_id=='65b3eee506b344958109b491db9776c4'){   //一星会员不显示存款方式
		        $pay_methods = PaymentAccountMethod::with('paymentAccount')
			        ->where('is_allow_deposit','1')
			        ->whereHas('paymentAccount',function($query) use ($company_id){
				        $query->where('company_id',$company_id)->where('is_allow_deposit','99');
			        })->orderByDesc('method_income_total')->get();
	        }else{
		        $pay_methods = PaymentAccountMethod::with('paymentAccount')
			        ->where('is_allow_deposit','1')
			        ->whereHas('paymentAccount',function($query) use ($company_id){
				        $query->where('company_id',$company_id)->where('is_allow_deposit','1');
			        })->orderByDesc('method_income_total')->get();
	        }*/
	        
	        //if($desicount>0){    //已存过款，非新会员
		        $pay_methods = PaymentAccountMethod::with('paymentAccount')
			        ->where('is_allow_deposit','1')
			        ->whereHas('paymentAccount',function($query) use ($company_id){
				        $query->where('company_id',$company_id)->where('is_allow_deposit','1');
			        })->orderByDesc('method_income_total')->get();
	        /*}else{      //未存过款，新会员
		        $pay_methods = PaymentAccountMethod::with('paymentAccount')
			        ->where('is_allow_deposit','1')
			        ->whereNotIn('payment_method_code',['Manual','Local'])
			        ->whereHas('paymentAccount',function($query) use ($company_id){
				        $query->where('company_id',$company_id)->where('is_allow_deposit','1');
			        })->orderByDesc('method_income_total')->get();
	        }*/
        }else{
	        $pay_methods = PaymentAccountMethod::with('paymentAccount')
		        ->where('is_allow_deposit','1')
		        ->whereHas('paymentAccount',function($query) use ($company_id){
			        $query->where('company_id',$company_id)->where('is_allow_deposit','1');
		        })->orderByDesc('method_income_total')->get();
        }
	    
        $payment_account_codes = array_keys(config('enums.payment_method_code'));
	    //log::info($payment_account_codes);
        $pay_method_arr = [];
        foreach($payment_account_codes as $payment_account_code){
        	
            $pay_method = $pay_methods->where('payment_method_code',$payment_account_code)->first();
	        //如果是本地收款账号，还需要检查是否配置了收款卡、收款码
            if($pay_method){
                if($pay_method->paymentAccount->is_local == 1){
                    $palm = PaymentAcctLocalMuti::where('payment_account_id',$pay_method->paymentAccount->payment_account_id)
	                    ->where('payment_method_code',$payment_account_code)
                        ->where('in_out_type','in')->where('is_allow_deposit',1)
	                    ->inRandomOrder()->first();
                    if(!$palm){
                        continue;
                    }

                }
                $pay_method_arr[$payment_account_code] = $pay_method;
            }
        }

        $pay_arrs = $pay_method_arr;
        if(empty($pay_arrs)){
            return ['result' => false,'content' => '','message' => '暂无收款方式，请联系客服'];
        }
        return ['result' => true,'content' => $pay_arrs,'message' => 'ok'];
    }

    public function getPaymentAccountMethodByPayClient(PayClient $payClient){
        $memberId = $payClient->memberId;
        $paymentMethodCode = $payClient->paymentMethodCode;
        $QRType = $payClient->QRType;
        //这里根据后台配置取渠道，看哪个渠道有开， 哪个渠道有额度等，具体账号，在渠道内部取
        $member = Member::find($memberId);
        //第一步，根据会员分组，取分组下的收款账号；特殊分组没设置收款账号，或者收款账号不可用，走安全等级
        //获取会员分组
        //$member_group = $member->group;
        //$ret = $this->getPaymentAccountMethodByMemberGroup($member_group,$paymentMethodCode,$QRType);
        //if($ret['result']) return $ret;
        // 第二步，取 payment_account_method 中某支付方式的账号结果集
        $companyId = $member->company_id;
        if($paymentMethodCode == 'Bank'){
            $paymentAccountIds = PaymentAccountMethod::where('payment_method_code','OnLine')
                ->where('is_allow_deposit',1)
                ->whereHas('paymentAccount',function($query) use ($companyId){
                    $query->where('company_id',$companyId)->where('is_allow_deposit',1);
                })->pluck('payment_account_id');
        }elseif($paymentMethodCode == 'QRCode'){
            $paymentAccountIds = PaymentAccountMethod::where('payment_method_code','QRCode')
                ->where('is_allow_deposit',1)
                ->where('qr_type','=',$QRType)->whereHas('paymentAccount',function($query) use ($companyId){
                    $query->where('company_id',$companyId)->where('is_allow_deposit',1);
                })->pluck('payment_account_id');
        }else{
            $paymentAccountIds = PaymentAccountMethod::where('payment_method_code',$paymentMethodCode)
                ->where('is_allow_deposit',1)
                ->whereHas('paymentAccount',function($query) use ($companyId){
                    $query->where('company_id',$companyId)->where('is_allow_deposit',1);
                })->pluck('payment_account_id');
        }

        if($paymentAccountIds->isEmpty()){
            return ['result' => false,'content' => '','message' => '网络错误，请联系客服！'];
        }
        //第三步，取按照上面的结果集，取和会员等级相符合的 账号，过滤掉不符合的收款账号，如果过滤结果为空
        $paymentAccounts = PaymentAccount::whereIn('payment_account_id',$paymentAccountIds)
            ->where('security_level_code','=',$member->security_level_code)->get();

        if($paymentAccounts->isEmpty()){
            return ['result' => false,'content' => '','message' => '网络错误，请联系客服！'];
        }
        //第四步，如果上面的是结果集，账号数量大于1，那么按照额度，取当前已经使用额度最小的账号，否则，直接返回取得的唯一账号即可
        $paymentAccount = $paymentAccounts->sortByDesc('current_quota')->first();
        $where = [
            'payment_account_id' => $paymentAccount->payment_account_id,'payment_method_code' => $paymentMethodCode,
            'in_out_type'        => 'in'
        ];
        if($paymentMethodCode === 'QRCode'){
            $where['qr_type'] = $QRType;
        }
        $paymentAccountMethod = PaymentAccountMethod::with('paymentAccount')->where($where)->first();
        //这里暂时这样写，休假后回来改
        if($paymentAccountMethod->paymentAccount->is_local == 1){
            $paymentAccountMethod = PaymentAcctLocalMuti::where([
                ['payment_method_code',$paymentMethodCode],['is_allow_deposit',1],
                ['payment_account_id',$paymentAccountMethod->payment_account_id]
            ])->orderBy('income_current')->first();
            if(empty($paymentAccountMethod)){
                return ['result' => false,'content' => '','message' => '网络错误，请联系客服！'];
            }
        }
        return ['result' => true,'content' => $paymentAccountMethod,'message' => ''];
    }

    public function getPaymentAccountMethodByMemberGroup($group,$paymentMethodCode,$QRType = null){
        if(!$group){
            return ['result' => false,'content' => '','message' => '会员分组不存在'];
        }
        $paymentAccount = $group->paymentAccount;
        if(!$paymentAccount){
            return ['result' => false,'content' => '','message' => '分组指定的收款账号不存在'];
        }

        /*if (!$this->paymentAccount->checkPaymentAccountValid($paymentAccount->payment_account_id)){
             return ['result'=>false,'content'=>'','message'=>'分组指定的收款账号不可用'];
         }*/

        if($paymentMethodCode !== 'QRCode'){
            $paymentAccountMethod = PaymentAccountMethod::with('paymentAccount')
                ->where([
                    'payment_account_id'  => $paymentAccount->payment_account_id,
                    'payment_method_code' => $paymentMethodCode,'in_out_type' => 'in'
                ])
                ->first();
        }else{
            $paymentAccountMethod = PaymentAccountMethod::with('paymentAccount')
                ->where([
                    'payment_account_id'  => $paymentAccount->payment_account_id,
                    'payment_method_code' => $paymentMethodCode,'in_out_type' => 'in','qr_type' => $QRType
                ])
                ->first();
        }
        if(!$paymentAccountMethod){
            return ['result' => false,'content' => '','message' => '分组指定的收款方式不存在'];
        }
        return ['result' => true,'content' => $paymentAccountMethod,'message' => ''];
    }

    public function getDrawPaymentAccountByDrawAppId($drawApplyId){
        $drawApply = PayOrder::find($drawApplyId);
        if(!$drawApply){
            return ['result' => false,'content' => '','message' => '该订单不存在！'];
        }
        $company_id = $drawApply->company_id;
        $draw_money = $drawApply->draw_money;
	      $bankAccount = $drawApply->bankAccount;
        $paymentAccount = PayHelper::getDrawPaymentAccountByDrawMoneyCompanyId($bankAccount->bank_code,$draw_money,$company_id);
        if(!$paymentAccount){
            return ['result' => false,'content' => '','message' => '暂无可用的出款方式！'];
        }
        return ['result' => true,'content' => $paymentAccount,'message' => 'ok'];
    }

    //第三方回调，支付成功后处理=》加入队列，//活动是否限制游戏厅，以及会员选择的游戏厅
    public function handelThirdCallBack(PayResult $payResult){
        //1、检查 订单号是否存在申请表
        //2、检查 mysql 中是否已经有数据
        //3、第一次回调的话，如果有关联活动，加入队列
        //4、第一次回调的话，如果没有关联活动，直接加分
        //5、不是第一次回调的话，更新回调次数记录，更新时间

        $data_array = [
            'money'                 => $payResult->depositMoney,
            'type'                  => 'deposit',
            'bill_no'               => $payResult->billNo,
            'payment_platform_code' => $payResult->paymentPlatformCode,
            'add_time'              => $payResult->dateTime,
            'all_args'              => $payResult->allArgs,
        ];

        $payResult->isContainActivity = '0';
        $payResult->isCutoff = '0';
        //第三方回调，只给 billNoEncoded 数据，并要求还没有上分，其他的要从存款订单中去找
        $depositApply = DepositApply::where(['bill_no'=>$payResult->billNo])->lockForUpdate()->first();
        if($depositApply){
            $depositStatus = $depositApply->deposit_status;
            //存在对应的存款申请，回调合法
            if($depositStatus == 'applied' || $depositStatus == 'expired'){
                //查找是否已经存在队列记录
                $paymentQueue = PaymentQueue::where(['bill_no' => $payResult->billNo])->first();
                if($paymentQueue){
                    $paymentQueue->increment('call_back_count',1);
                    $result = $paymentQueue->update(['update_time' => $payResult->dateTime]);
                    //此处信更新回调次数，是否需分重新加入队列？
	                //HandelDeposit::dispatch($paymentQueue)->onQueue('deposit');
                }else{
                    //从存款订单中找会员编号和相关的会员活动编号，准备队列中使用
                    $payResult->memberId = $depositApply->member_id;
                    $payResult->memberActivityId = $depositApply->member_activity_id;

                    //查找是否有关联的活动id，在申请表中查询
                    $memberActivity = $depositApply->memberActivity()->first();
                    if($memberActivity){
                        $payResult->isContainActivity = '1';
                        $activity = $memberActivity->activity()->first();
                        $paymentAction = $activity->activitySteps()->where('activity_step_type','=','PaymentAction')
                            ->first();
                        if($paymentAction){
                            $payResult->isCutoff = $paymentAction->is_cutoff;
                        }
                    }else{
						Log::info('【第三方回调=》处理】bill_no:'.$payResult->billNo." 未参与活动");
                    }
                    //各种需要查询的参数已经查询完毕，调用方法解决:加入队列记录和进队列功能
                    $this->newPaymentQueueAndEnterQueue($payResult);
                    //更新为上分中状态
                    $data = ['deposit_status' => 'audit'];
                    if($depositStatus == 'expired'){
                        $data['description'] = '';
                    }
                    $depositApply->update($data);
                }
            }else{
	            Log::info('【第三方回调=》处理】 bill_no:'.$payResult->billNo."，订单状态不为：applied，expired！");
            }
        }else{
	        Log::info('【第三方回调=》处理】 bill_no:'.$payResult->billNo."，订单状态未找到！");
        }
    }

    //取款回调
    public function handelThirdDrawCallBack(PayResult $payResult){
        $data_array = [
            'money'                 => $payResult->drawMoney,
            'type'                  => 'draw',
            'bill_no'               => $payResult->billNo,
            'payment_platform_code' => $payResult->paymentPlatformCode,
            'add_time'              => $payResult->dateTime,
            'all_args'              => $payResult->allArgs,
        ];

        $drawApply = PayOrder::where(['bill_no' => $payResult->billNo])->first();
        if($drawApply){
            if(in_array($drawApply->draw_status,['apply','accept','audit'])){
                $this->newPaymentQueueAndEnterQueue($payResult);
            }
        }
    }

    //加入队列记录，并加入队列
    public function newPaymentQueueAndEnterQueue(PayResult $payResult){
        $queue_status = 'queue_ready';
        if($payResult->isContainActivity == '1'){
            if($payResult->isCutoff == '1'){
                $queue_status = 'queue_no_enter';
            }
        }
        $paymentQueue = $this->newPaymentQueue($payResult,$queue_status);
        if($queue_status <> 'queue_no_enter'){
            if($payResult->depositMoney>0){
                HandelDeposit::dispatch($paymentQueue)->onQueue('deposit');
            }else if($payResult->drawMoney>0){
                HandelDraw::dispatch($paymentQueue)->onQueue('draw');
            }
        }
    }

    //加入队列记录的方法
    public function newPaymentQueue(PayResult $payResult,$queue_status){
    	$payqueue = PaymentQueue::where('bill_no',$payResult->billNo)->first();
    	if($payqueue){
		    return $payqueue;
	    }
        $paymentQueue = PaymentQueue::create([
            'bill_no'               => $payResult->billNo,
            'payment_platform_code' => $payResult->paymentPlatformCode,
            'payment_method_code'   => $payResult->paymentMethodCode,
            // 记录一些讯息 方便财务在看上分队列的时候，能关联到其他讯息，队列本身和这些无关 ……
            'member_agent_id'       => $payResult->memberId ?: $payResult->agentId,
            'member_agent_type'     => $payResult->memberId ? 'App\Models\Member' : 'App\Models\Agent',
            'draw_money'            => $payResult->drawMoney,
            'deposit_money'         => $payResult->depositMoney,
            'gift_money'            => $payResult->giftMoney,
            'member_activity_id'    => $payResult->memberActivityId,
            'is_contain_activity'   => $payResult->isContainActivity,
            'is_cutoff'             => $payResult->isCutoff,
            'add_time'              => $payResult->dateTime,
            'order_created_time'    => $payResult->dateTime,
            'call_back_count'       => 1,
            // 先插入为进入队列 queue_ready ，队列中处理发现截单，再更新状态为 queue_no_enter
            'queue_status'          => $queue_status,
            'pay_status'            => $payResult->payStatus        //取款时会用到
        ]);
        return $paymentQueue;
    }

    //根据支付结果，给存/付款并记录日志
    public function addDepositMoneyAndRecordMovement(PayResult $payResult){
        $memberId         = $payResult->memberId;
        $memberActivityId = $payResult->memberActivityId;
        $depositMoney     = $payResult->depositMoney;
        $billNo           = $payResult->billNo;

        $depositApply = DepositApply::where([
            'bill_no' => $billNo,'deposit_status' => 'audit','is_deposit_money_given' => '0'
        ])->first();
        if($depositApply){
            $ret = Helper::upMoney($memberId,'App\Models\Member',$depositMoney,'deposit',null,$memberActivityId,
                $billNo);
            //如果是要求会员承担手续费的，那么直接扣除会员的余额
            if($depositApply->deposit_fee>0 && $depositApply->deposit_fee_bear==='member'){
                Helper::upMoney($memberId,'App\Models\Member',$depositMoney->deposit_fee,'deposit_fee',null,
                    $memberActivityId,$billNo);
	            Log::info('$$$$$ 会员承担手续费：' . $depositMoney->deposit_fee);
            }
            if($ret){
	            Log::info('$$$$$ 存款上分，单号:'.$billNo.'，会员Id ' . $memberId . '，存款金额：' . $depositMoney);
                //如果有 memberActivity 才能记录 …
                if($memberActivityId){
                    \Act::updateMemberActivityDepositMoney($memberActivityId,$depositMoney);
                }
                $result = $depositApply->update([
                    'is_deposit_money_given' => 1,'deposit_status' => 'succeed',
                    'rule_handel_time'       => $payResult->dateTime
                ]);
                return $result;
            }
	        Log::info('$$$$$ 存款上分失败，单号: ' . $billNo . '，存款金额：' . $depositMoney);
            return false;
        }
	    Log::info('$$$$$ addDepositMoneyAndRecordMovement 存款上分失败=》未找到存款订单，单号: ' . $billNo . '，存款金额：' . $depositMoney);
        return false;
    }

    //根据支付结果，给付赠金并记录日志
    public function addGiftMoneyAndRecordMovement(PayResult $payResult){
        $memberId = $payResult->memberId;
        $memberActivityId = $payResult->memberActivityId;
        $giftMoney = $payResult->giftMoney;
        $billNo = $payResult->billNo;

        $depositApply = DepositApply::where([['bill_no','=',$billNo],['is_gift_money_given','=','0']])->first();
        Log::info('$$$$$ 存款活动，BillNO:' . $billNo . ' 活动ID:' .$memberActivityId);
        if($depositApply){
            $memberActivity = MemberActivity::where(['member_activity_id' => $memberActivityId])->first();
            if($memberActivity){
                Log::info('$$$$$ $memberActivity ' . $memberActivityId . ' found');
                if($giftMoney>0){
                    $remark = $memberActivity->activity->activity_name;
                    $ret1 = Helper::upMoney($memberId,'App\Models\Member',$giftMoney,'gift_money',$remark,
                        $memberActivityId,$billNo);
                    if($ret1){
                        Log::info('$$$$$ new money to memberId ' . $memberId . ' with gift_money ' . $giftMoney);
                        \Act::updateMemberActivityGiftMoney($memberActivityId,$giftMoney);
                        //更新存款申请中的赠金 为已经给了
                        $result = $depositApply->update(['is_gift_money_given' => 1]);
                        //更新队列中的赠金为实际赠金金额
                        $paymentQueue = PaymentQueue::where(['bill_no' => $billNo])->first();
                        if($paymentQueue){
                            $result = $paymentQueue->update(['gift_money' => $giftMoney]);
                        }
                        if(empty($memberActivity->room_code)){
                            $result = $memberActivity->update([
                                'occur_time'      => date('Y-m-d H:i:s'),
                                'activity_status' => 'accepted'
                            ]);
                            CheckMemberActivity::dispatch($memberActivity)->onQueue('activity_check')
                                ->delay(now()->addSeconds(5));
                        }else{
                            $money = $depositApply->deposit_money - $depositApply->deposit_fee + $giftMoney;
                            $column = [
                                'transfer_out_room' => 'Wallet',
                                'transfer_in_room'  => $memberActivity->room_code,
                                'billNo'            => $this->getValidBillNo('Z'),
                                'money'             => $money,
                                'activityTransfer'  => true
                            ];
                            for($i = 0;$i<3;$i++){
                                $ret2 = RoomHelper::transferSyncInRoom($memberActivity->member_id,
                                    $memberActivity->room_code,$money);
                                if($ret2['status'] === SUCCESS){
                                    break;
                                }
                            }
                            if($ret2['status'] == SUCCESS){
                                //更新 memberActivity 的数据，会员选择的活动为已经接受
                                $transfer = RoomAccountTransfer::where('bill_no',$column['billNo'])->first();
                                $result = $memberActivity->update([
                                    'occur_time'      => date('Y-m-d H:i:s'),
                                    'activity_status' => 'accepted'
                                ]);
                                CheckMemberActivity::dispatch($memberActivity)->onQueue('activity_check')
                                    ->delay(now()->addSeconds(5));
                            }else{//转厅不成功，先把钱冻结
                                $ret3 = Helper::upMoney($memberId,'App\Models\Member',$money,'transfer_freeze',
                                    '付款成功时活动转厅失败，暂冻结',$memberActivityId,$billNo);
                                if($ret3){
                                    $result = $memberActivity->update(['activity_status' => 'transfer_freeze ']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /* 非存款类活动给付赠金 */
    public function addGiftMoneyAndRecordMovementForManualActivity(PayResult $payResult){
        $memberId = $payResult->memberId;
        $memberActivityId = $payResult->memberActivityId;
        $giftMoney = $payResult->giftMoney;

        $memberActivity = MemberActivity::where(['member_activity_id' => $memberActivityId])->first();
        if($memberActivity){
            Log::info('$$$$$ $memberActivity ' . $memberActivityId . ' found');
            if($giftMoney>0){
                $remark = $memberActivity->activity->activity_name;
                $ret1 = Helper::upMoney($memberId,'App\Models\Member',$giftMoney,'gift_money',$remark,
                    $memberActivityId);
                if($ret1){
                    Log::info('$$$$$ new money to memberId ' . $memberId . ' with gift_money ' . $giftMoney);
                    \Act::updateMemberActivityGiftMoney($memberActivityId,$giftMoney);
                    $moneyMvmtMemberId = $ret1->money_movement_id;
                    if(empty($memberActivity->room_code)){
                        $result = $memberActivity->update([
                            'activity_status' => 'accepted','occur_time' => date('Y-m-d H:i:s')
                        ]);
	                    Log::info('$$$$$ empty($memberActivity->room_code) CheckMemberActivity');
                        CheckMemberActivity::dispatch($memberActivity)->onQueue('activity_check')
                            ->delay(now()->addSeconds(5));
                    }else{
                        $money = $memberActivity->deposit_money+$giftMoney;
                        $column = [
                            'transfer_out_room' => 'Wallet',
                            'transfer_in_room'  => $memberActivity->room_code,
                            'billNo'            => $this->getValidBillNo('Z'),
                            'money'             => $money,
                            'activityTransfer'  => true
                        ];
                        for($i = 0;$i<3;$i++){
                            $ret = RoomHelper::transferSyncInRoom($memberActivity->member_id,$memberActivity->room_code,
                                $money);
                            if($ret['status'] === SUCCESS){
                                break;
                            }
                        }
                        if($ret['status'] == SUCCESS){
                            //更新 memberActivity 的数据，会员选择的活动为已经接受
                            $transfer = RoomAccountTransfer::where('bill_no',$column['billNo'])->first();
                            $result = $memberActivity->update([
                                'activity_status' => 'accepted','occur_time' => date('Y-m-d H:i:s')
                            ]);
                            CheckMemberActivity::dispatch($memberActivity)->onQueue('activity_check')
                                ->delay(now()->addSeconds(5));
                        }else{//转厅不成功，先把钱冻结
                            $ret2 = app('BaseRoom')->freezeTransferMoney($memberId,$money,$memberActivityId,
                                '活动转厅失败，暂冻结');
                            if($ret2){
                                $result = $memberActivity->update(['activity_status' => 'transfer_freeze ']);
                            }
                        }
                    }
                }
            }
        }
    }

    //冻结取款金额
    public function freezeDrawMoney($memberId,$money){
        $member = Member::where('member_id',$memberId)->first();
        if($member){
            $ret = $member->increment('freeze_draw',$money);
            return $ret;
        }
        return false;
    }

    //解冻取款金额
    public function unfreezeDrawMoney($memberId,$money){
        $member = Member::where('member_id',$memberId)->lockForUpdate()->first();
        if(!$member){
        	Log::info('【解冻取款金额】失败，用户不存在：Meberid'.$memberId);
	        return false;
        }
        if($member->freeze_draw>=$money){
            $ret = $member->decrement('freeze_draw',$money);
            return $ret;
        }
	    Log::info('【解冻取款金额】失败，冻结金额不足=》冻结金额：'.$member->freeze_draw.'，要解冻金额：'.$money.'，Mid'.$memberId);
        return false;
    }

    public function checkMoneyNeedByByPayClient(PayClient $payClient){
        $paymentMethodCode = $payClient->paymentMethodCode;
        $QRType = $payClient->QRType;
        $depositMoney = $payClient->depositMoney;

        $minMoney = 8;
        $maxMoney = 99999;
        if($depositMoney<$minMoney){
            return ['result' => false,'content' => '','message' => '低于最低金额，请确认！'];
        }
        if($depositMoney>$maxMoney){
            return ['result' => false,'content' => '','message' => '超过最低金额，请确认！'];
        }
        return ['result' => true,'content' => '','message' => ''];
    }

    public function handleQueueDeposit($queue,$isGiveGiftMoney = true,$remark = ''){
        $payResult = new PayResult();
        if(empty($queue)){
	        log::info('队列执行失败，未找到队列信息。。');
	        return ['status' => FAILED,'msg' => '更新失败！'];
        }
        $payResult->memberActivityId = $queue->member_activity_id;
        $payResult->memberId = $queue->member_agent_id;
        $payResult->depositMoney = $queue->deposit_money;
        $payResult->billNo   = $queue->bill_no;
        $payResult->dateTime = $queue->add_time;
        $payResult->isContainActivity = $queue->is_contain_activity;
        $payResult->isCutoff = $queue->is_cutoff;

        $ruleActClient = new RuleActClient();
        $ruleActClient->memberId = $queue->member_agent_id;
        $ruleActClient->depositMoney = $queue->deposit_money;
        $ruleActClient->memberActivityId = $queue->member_activity_id;
        $ruleActClient->billNo = $queue->bill_no;

        $depositApply = DepositApply::where('bill_no',$payResult->billNo)->lockForUpdate()->first();
        if($queue->pay_status == 'failed'){
            $res = $depositApply->update([
                'deposit_status' => 'fail','rule_handel_times' => now(),'description' => '存款失败'
            ]);
            if(!$res){
                \Log::info('存款订单：' . $payResult->billNo . '更新失败，原因=》队列执行失败！');
                return ['status' => FAILED,'msg' => '更新失败！'];
            }
            $memberActivity = $depositApply->memberActivity;
            if($memberActivity){
                $memberActivity->update(['activity_status' => 'reject','description' => '存款失败']);
            }
            return ['status' => SUCCESS,'msg' => '更新成功！'];
        }else{
            DB::beginTransaction();
            try{
                $ret = \Pay::addDepositMoneyAndRecordMovement($payResult);
                if(!$ret){
                    DB::rollback();
	                \Log::info('队列执行失败：原因=》上分不成功！单号：'.$queue->bill_no);
                    $queue->update(['queue_status' => 'queue_failed','order_finish_time' => date('Y-m-d H:i:s')]);
                    return ['status' => FAILED,'msg' => '上分失败！'];
                }
            }catch(Exception $exception){
                DB::rollback();
	            \Log::info('队列执行失败：原因=》'.$exception->getMessage().'，单号：'.$queue->bill_no);
                $queue->update(['queue_status' => 'queue_failed','order_finish_time' => date('Y-m-d H:i:s')]);
                return ['status' => FAILED,'msg' => '上分失败！'];
            }
            $member = Member::find($payResult->memberId);
            //更新会员总共存款数
            $member->increment('total_deposit',$depositApply->deposit_money);
            //更新收款账号收款信息
            $paymentAccount = PaymentAccount::where('payment_account_id',$depositApply->payment_account_id)
                ->lockForUpdate()->first();
            $paymentAccount->increment('income_current',$depositApply->deposit_money);
            $paymentAccount->increment('income_total',$depositApply->deposit_money);
            $where = [
                'payment_account_id'  => $depositApply->payment_account_id,
                'payment_method_code' => $depositApply->payment_method_code,
            ];
            if($depositApply->qr_type){
                $where['qr_type'] = $depositApply->qr_type;
            }
            if($paymentAccount->is_local){//本地收款走 PaymentAcctLocalMuti
                $paymentAcctLocalMuti = PaymentAcctLocalMuti::where('payment_acct_local_muti_id',
                    $depositApply->payment_account_local_muti_id)->lockForUpdate()->first();
                if($paymentAcctLocalMuti){
                    $paymentAcctLocalMuti->update([
                        'income_total'   => $paymentAcctLocalMuti->income_total+$depositApply->deposit_money,
                        'income_current' => $paymentAcctLocalMuti->income_current+$depositApply->deposit_money
                    ]);
                }
            }else{ //第三方收款走 PaymentAccountMethod
                $paymentAccountMethod = PaymentAccountMethod::where($where)->lockForUpdate()->first();
                PaymentAccountMethod::where($where)->update([
                    'method_income_total' => $paymentAccountMethod->method_income_total+$depositApply->deposit_money
                ]);
            }
            //检查是否第一次存款
            if(!$member->first_deposit_time){
                $member->update(['first_deposit_time' => $payResult->dateTime]);
            }
            if($payResult->isContainActivity == '1'){
                // 具体动作，要按照活动配置的 paymentAction 步骤来走，计算上分和赠金
                $memberActivity = MemberActivity::where('member_activity_id','=',$payResult->memberActivityId)->first();
                if($memberActivity && $isGiveGiftMoney){
                    $activity = $memberActivity->activity()->first();
                    if($activity){
                        $activityId = $activity->activity_id;
                        try{
                            \App::make('PaymentAction')->executePaymentAction($activityId,$ruleActClient);
                        }
                        catch(Exception $e){
                            DB::rollback();
                        }
                    }
                }else{
                    $memberActivity->update(['activity_status' => 'fail','description' => $remark]);
                }
            }
            //更新队列状态为成功
            $queue->update(['queue_status' => 'queue_succeed','order_finish_time' => date('Y-m-d H:i:s')]);
            DB::commit();
            return ['status' => SUCCESS,'msg' => '上分成功！'];
        }
    }

    public function handleQueueDraw($queue){
        DB::beginTransaction();
        $drawApply = PayOrder::where(['bill_no' => $queue->bill_no,'draw_status' => 'audit'])->first();
        if($drawApply){
            if($queue->pay_status == 'success'){
                //释放取款冻结金额
                $user_type = $drawApply->member_agent_type;
                if($user_type == 'App\Models\Member'){
                    $member = Member::where('member_id',$drawApply->member_agent_id)->first();
                    try{
                        $ret1 = Helper::upMoney($member->member_id,Member::class,$drawApply->draw_money,'money_draw');
                    }
                    catch(\Exception $exception){
                        DB::rollback();
                        throw new \Exception("Draw error:" . $exception->getMessage());
                    }

                    $ret2 = $member->increment('total_draw',$drawApply->draw_money);
                }
                //更新支付接口取款统计
                if(!empty($drawApply->payment_account_id)){
                    try{
                        $paymentAccount = PaymentAccount::find($drawApply->payment_account_id);
                        $paymentAccount->increment('payout_current',$drawApply->draw_money);
                        $paymentAccount->increment('payout_total',$drawApply->draw_money);
                        $paymentAccountMethod = PaymentAccountMethod::where([
                            'payment_account_id' => $drawApply->payment_account_id,'in_out_type' => 'out'
                        ])->first();
                        PaymentAccountMethod::where([
                            'payment_account_id' => $drawApply->payment_account_id,'in_out_type' => 'out'
                        ])->update([
                            'method_outcome_total' => $paymentAccountMethod->method_outcome_total+$drawApply->draw_money
                        ]);
                    }
                    catch(\Exception $exception){
                        DB::rollback();
                        throw new \Exception("Draw error:" . $exception->getMessage());
                    }
                }
                $drawApply->update(['draw_status' => 'success','rule_handel_time' => date('Y-m-d H:i:s')]);
            }else{
                //释放取款冻结金额
                try{
                    $user_type = $drawApply->member_agent_type;
                    $drawApply->update(['draw_status' => 'failed']);
                    Helper::upMoney($drawApply->member_agent_id,$user_type,$drawApply->draw_money,'money_unfreeze',
                        '取款失败解冻冻结金额',null,$drawApply->bill_no);
                    if($user_type == 'App\Models\Member'){
                        $member = Member::where('member_id',$drawApply->member_agent_id)->first();
                        $member->decrement('freeze_draw',$drawApply->draw_money);
                    }
                    if($user_type == 'App\Models\Agent'){
                        $agent = Agent::where('agent_id',$drawApply->member_agent_id)->first();
                        $agent->decrement('freeze_money',$drawApply->draw_money);
                    }
                }
                catch(\Exception $exception){
                    DB::rollback();
                    throw new \Exception("Draw error:" . $exception->getMessage());
                }
            }
        }
        //更新队列状态为成功
        $queue->update(['queue_status' => 'queue_succeed','order_finish_time' => date('Y-m-d H:i:s')]);
        DB::commit();
    }

    public static function post($url,$data,$sign){
        $header[] = "Content-Hmac:" . $sign;
        $header[] = "Content-Type: application/json";
        $MgCurl   = curl_init();
        curl_setopt($MgCurl,CURLOPT_URL,$url);
        curl_setopt($MgCurl,CURLOPT_POST,true);
        curl_setopt($MgCurl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($MgCurl,CURLOPT_HTTPHEADER,$header);
        curl_setopt($MgCurl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($MgCurl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($MgCurl,CURLOPT_RETURNTRANSFER,true);
        $obj = curl_exec($MgCurl);
        curl_close($MgCurl);
        return $obj;
    }
    
    public function queuetest($a){
        sleep(5);
        Log::info($a);
    }
}
