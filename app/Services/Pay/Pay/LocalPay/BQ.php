<?php
//BQ

namespace App\Services\Pay\Pay\LocalPay;

use App\Libs\Helper;
use App\Models\Agent;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Models\PaymentAcctLocalMuti;
use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BQ extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'BQ';
    public $paymentAccount;

    public function __construct(PayClient $payClient,PayResult $payResult){
        parent::__construct($payClient,$payResult);
    }

    public function Pay(PayClient $payClient,$paymentAccount){
        $this->payClient = $payClient;
        $this->assembleClient();
        $this->paymentAccount = $paymentAccount;
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
	    $member = Member::find($this->payClient->memberId);
        $this->flag      = ' 321';
        $paymentAccount  = $this->paymentAccount;
        $member_level_id = $member->member_level_id;
        $where  = [
            'is_allow_deposit'    => 1,
            'payment_account_id'  => $paymentAccount->payment_account_id,
            'payment_method_code' => $this->payClient->paymentMethodCode
        ];

        if($this->payClient->paymentMethodCode == 'QRCode'){
            $where['qr_type'] = $this->payClient->QRType;
        }
        $paymentBankAcct = PaymentAcctLocalMuti::where($where)->where(function($query) use($member_level_id){
        	$query->where('member_level_id',$member_level_id)->orWhere('member_level_id','0');
        })->orderBy('member_level_id','desc')->first();
        if(empty($paymentBankAcct)){
            return ['status' => FAILED,'msg' => '暂无收款卡！'];
        }
        if($this->payClient->paymentMethodCode == 'OnLine'){
            $tflag = $this->payClient->bankCode;
            $type = 'online';
        }elseif($this->payClient->paymentMethodCode == 'Local'){
            
            $data['deposit_money']   = $this->payClient->depositMoney;
            $data['bill_no']         = $this->payClient->billNo;
            $data['from_username']   = $this->payClient->bankAccountName;
            $data['from_cardnumber'] = $this->payClient->bankAccountNumber;
            $data['username']        = $member->display_name;
            $data['from_bankname']   = config('enums.bank_code')[$this->payClient->bankCode]??'';
            $data['remit']           = $this->payClient->remittanceInfo;
            $data['to_username']     = $paymentBankAcct->bank_account_name;
            $data['to_cardnumber']   = $paymentBankAcct->bank_account_number;
            $data['to_openingbank']  = $paymentBankAcct->opening_bank;
            $data['to_bankname']     = config('enums.bank_code')[$paymentBankAcct->bank_code]??'';
            $order['apikey']         = $this->payClient->infoAcct['apiKey'];
            $order['order_id']       = $this->payClient->billNo;
            $order['bank_flag']      = $paymentBankAcct->bank_code;
            $order['card_login_name'] = '';
            $order['card_number']     = $paymentBankAcct->bank_account_number;
            $order['pay_username']    = $this->payClient->bankAccountName;
            $order['pay_card_number'] = $this->payClient->bankAccountNumber;
            $order['amount']          = $this->payClient->depositMoney;
            $order['create_time']     = time();
            $order['comment']         = $this->payClient->remittanceInfo;

            $header = ['Content-Type:application/x-www-form-urlencoded'];
            $opts['http'] = [
                'header'  => $header,
                'timeout' => 30,
                'method'  => 'POST',
                'content' => json_encode($order)
            ];
            $context = stream_context_create($opts);
            $result  = file_get_contents($this->payClient->infoAcct['API_URL'] . 'place_order/',false,$context);
            $result  = json_decode($result,true);
            $deposit = DepositApply::where('bill_no',$this->payClient->billNo)->first();
            if(!$result['success']){
                Log::info('deposit money failed,bill_no: ' . $this->payClient->billNo . ' reason: '.$result['message']);
            }else{
                $deposit->update(['third_no'                   => $result['id'],
                                  'payment_acct_local_muti_id' => $paymentBankAcct->payment_acct_local_muti_id
                ]);
            }
	        log::info('【同略云本地收款】data=》'.json_encode($data));
            log::info('【同略云本地收款】URL=》'.BACKEND_URL . '/backend/localPay?' . http_build_query($data));
            return ['status'  => SUCCESS,
                    'content' => ['url' => BACKEND_URL . '/backend/localPay?' . http_build_query($data)],
                    'msg'     => '获取url成功'
            ];
        }elseif($this->payClient->paymentMethodCode == 'QRCode'){
            $type = 'qrcode';
            if($this->payClient->QRType === 'AliPay'){
                $tflag = 'ALIPAY';
            }elseif($this->payClient->QRType == 'QQPay'){
                $tflag = 'QQPAY';
            }elseif($this->payClient->QRType == 'WeiXin'){
                $tflag = 'WebMM';
            }else{
                return ['status' => FAILED,'msg' => '不支持该支付方式！'];
            }
        }else{
            return ['status' => FAILED,'msg' => '不支持该支付方式！'];
        }

        return ['status' => SUCCESS,'msg' => '存款申请提交成功','url' => '/api_web/deposit'];
    }

    public function thirdCallBack(Request $request){
        $notify_str = file_get_contents('php://input');
        Log::info('BQ-notify:'.$notify_str);
        $data = json_decode($notify_str,true);
        $depositApply = DepositApply::query()->where('third_no',$data['order_id'])->firstOrFail();

        // 组合 payResult 对象
        $payResult = $this->payResult;
        $payResult->billNo = $depositApply->bill_no;
        $payResult->depositMoney = $depositApply->deposit_money;
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->paymentMethodCode = $depositApply->payment_method_code;
        $payResult->dateTime = date('Y-m-d H:i:s');
        $payResult->allArgs = $notify_str;
        $payResult->payStatus = 'success';

        parent::handelThirdCallBack($payResult);
    }

    public function handelDepositThirdCallBack(Request $request,$depositApply){
        $str = "充值成功！订单号：" . $request["order_id"];
        Log::info('&&&&& ' . $str);
        $depositApply = DepositApply::find(['bill_no' => $request['order_id']])->first();
        if(!$depositApply){
            Log::info('充值订单：' . $request['order_id'] . '未找到');
            exit;
        }
        if($depositApply->deposit_money * 100 != $request['amount']){
            Log::info('充值订单：' . $request['order_id'] . '金额不对');
            exit();
        }

        $payResult = $this->payResult;
        $data = $request->all();
        $payResult->billNo = $data["order_id"];
        $payResult->depositMoney = $data["amount"] / 100;
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->paymentMethodCode = $depositApply->payment_method_code;
        $payResult->dateTime = date('Y-m-d H:i:s');
        $payResult->allArgs = json_encode($data);
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
        $data['time'] = time();
        $data['amount'] = $drawApply->draw_money-$drawApply->draw_fee;
        $data['order_id'] = $drawApply->bill_no;
        $data['to_bank_flag'] = $bankAccount->bank_code;
        $data['to_cardnumber'] = $bankAccount->bank_account_number;
        $data['to_username'] = $bankAccount->bank_account_name;
        $data['out_system'] = $info_acct['out_system'];

        $sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
        $res = $this->post($info_acct['PAYOUT_API_URL'],json_encode($data),$sign);
        $result = json_decode($res,true);
        if(!$result['success']){
            $drawApply->update(['rule_handel_time'      => Carbon::now(),
                                'payment_account_id'    => $paymentAccount->payment_account_id,
                                'payment_platform_code' => $this->payment_platform_code
            ]);
            \Log::info('汇款失败，错误信息【' . $result['msg'] . '】 -- 订单号：' . $drawApply->bill_no);
            return ['status' => FAILED,'content' => null,'msg' => '汇款失败，错误信息【' . $result['msg'] . '】'];
        }else{
            $drawApply->update(['rule_handel_time'   => Carbon::now(),
                                'payment_account_id' => $paymentAccount->payment_account_id,'draw_status' => 'audit'
            ]);
            return ['status' => SUCCESS,'content' => null,'msg' => '汇款进行中'];
        }
    }

    //查询订单状态
    public function queryWithdrawal($drawApplyId,$paymentAccount){
        $info_acct = json_decode($paymentAccount->info_acct,true);
        $drawApply = PayOrder::find($drawApplyId);
        $data['cid'] = $info_acct['CID'];
        $data['order_id'] = $drawApply->bill_no;
        $data['time'] = time();
        $sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
        $res = $this->post('https://www.dsdfpay.com/dsdf/api/query_withdraw',json_encode($data),$sign);
        Log::info('取款单：' . $drawApply->bill_no . '--' . $res);
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

    //取消订单 代收付系统需要的方法
    public function cancelBillNo(){
        $info_acct = $this->payClient->infoAcct;
        $bank_money = DepositApply::where([
            ['member_id','=',$this->payClient->memberId],
            ['created_at','>',date('Y-m-d H:i:s',time()-900)],
            ['bill_no','!=',$this->payClient->billNo]
        ])->rderBy('created_at','desc')->first();
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
        $key = $infoAcct['AIP_KEY'];
        $data['order_id'] = $request['order_id'];
        $data['amount'] = (int)$request['amount'];
        $sign = $request->header('Content-Hmac');
        $temp = json_encode($request->except('_url'));
        $dig64 = base64_encode(hash_hmac('sha1',(string)$temp,$key,true));
        Log::info('DSDF【sign】：' . $sign);
        Log::info('DSDF【temp】：' . $temp);
        Log::info('DSDF【dig64】：' . $dig64);
        Log::info('DSDF【data】：' . json_encode($request->except('_url')));

        if($dig64 != $sign){
            Log::info('DSDF【sign】：Signature error');
            exit('Signature error');
        }
    }
}
