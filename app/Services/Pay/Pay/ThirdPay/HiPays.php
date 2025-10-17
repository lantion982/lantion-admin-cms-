<?php
//HiPays

namespace App\Services\Pay\Pay\ThirdPay;

use App\Models\DepositApply;
use App\Models\PaymentAccount;
use App\Services\Pay\Pay\BasePay;
use App\Services\Pay\Pay\PayClient;
use App\Services\Pay\Pay\PayResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HiPays extends BasePay{
    public $flag;
    public $needActivity          = false;
    public $payment_platform_code = 'HiPays';

    public function __construct(PayClient $payClient,PayResult $payResult){
        parent::__construct($payClient,$payResult);
    }

    public function Pay(Request $request,$paymentAccount){

    }

    public function doPayIn(){

    }

    public function thirdCallBack(Request $request){

        $data = $request->input();

        $ReturnArray = array(
            "memberid" => $data["member"],
            "orderid"  => $data["order"],
            "amount"   => $data["amount"],
            "datetime" => $data["dealdate"],
            "recode"   => $data["recode"],
            "extra"    => $data["extra"]
        );

        $depositApply   = DepositApply::where(['bill_no' => $data["order"]])->first();
        $paymentAccount = PaymentAccount::find($depositApply->payment_account_id);

        $Md5key = $paymentAccount->third_public_key;

        ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach($ReturnArray as $key => $val){
            $md5str = $md5str . $key . "=>" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        if($sign == $data["sign"]){
            if($data["recode"] == "00"){
                $str = "交易成功！订单号：" . $data["order"];
                Log::info('&&&&& ' . $str);
                echo 'ok';
            }
        }else{
            exit('Signature error');
        }

        $payResult = new payResult();

        $payResult->billNo = $data["order"];

        $payResult->depositMoney = $data["amount"];
        $payResult->paymentPlatformCode = 'HiPays';
        $payResult->dateTime = Carbon::now();
        $payResult->allArgs = $data;

        parent::handelThirdCallBack($payResult);
    }

    public function assembleClient(Request $request){
        parent::assembleClient($request);
        $this->payClient->paymentPlatformCode = $this->payment_platform_code;
        $this->payClient->bankCode = $request['bank_code'];
        $this->payClient->remittanceInfo = $request['remittance_info'];
        if($request->get('activity_id') == ''){
            clock('没有选择活动！' . $request['activity_id']);
        }else{
            clock('会员有选择活动' . $request['activity_id']);
            $this->payClient->activityId = $request['activity_id'];
            $this->payClient->roomCode = $request['room_code'];
            $this->needActivity = true;
        }
    }

    public function genDepositApply(){

    }

    public function genMemberActivity(){

    }

    public function PayOut(Request $request,$paymentAccount,$bankAccount){
        $this->payClient->paymentAccountId = $paymentAccount->payment_account_id;
        $this->payClient->thirdPublicKey = $paymentAccount->third_public_key;
        $this->payClient->accountNumber = $paymentAccount->account_number;
        $this->payClient->returnUrl = $paymentAccount->return_url;
        $this->checkBillNo();

        return $this->doPayOut();
    }

    public function doPayOut(){
        $_site = "https://api.hipays.cc/";
        $paytype = $_POST['paytype'];
        $member = "100000003";
        $order = date("YmdHis") . rand(100000,999999);
        $amount = $_POST['amount'];
        $dealdate = date("Y-m-d H:i:s");
        $notify = $_site . "demo/server.php";
        $callback = $_site . "demo/page.php";
        $md5key = "Qf3skdQeECWrDo3xh2A3gu8rC3guF5";
        $apiurl = $_site . "v1.0/Withdrawals.do";
        $acct_name = $_POST['acct_name'];
        $bankno = $_POST['bankno'];
        $banktype = $_POST['banktype'];
        $phone = $_POST['phone'];
        $bank_name = $_POST['bank_name'];
        $settle_no = $_POST['settle_no'];
        $product = $_POST['product'];
        $extra = "extra_param_test";

        $pay_array = array(
            "member"   => $member,
            "order"    => $order,
            "amount"   => $amount,
            "dealdate" => $dealdate,
            "notify"   => $notify,
            "callback" => $callback,
            "extra"    => $extra,
        );

        ksort($pay_array);
        $para_str = "";
        foreach($pay_array as $key => $val){
            $para_str = $para_str . $key . "=" . $val . "&";
        }
        $signstr = strtoupper(md5($para_str . "key=" . $md5key));

        $pay_array["md5sign"] = $signstr;
        $pay_array["type"] = $paytype;
        $pay_array["product"] = $product;
        $pay_array["extra"] = $extra;
        $pay_array["acct_name"] = $acct_name;
        $pay_array["bankno"] = $bankno;
        $pay_array["banktype"] = $banktype;
        $pay_array["phone"] = $phone;
        $pay_array["bank_name"] = $bank_name;
        $pay_array["settle_no"] = $settle_no;
    }
}