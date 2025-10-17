<?php
//闪讯付

namespace App\Services\Pay\Pay\ThirdPay;

use App\Libs\Helper;
use App\Models\DepositApply;
use App\Models\MemberActivity;
use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SanXunFu extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'SanXunFu';

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
        $this->flag = ' 321';
        $info_acct = $this->payClient->infoAcct;
        $merchant_no = $info_acct['merchant_no'];
        $order_money = $this->payClient->depositMoney;
        $order_no = $this->payClient->billNo;
        $notify_url = CALLBACK_URL . '/callback/thirdCallBack/' . $this->payment_platform_code;
        $key = $info_acct['md5key'] ?? '';

        $data = [
            'oid_partner' => $merchant_no,
            'notify_url'  => $notify_url,
            'user_id'     => round(1,1000),
            'sign_type'   => 'MD5',
            'no_order'    => $order_no,
            'time_order'  => date('Y-m-d H:i:s'),
            'money_order' => $order_money,
            'name_goods'  => md5(str_random(4)),
        ];
        ksort($data);
        $sign = md5(urldecode(http_build_query($data) . $key));
        $data['sign'] = $sign;
        $encryptDataBefore = [
            'data'        => $data,
            'method'      => 'post',
            'gateway_url' => $info_acct['gateway_url']
        ];
        $encryptData = encrypt($encryptDataBefore);
        $url = BACKEND_URL . '/callback/httpForm?encryptData=' . $encryptData;

        return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
    }

    public function thirdCallBack(Request $request){
        $str = file_get_contents("php://input");
        Log::info('SanXunFu【data】：' . $str);
        $data = json_decode($str,true);
        if(empty($data['no_order'])){
            exit('参数错误！');
        }
        $order = DepositApply::where('bill_no',$data['no_order'])->with('paymentAccount')->first();
        if(empty($order)){
            exit('参数错误！');
        }
        $infoAcct = json_decode($order->paymentAccount->info_acct,true);
        $sign = $data['sign'];
        unset($data['sign']);
        $data2 = array_filter($data,function($v){
            if($v !== '' && $v !== null){
                return true;
            }
        });
        ksort($data2);
        $md5key = $infoAcct['md5key'];
        $sign2 = md5(urldecode(http_build_query($data2) . '&key=' . $md5key));
        if($sign !== $sign2){
            exit('签名错误!');
        }else{
            echo(json_encode(['ret_code' => "0000",'ret_msg' => '交易成功'],JSON_UNESCAPED_UNICODE));
        }
        $this->handelDepositThirdCallBack($request,$order);
    }

    public function handelDepositThirdCallBack($receiveData,$depositApply){
        // 组合 payResult 对象
        $payResult = $this->payResult;
        $data = $receiveData;
        $payResult->billNo = $data["no_order"];

        $payResult->depositMoney = $data["money_order"];
        $payResult->paymentPlatformCode = $this->payment_platform_code;
        $payResult->paymentMethodCode = $depositApply->payment_method_code;
        $payResult->dateTime = date('Y-m-d H:i:s');
        $payResult->allArgs = json_encode($data);
        $payResult->payStatus = $data['result_pay'] == 'SUCCESS' ? 'success' : 'failed';
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

        return '不支持代付';
    }

    // 查询订单状态 */
    public function queryWithdrawal($drawApplyId,$paymentAccount){

    }

}