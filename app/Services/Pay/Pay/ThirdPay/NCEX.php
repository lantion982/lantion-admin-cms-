<?php
//NCEX

namespace App\Services\Pay\Pay\ThirdPay;

use App\Models\Agent;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use App\Services\Room\Room\MyCurl;
use App\Libs\Mobile_Detect;

class NCEX extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'NCEX';
		public $detect = null;
    public function __construct(PayClient $payClient,PayResult $payResult){
        parent::__construct($payClient,$payResult);
	      $this->detect = new Mobile_Detect;
    }

    public function Pay(PayClient $payClient,$paymentAccount){
        $this->payClient = $payClient;
        $this->assembleClient();
        $this->payClient->paymentAccountId = $paymentAccount->payment_account_id;
        $this->payClient->infoAcct = json_decode($paymentAccount->info_acct,true);

        $this->checkBillNo();

        DB::beginTransaction();

        $ret1 = $this->genMemberActivity();
        $ret2 = $this->genDepositApply();
        if(!$ret1 || !$ret2){
            DB::rollback();
        }else{
            DB::commit();
        }

        return $this->doPayIn();
    }

    public function doPayIn(){
        $this->flag = ' 321';
        $info_acct = $this->payClient->infoAcct;
        $merchant_id = $info_acct['merchant_id'];
        $order_money = number_format($this->payClient->depositMoney,2,'.', '');
        $order_no = $this->payClient->billNo;
        $notify_url = CALLBACK_URL . '/callback/thirdCallBack/' . $this->payment_platform_code;
        $md5key = $info_acct['md5key'] ?? '';


	    $token_id = '3';
	
        $data['out_trade_no'] = $order_no;
	    $data['merchant_id'] = $merchant_id;
	    $data['token_id'] = $token_id;
	    $data['nonstr'] = substr(Uuid::uuid4()->getHex() , 0 , 16);
	    $data['body'] = '娱乐';
	    $data['detail'] = '娱乐';
	    $data['amount'] = $order_money;
	    $data['notify_url'] = $notify_url;
        $data['valid_time'] = 900;
	
	    if( $this->detect->isiOS() || $this->detect->isAndroidOS() ){
				  Log::info('NCEX【drvice】：android or iOS'.$this->payClient->domain.'/#/user');
		      $data['redirect_url'] = 'https://'.$this->payClient->domain.'/#/user';
				}else{
				  Log::info('NCEX【drvice】：PC'.$this->payClient->domain.'/#/user/info');
					$data['redirect_url'] = 'https://'.$this->payClient->domain.'/#/user/info';
				}
	    

       /* $data = array(
	          'out_trade_no'=> $order_no,
            'merchant_id' => $merchant_id,
            'token_id'    => $token_id,
            'nonstr'      => substr(Uuid::uuid4()->getHex() , 0 , 16),
	          'body'        => '娱乐',
	          'detail'      => '娱乐',
            'amount'      => $order_money,
	          'notify_url'  => $notify_url,
	          'redirect_url' => 'http://'.$_SERVER['HTTP_HOST'],
        );*/

        $data2 = array_filter($data,function($v){
            if($v !== '' && $v !== null){
                return true;
            }
        });
        ksort($data2);
        $sign = strtoupper(md5(urldecode(http_build_query($data2) . '&key=' . $md5key)));
        $data['sign'] = $sign;
	
	    $myCurl = new MyCurl();
	    $result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/unifiedorder',$data);
        //Log::info('NCEX【url】：' . $info_acct['api_url'].'/api/unifiedorder');
        //Log::info('NCEX【request】：' . $data);
        //Log::info('NCEX【result】：' . $result);
	    $json = json_decode($result, true);
	    $code = $json['code'];
	    if (trim($code) == '0'){
		    $third_order_no = $json['desc']['order_no'];
		    //Log::info('NCEX【order_no】：' . $third_order_no);
	        // 数据库记录 order_no
		    $this->updateDepositBillAtOnce($third_order_no);



		    /*if( $this->detect->isiOS() || $this->detect->isAndroidOS() ){
			    Log::info('NCEX【drvice】：mobile return');
			    $data3['ccp_order'] = $third_order_no;
			    return ['status'  => SUCCESS,'content' => ['url' => BACKEND_URL . '/backend/ncexPay?' . http_build_query($data3)],'msg' => '获取url成功'];
		    }else{
			    Log::info('NCEX【drvice】：PC');
			    return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
		    }*/

		    if( $this->detect->isiOS() || $this->detect->isAndroidOS() ){
			    $url = $info_acct['gateway_url'] .'/#/?ccp='.$third_order_no.'&chanel=H5';
		    }else{
			    $url = $info_acct['gateway_url'] .'/#/?ccp='.$third_order_no.'&chanel=H5';
		    }

		    return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
	    }else{
		    return ['status' => FAILED,'content' => ['url' => ''],'msg' => '获取url失败'];
	    }
    }

	public function updateDepositBillAtOnce($third_order_no){
		$order = DepositApply::query()->where('bill_no',$this->payClient->billNo)->first();
		//Log::info('CCEX【updateDepositBillAtOnce】：' . $bill_no);
		if(empty($order)){
			//Log::info('CCEX【updateDepositBillAtOnce】：参数错误');
			exit('参数错误！');
		}else{
			$order->update(['third_no' => $third_order_no]);
		}
	}
	
		public function updateDrawBillAtOnce($bill_no,$third_order_no){
			$order = PayOrder::where('bill_no',$bill_no)->first();
			Log::info('NCEX【updateDrawBillAtOnce】：' . $bill_no);
			if(empty($order)){
				Log::info('NCEX【updateDrawBillAtOnce】：参数错误');
				exit('参数错误！');
			}else{
				$order->update(['third_no' => $third_order_no]);
			}
		}

    public function thirdCallBack(Request $request){
    
    }

    public function handelDepositThirdCallBack(Request $request,$depositApply){
        $str = "充值成功！订单号：" . $request["order_no"];
        Log::info('&&&&& ' . $str);
        // 组合 payResult 对象
        $payResult = $this->payResult;
        $data = $request->all();
        $payResult->billNo = $data["order_no"];

        $payResult->depositMoney = $data["order_money"];
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->paymentMethodCode = $depositApply->payment_method_code;
        $payResult->dateTime = date('Y-m-d H:i:s');
        $payResult->allArgs = json_encode($data);
        $payResult->payStatus = $request['order_status'] == 'success' ? 'success' : 'failed';
        parent::handelThirdCallBack($payResult);
    }

    public function assembleClient(){
        parent::assembleClient();
        $this->payClient->paymentPlatformCode = $this->payment_platform_code;
        if(!empty($this->payClient->activityId)){
            $this->needActivity = true;
        }
    }

    public function genDepositApply(){
        $data = [
            'bill_no'               => $this->payClient->billNo,
            'deposit_money'         => $this->payClient->depositMoney,
            'bank_account_name'     => $this->payClient->bankAccountName,
            'bank_account_number'   => $this->payClient->bankAccountNumber,
            'remittance_info'       => $this->payClient->remittanceInfo,
            'member_id'             => $this->payClient->memberId,
            'company_id'            => $this->payClient->companyId,
            'qr_type'               => $this->payClient->QRType,
            'bank_code'             => $this->payClient->bankCode,
            'room_code'             => $this->payClient->roomCode,
            'opening_bank'          => $this->payClient->openingBank,
            'payment_account_id'    => $this->payClient->paymentAccountId,
            'deposit_time'          => $this->payClient->depositTime,
            'payment_method_code'   => $this->payClient->paymentMethodCode,
            'payment_platform_code' => $this->payClient->paymentPlatformCode,
            'member_activity_id'    => $this->payClient->memberActivityId,
        ];

        $depositApply = DepositApply::create($data);
        return $depositApply;
    }

    public function genMemberActivity(){
        if(!$this->needActivity){
            return true;
        }else{
            $memberActivity = MemberActivity::create([
                'member_id'       => $this->payClient->memberId,
                'activity_id'     => $this->payClient->activityId,
                'room_code'       => $this->payClient->roomCode,
                'related_info'    => $this->payClient->billNo,
                'activity_status' => 'applied',
                'occur_time'      => $this->payClient->depositTime,
                'ip'              => $this->payClient->ip,
                'is_finished'     => '0',
            ]);
            $this->payClient->memberActivityId = $memberActivity->member_activity_id;
            return $memberActivity;
        }
    }

    public function payOut($drawApplyId,$paymentAccount){
	    $info_acct = json_decode($paymentAccount->info_acct,true);
	    $merchant_id = $info_acct['merchant_id'];
	    $md5key = $info_acct['md5key'] ?? '';
	    $drawApply = PayOrder::find($drawApplyId);
	    if(!$drawApply){
		    return ['status' => FAILED,'msg' => '未找到该订单！'];
	    }
	    if($drawApply->draw_status != 'accept'){
		    return ['status' => FAILED,'msg' => '该订单状态下不能进行出款！'];
	    }
	    if($drawApply->member_agent_type == Member::class){
		    $user = Member::find($drawApply->member_agent_id);
	    }else{
		    $user = Agent::find($drawApply->member_agent_id);
	    }
	    $order_no = $drawApply->bill_no;
	    $bankAccount = $drawApply->bankAccount;
	
		//出款是固定的 CNT 代币
	    
	    $data = array(
		    'merchant_id'     => $merchant_id,
		    'out_trade_no'    => $order_no ,
		    'uid'             => $bankAccount->bank_account_number,
		    'email'           => $bankAccount->opening_bank,
		    'token_id'        =>'3',
		    'amount'          => number_format($drawApply->draw_money-$drawApply->draw_fee,2,'.', ''),
		    'nonstr'          => substr(Uuid::uuid4()->getHex() , 0 , 16),
		    'body'            => $user->login_name,
		    'detail'          => $user->login_name,
	    );
	    $data2 = array_filter($data,function($v){
		    if($v !== '' && $v !== null){
			    return true;
		    }
	    });
	    ksort($data2);
	    $sign = strtoupper(md5(urldecode(http_build_query($data2) . '&key=' . $md5key)));
	    $data['sign'] = $sign;
	
	    Log::info('NCEX payout param:' .json_encode($data));
	    
	    $myCurl = new MyCurl();
	    $result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/refund',$data);
	    Log::info('NCEX payout result:' . $result);
	    $json = json_decode($result, true);
	    $code = $json['code'];
	    //Log::info('NCEX payout code:' . $code);
	    if (trim($code) == '0'){
		   
		    $out_trade_no = $json['desc']['out_trade_no'];
		    if($out_trade_no == $order_no){
		    	// 直接更新
			    $drawApply = PayOrder::where(['bill_no' => $order_no])->first();
			    if(!$drawApply){
				    \Log::info('出款订单：' . $order_no . '未找到');
				    exit;
			    }
			    $transfer_amount = $json['desc']['transfer_amount'];
			    if(($drawApply->draw_money-$drawApply->draw_fee) !=  number_format($transfer_amount,2,'.', '')){
				    \Log::info('出款订单：' . $order_no . '金额不对');
				    exit();
			    }
			    // 先把订单改成 audit 状态
			    $drawApply->update([
				    'rule_handel_time'   => Carbon::now(),
				    'payment_account_id' => $paymentAccount->payment_account_id,
				    'payment_platform_code' => $this->payment_platform_code,
				    'draw_status' => 'audit'
			    ]);
			    
			    $payResult = $this->payResult;
			    
			    $payResult->billNo = $order_no;
			    if($drawApply->member_agent_type == 'App\Models\Member'){
				    $payResult->memberId = $drawApply->member_agent_id;
			    }else{
				    $payResult->agentId = $drawApply->member_agent_id;
			    }
			    $payResult->drawMoney = $drawApply->draw_money;
			    $payResult->paymentPlatformCode = $this->payment_platform_code;
			    $payResult->dateTime = $drawApply->created_at;
			    $payResult->allArgs = json_encode( $json['desc']);
			    $payResult->payStatus = 'success' ;
			    parent::handelThirdDrawCallBack($payResult);
		    }
		    //无须主动查询，所以不用记录，也没得获取第三方订单号
		    //$this->updateDrawBillAtOnce($order_no,$third_order_no);
		    return ['status' => SUCCESS,'content' => null,'msg' => '汇款成功'];
	    }else if(trim($code) == '-11'){
		    //Log::info('汇款失败，重复出款订单 -- 订单号：' . $drawApply->bill_no);
		    return ['status' => FAILED,'content' => null,'msg' => '清单号重复，您已经提交过出款申请！'];
	    }else if(trim($code) == '-6'){
		    //Log::info('汇款失败，重复出款订单 -- 订单号：' . $drawApply->bill_no);
		    return ['status' => FAILED,'content' => null,'msg' => '参数不完整，请联系技术处理！'];
	    }else if(trim($code) == '-12'){
		    //Log::info('汇款失败，重复出款订单 -- 订单号：' . $drawApply->bill_no);
		    return ['status' => FAILED,'content' => null,'msg' => '收款账号和ID不匹配，请备注并联系客户！'];
	    }else{
		    //Log::info('汇款失败，错误信息【' . $json['desc'] . '】 -- 订单号：' . $drawApply->bill_no);
		    return ['status' => FAILED,'content' => null,'msg' => '汇款失败'];
	    }
    }

    //查询订单状态
    public function queryWithdrawal($drawApplyId,$paymentAccount)
    {
	    $info_acct = json_decode($paymentAccount->info_acct, true);
	    $merchant_id = $info_acct['merchant_id'];
	    $md5key = $info_acct['md5key'] ?? '';
	    $drawApply = PayOrder::find($drawApplyId);
	    if (!$drawApply) {
		    return ['status' => FAILED, 'msg' => '未找到该订单！'];
	    }
	    $order_no = $drawApply->bill_no;
	
	    $data = array(
		    'merchant_id' => $merchant_id,
		    'out_trade_no' => $order_no,
		    'nonstr' => substr(Uuid::uuid4()->getHex(), 0, 16),
	    );
	    $data2 = array_filter($data, function ($v) {
		    if ($v !== '' && $v !== null) {
			    return true;
		    }
	    });
	    ksort($data2);
	    $sign = strtoupper(md5(urldecode(http_build_query($data2) . '&key=' . $md5key)));
	    $data['sign'] = $sign;
	
	    //Log::info('NCEX payout query param:' . json_encode($data));
	
	    $myCurl = new MyCurl();
	    $result = $myCurl->postHtmlBbin($info_acct['api_url'] . '/api/refund/query', $data);
	    //Log::info('NCEX payout query result:' . $result);
	    $json = json_decode($result, true);
	    $code = $json['code'];
	    //Log::info('NCEX payout query code:' . $code);
	    if (trim($code) == '0') {
		    $out_trade_no = $json['desc']['out_trade_no'];
		    if ($out_trade_no == $order_no) {
			    return ['status' => SUCCESS, 'content' => '', 'msg' => '订单已经完成'];
		    }else{
			    return ['status' => SUCCESS, 'content' => '', 'msg' => '未知错误'];
		    }
	    } else {
		    return ['status' => SUCCESS, 'content' => '', 'msg' => '查询失败'];
	    }
	
    }
}
