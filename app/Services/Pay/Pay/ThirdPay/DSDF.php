<?php
//风云DD付

namespace App\Services\Pay\Pay\ThirdPay;

use App\Libs\Helper;
use App\Models\Agent;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;

class DSDF extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'DSDF';

    public function __construct(PayClient $payClient,PayResult $payResult){
        parent::__construct($payClient,$payResult);
    }

    public function Pay(PayClient $payClient,$paymentAccount){
        $this->payClient = $payClient;
        $this->assembleClient();
        $this->payClient->paymentAccountId = $paymentAccount->payment_account_id;
        $this->payClient->infoAcct = json_decode($paymentAccount->info_acct,true);

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
        $this->flag = '321';
        $this->cancelBillNo();
        $info_acct = $this->payClient->infoAcct;
        $data['cid']  = $info_acct['CID'];
        $data['uid']  = $this->payClient->login_name;
        $data['time'] = time();
        $data['amount']   = $this->payClient->depositMoney;
        $data['order_id'] = $this->payClient->billNo;
        $data['ip']       = $this->payClient->ip;
        $temp = '';
        foreach($data as $x => $x_value){
            $temp .= $x . "=" . $x_value . "&";
        }
        $temp  = substr($temp,0,-1);
        $dig64 = base64_encode(hash_hmac('sha1',$temp,$info_acct['AIP_KEY'],true));
        $sign  = urlencode($dig64);
        $url   = $info_acct['API_URL'];
        if($this->payClient->paymentMethodCode == 'OnLine'){
            $tflag = Helper::getPaymentPlatformBank($this->payment_platform_code,$this->payClient->bankCode);
            $type = 'online';
        }elseif($this->payClient->paymentMethodCode == 'Manual'){
            $data['from_username'] = $this->payClient->bankAccountName;
            $data['from_cardnumber'] = $this->payClient->bankAccountNumber;
            $data['comment'] = $this->payClient->remittanceInfo;
            $type  = 'remit';
            $tflag = $this->payClient->bankCode;
        }elseif($this->payClient->paymentMethodCode == 'AP2Bank'){
	        $data['from_username'] = $this->payClient->bankAccountName;
	        $data['from_cardnumber'] = $this->payClient->bankAccountNumber;
	        $data['comment'] = $this->payClient->remittanceInfo;
	        $type  = 'remit';
	        $tflag = 'ALIPAY';
        }elseif($this->payClient->paymentMethodCode == 'WX2Bank'){
	        $data['from_username'] = $this->payClient->bankAccountName;
	        $data['from_cardnumber'] = $this->payClient->bankAccountNumber;
	        $data['comment'] = $this->payClient->remittanceInfo;
	        $type  = 'remit';
	        $tflag = 'WebMM';
        }elseif($this->payClient->paymentMethodCode == 'AliPay'){
            $type  = 'qrcode';
            $tflag = 'ALIPAY';
        }elseif($this->payClient->paymentMethodCode == 'QQPay'){
            $type  = 'qrcode';
            $tflag = 'QQPay';
        }elseif($this->payClient->paymentMethodCode == 'WeiXin'){
            $type  = 'qrcode';
            $tflag = 'WebMM';
        }elseif($this->payClient->paymentMethodCode == 'FastPay'){
            $type = 'quick';
        }elseif($this->payClient->paymentMethodCode == 'Counter'){
			Log::info('【风云DSDF】选择聚合支付，由会员自行选择支付方式');
        }else{
            return ['status' => FAILED,'msg' => '不支持该支付方式！'];
        }
	    if(isset($type)){
		    $data['type'] = $type;
	    }

        if(isset($tflag)){
            $data['tflag'] = $tflag;
        }
        $data['sign'] = $sign;
	    $data['use_card_mch'] = $info_acct['use_card_mch']??0;
        $http = '';
        foreach($data as $x => $x_value){
            $http .= $x . "=" . $x_value . "&";
        }
        Log::info('【风云DSDF】=>URL Before:'.$http);
        $http = substr($http,0,-1);
	    Log::info('【风云DSDF】=>URL After:'.$http);
        return ['status' => SUCCESS,'content' => ['url' => $url . '?' . ($http)],'msg' => '获取url成功'];
    }

    public function thirdCallBack(Request $request){
        Log::info('DSDF【data】：' . json_encode($request->except('_url')));
        $bill_no = $request['order_id'];
        if(empty($bill_no)){
            exit('参数错误，订单号为空');
        }
        $pay_type = $request['direction'];
        if($pay_type == 'in'){
            $order = DepositApply::where('bill_no',$bill_no)->with('paymentAccount')->first();
            if(empty($order)){
                exit('参数错误,订单不存在');
            }
            $infoAcct = json_decode($order->paymentAccount->info_acct,true);
            $this->checkSign($request,$infoAcct);
            $this->handelDepositThirdCallBack($request);
        }elseif($pay_type == 'out'){
            $order = PayOrder::where('bill_no',$bill_no)->with('paymentAccount')->first();
            $infoAcct = json_decode($order->paymentAccount->info_acct,true);
            $this->checkSign($request,$infoAcct);
            $this->handelDrawThirdCallBack($request);
        }else{
            exit('参数错误,direction类型不符');
        }
        //回应第三方
        echo 'true';
    }

    public function handelDepositThirdCallBack(Request $request){
        $str = "充值成功！订单号：" . $request["order_id"];
        Log::info('&&&&& ' . $str);
        $depositApply = DepositApply::where(['bill_no' => $request['order_id']])->first();
        if(!$depositApply){
            Log::info('充值订单：' . $request['order_id'] . '未找到');
            exit;
        }
        if($depositApply->deposit_money * 100 != $request['amount']){
            Log::info('充值订单：' . $request['order_id'] . '金额不对');
            exit();
        }

        // 组合 payResult 对象
        $payResult = $this->payResult;
        $data = $request->all();
        $payResult->billNo = $data["order_id"];
        $payResult->depositMoney = $data["amount"] / 100;
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->paymentMethodCode = $depositApply->payment_method_code;
        $payResult->dateTime  = date('Y-m-d H:i:s');
        $payResult->allArgs   = json_encode($data);
        $payResult->payStatus = $request['status'] == 'verified' ? 'success' : 'failed';

        parent::handelThirdCallBack($payResult);
    }

    public function handelDrawThirdCallBack(Request $request){
        $drawApply = PayOrder::where(['bill_no' => $request['order_id']])->first();
        if(!$drawApply){
            Log::info('出款订单：' . $request['order_id'] . '未找到');
            exit;
        }
        if(($drawApply->draw_money-$drawApply->draw_fee) * 100 != $request['amount']){
            Log::info('出款订单：' . $request['order_id'] . '金额不对');
            exit();
        }
        $payResult = $this->payResult;
        $data = $request->all();
        $payResult->billNo = $data['order_id'];
        if($drawApply->member_agent_type == 'App\Models\Member'){
            $payResult->memberId = $drawApply->member_agent_id;
        }else{
            $payResult->agentId = $drawApply->member_agent_id;
        }
        $payResult->drawMoney = $drawApply->draw_money;
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->dateTime = $drawApply->created_at;
        $payResult->allArgs = json_encode($data);
        $payResult->payStatus = $request['status'] == 'verified' ? 'success' : 'failed';

        parent::handelThirdDrawCallBack($payResult);
    }

    public function assembleClient(){
        parent::assembleClient();
        $this->payClient->paymentPlatformCode = $this->payment_platform_code;
        if(!empty($this->payClient->activityId)){
            $this->needActivity = true;
        }
    }

    public function genDepositApply(){
	    $despApply = DepositApply::where('member_id',$this->payClient->memberId)
		    ->where('deposit_status','applied')->count();
	    if($despApply>0){
		    return false;
	    }
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
            //支付方式手续费
            'deposit_fee' => $this->getDepositFeeRatio($this->payClient),
            //手续费承担对象
            'deposit_fee_bear'=>Helper::getSetting('DEPOSIT_FEE_BEAR',$this->payClient->companyId)
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

        $this->checkBillNo();
        $bankAccount = $drawApply->bankAccount;
        $data['cid'] = $info_acct['CID'];
        $data['uid'] = $user->login_name;
        $data['time']   = time();
        $data['amount'] = $drawApply->draw_money-$drawApply->draw_fee;
        $data['order_id'] = $drawApply->bill_no;
        $data['to_bank_flag']  = $bankAccount->bank_code;
        $data['to_cardnumber'] = $bankAccount->bank_account_number;
        $data['to_username']   = $bankAccount->bank_account_name;
        $data['out_system']    = $info_acct['out_system'];
	    $data['use_card_mch']  = $info_acct['use_card_mch']??0;
        $sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
	    Log::info('【风云DSDF】Data=>'.json_encode($data));
        $res     = $this->post($info_acct['PAYOUT_API_URL'],json_encode($data),$sign);
        $result  = json_decode($res,true);
	    $adminId = Auth::guard('admin')->user()->admin_id??null;
        if(!$result['success']){
	        $drawApply->update([
		        'rule_handel_time'      => Carbon::now(),
		        'payment_account_id'    => $paymentAccount->payment_account_id,
		        'payment_platform_code' => $this->payment_platform_code
	        ]);
            Log::info('【风云DSDF】汇款失败，错误信息【' . $result['msg'] . '】 -- 订单号：' . $drawApply->bill_no);
            return ['status' => FAILED,'content' => null,'msg' => '汇款失败，错误信息【' . $result['msg'] . '】'];
        }else{
	        $drawApply->update([
		        'rule_handel_time'      => Carbon::now(),
		        'payment_account_id'    => $paymentAccount->payment_account_id,
		        'payment_platform_code' => $this->payment_platform_code,
		        'draw_status'           => 'audit',
		        'admin_id'              => $adminId,
	        ]);
            return ['status' => SUCCESS,'content' => null,'msg' => '汇款进行中'];
        }
    }

    //查询订单状态，除了正常回调外，用户可以在后台手动查询出款订单状态
    public function queryWithdrawal($drawApplyId,$paymentAccount){
        $info_acct = json_decode($paymentAccount->info_acct,true);
        $drawApply = PayOrder::find($drawApplyId);
        $data['cid'] = $info_acct['CID'];
        $data['order_id'] = $drawApply->bill_no;
        $data['time'] = time();
        $sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
        $res = $this->post('https://www.dsdfpay.com/dsdf/api/query_withdraw',json_encode($data),$sign);
        Log::info('【风云DSDF】取款单查询：' . $drawApply->bill_no . '--' . $res);
        $res = json_decode($res,true);
        if(!$res['success']){
            return ['status' => FAILED,'msg' => $res['msg']];
        }else{
            $status = $res['order']['status'];
            switch($status){
                case 'created':
                    $msg = '创建等待支付';
                    break;
                case 'timeout':
                    $msg = '超时';
                    break;
                case 'revoked':
                    $msg = '订单已被撤销';
                    break;
                case 'verified':
                    $msg = '订单已经完成';
                    break;
                default :
                    $msg = '未找到';
            }
            return ['status' => SUCCESS,'content' => '','msg' => $msg];
        }
    }

    //取消订单 代收付系统需要的方法，DSDF 规则要求，发起前先查询并取消
    public function cancelBillNo(){
        $info_acct = $this->payClient->infoAcct;
        $bank_money = DepositApply::where([
            ['member_id','=',$this->payClient->memberId],
            ['created_at','>',date('Y-m-d H:i:s',time()-900)],
            ['bill_no','!=',$this->payClient->billNo]
        ])->orderBy('created_at','desc')->first();
        if(empty($bank_money)){
            return ['status' => SUCCESS,'content' => null,'msg' => ''];
        }
        $data['cid'] = $info_acct['CID'];
        $data['order_id'] = $bank_money->bill_no;
        $data['time'] = time();
        $sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
        $res = $this->post('https://www.dsdfpay.com/dsdf/api/revoke_order',json_encode($data),$sign);
        return ['status' => SUCCESS,'content' => null,'msg' => ''];
    }

    //模拟请求
    public static function post($url,$data,$sign){
        $header[] = "Content-Hmac:" . $sign;
        $header[] = "Content-Type: application/json";
        $MgCurl = curl_init();
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

    public function checkSign(Request $request,$infoAcct){
    	try{
		    $key = $infoAcct['AIP_KEY'];
		    $a   = $request->all();
		    $s1  = "order_id={$a['order_id']}&amount={$a['amount']}&verified_time={$a['verified_time']}";
		    $s2  = $a['qsign'];
		    $dig64 = base64_encode(hash_hmac('sha1',$s1,$key,true));
		    Log::info('【风云DSDF】sign：'.$s2);
		    //Log::info('【风云DSDF】temp：'.$s1);
		    Log::info('【风云DSDF】dig64：'.$dig64);
		    //Log::info('【风云DSDF】data：'.json_encode($request->all()));
		    if($dig64 != $s2){
			    Log::info('DSDF【sign】：签名验证失败');
			    exit('Signature error');
		    }
	    }catch(\Exception $exception){
		    Log::info('DSDF【sign】：签名验证失败=>'.$exception->getMessage());
		    exit('Signature error');
	    }



    }
}
