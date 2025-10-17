<?php
//本地支付，未使用
namespace App\Services\Pay\Pay;

use App\Models\DepositApply;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Models\PaymentAcctLocalMuti;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalPay extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'LocalPay';

    public function __construct(PayClient $payClient,PayResult $payResult){
        parent::__construct($payClient,$payResult);
    }

    public function Pay(PayClient $payClient,$paymentAccount){

        //调用方法组合 payClient ，该方法先调用父类的方法，再根据自己情况，调用子类方法 */
        $this->payClient = $payClient;
        $this->assembleClient();
        //收款账户，在进入Pay的时候，就已经在 PayController 中决定了 */
        $this->payClient->paymentAccountId = $paymentAccount->payment_account_id;
        $this->payClient->infoAcct = json_decode($paymentAccount->info_acct,true);
        //订单号检查暂时可不写，因为订单号采用 UUID 方式生成，同时生成 快捷码供用户查询 */
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
        $paymentAccount = $this->paymentAccount->find($this->payClient->paymentAccountId);
        $paymentBankAcct = PaymentAcctLocalMuti::where([
            'payment_account_id' => $paymentAccount->payment_account_id,'bank_code' => $this->payClient->bankCode,
            'is_allow_deposit'   => 1
        ])
            ->orderBy('income_current')->first();
        $this->flag = ' 321';
        if($this->payClient->paymentMethodCode == 'OnLine'){
            $tflag = $this->payClient->bankCode;
            $type = 'online';
        }elseif($this->payClient->paymentMethodCode == 'Manual'){
            $member = Member::find($this->payClient->memberId);
            $data['deposit_money'] = $this->payClient->depositMoney;
            $data['bill_no'] = $this->payClient->billNo;
            $data['from_username'] = $this->payClient->bankAccountName;
            $data['from_cardnumber'] = $this->payClient->bankAccountNumber;
            $data['username'] = $member->display_name;
            $data['from_bankname'] = config('enums.bank_code')[$this->payClient->bankCode];
            $data['remit'] = $this->payClient->remittanceInfo;
            $data['to_username'] = $paymentBankAcct->bank_account_name;
            $data['to_cardnumber'] = $paymentBankAcct->bank_account_number;
            $data['to_openingbank'] = $paymentBankAcct->opening_bank;
            $data['to_bankname'] = config('enums.bank_code')[$paymentAccount->bank_code];
            $order['apikey'] = $this->payClient->infoAcct['apiKey'];
            $order['order_id'] = $this->payClient->billNo;
            $order['bank_flag'] = $this->payClient->bankCode;
            $order['card_login_name'] = $this->payClient->infoAcct['cardLoginName'] ?? '';
            $order['card_number'] = $paymentBankAcct->bank_account_number;
            $order['pay_card_name'] = $this->payClient->bankAccountName;
            $order['pay_card_number'] = $this->payClient->bankAccountNumber;
            $order['amount'] = $this->payClient->depositMoney;
            $order['create_time'] = time();
            $order['comment'] = $this->payClient->remittanceInfo;

            $opts['http'] = [
                'timeout' => 30,
                'method'  => 'POST',
                'content' => http_build_query($order)
            ];
            $context = stream_context_create($opts);
            $result = file_get_contents($this->payClient->infoAcct['API_URL'],false,$context);
            $result = json_decode($result,true);
            if(!$result['success']){
                Log::info('deposit money failed,bill_no: ' . $this->payClient->billNo . ' reason: ' .
                    $result['message']);
                $deposit = DepositApply::where('bill_no',$this->payClient->billNo)->first();
                $deposit->update(['deposit_status' => 'fail','description' => '存款渠道维护中']);
                if($deposit->member_activity_id){
                    MemberActivity::where('member_activity_id',$deposit->member_activity_id)
                        ->update(['activity_status' => 'reject','description' => '存款失败']);
                }
                return ['status' => FAILED,'msg' => '网络错误，请选择其他方式存款'];
            }

            return [
                'status'  => SUCCESS,
                'content' => ['url' => 'https://manager.xb99.cc/backend/localPay?' . http_build_query($data)],
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

        return response()->json(['status' => SUCCESS,'msg' => '存款申请提交成功','url' => '/api_web/deposit']);
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
            'bank_account_id'       => $this->payClient->bankAccountId,
            'remittance_info'       => $this->payClient->remittanceInfo,
            'member_id'             => $this->payClient->memberId,
            'company_id'            => $this->payClient->companyId,
            'qr_type'               => $this->payClient->QRType,
            //转账汇款的汇款银行  为了和 bank_code 区分，用 transferBank 保存
            'bank_code'             => $this->payClient->bankCode,
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

    public function thirdCallBack(Request $request){
        $bill_no = $request['order_id'];
        if(empty($bill_no)){
            exit('参数错误！');
        }
        $pay_type = $request['direction'];
        if($pay_type == 'in'){
            $order = DepositApply::where('bill_no',$bill_no)->with('paymentAccount')->first();
            if(empty($order)){
                exit('参数错误！');
            }
            $infoAcct = json_decode($order->paymentAccount->info_acct,true);
            $this->checkSign($request,$infoAcct);
            $this->handelDepositThirdCallBack($request);
        }elseif($pay_type == 'out'){
            $order = DrawApply::where('bill_no',$bill_no)->with('paymentAccount')->first();
            $infoAcct = json_decode($order->paymentAccount->info_acct,true);
            $this->checkSign($request,$infoAcct);
            $this->handelDrawThirdCallBack($request);
        }else{
            exit('参数错误！');
        }
        //回应第三方
        echo 'true';
    }

}