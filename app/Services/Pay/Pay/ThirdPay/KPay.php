<?php
//KPay

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

class KPay extends BasePay{
	public $flag;
	public $needActivity          = false;
	public $payment_platform_code = 'KPay';

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
		if($this->payClient->paymentMethodCode === 'OnLine'){
			return $this->_onLine();
		}elseif($this->payClient->paymentMethodCode === 'AliPay'){
			return $this->_alipay();
		}elseif($this->payClient->paymentMethodCode === 'WeiXin'){
			return $this->_weixin();
		}elseif($this->payClient->paymentMethodCode === 'FastPay'){
			return $this->_fastPay();
		}else{
			return ['status' => FAILED,'msg' => '不支持该支付方式！'];
		}
	}

	private function _sign($data,$md5key){
		ksort($data);
		return strtoupper(md5(urldecode(http_build_query($data).'&key='.$md5key)));
	}

	private function _alipay(){
		$info_acct = $this->payClient->infoAcct;
		$data = [
			'version'     => '2.0',
			'charset'     => 'UTF-8',
			'spid'        => $info_acct['spid'],
			'spbillno'    => $this->payClient->billNo,
			'tranAmt'     => (string)($this->payClient->depositMoney*100),
			'payType'     => $this->payClient->isMobile?'pay.alipay.wap':'pay.alipay.native',
			'backUrl'     => 'https://'.request()->headers->get('domain'),
			'notifyUrl'   => CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code,
			'productName' => '唐家的小面包',
		];
		$data['sign'] = $this->_sign($data,$info_acct['md5key']);
		$data['signType'] = 'MD5';
		$xmlData = xml_encode($data);
		\Log::info('K-Pay:'.$xmlData);
		if($this->payClient->isMobile){
			$url = $info_acct['gateway_url'].'/pay/wapPay';
			$requestData = [
				'req_data' => $xmlData
			];
			$encryptDataBefore = [
				'data'        => $requestData,
				'method'      => 'post',
				'gateway_url' => $url
			];
			$encryptData = encrypt($encryptDataBefore);
			$url = BACKEND_URL.'/callback/httpForm?encryptData='.$encryptData;
			return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
		}else{
			$url = $info_acct['gateway_url'].'/pay/nativePay';
			$ret = $this->_curlPost($url,$xmlData);
			\Log::info('K-Pay_return:'.$ret);
			$info = (array)simplexml_load_string($ret);
			if($info['retcode'] == '0'){
				$url = $info['codeUrl']??$info['codeImgUrl'];
				return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
			}else{
				return ['status' => FAILED,'msg' => $info['retmsg']];
			}
		}
	}

	private function _weixin(){
		$info_acct = $this->payClient->infoAcct;
		$data = [
			'version'     => '2.0',
			'charset'     => 'UTF-8',
			'spid'        => $info_acct['spid'],
			'spbillno'    => $this->payClient->billNo,
			'tranAmt'     => (string)($this->payClient->depositMoney*100),
			'payType'     => $this->payClient->isMobile?'pay.weixin.wap':'pay.weixin.native',
			'backUrl'     => 'http://'.request()->headers->get('domain'),
			'notifyUrl'   => CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code,
			'productName' => '唐家的小面包',
		];
		$data['sign'] = $this->_sign($data,$info_acct['md5key']);
		$data['signType'] = 'MD5';
		$xmlData = xml_encode($data);
		if($this->payClient->isMobile){
			$url = $info_acct['gateway_url'].'/pay/wapPay';
			$requestData = [
				'req_data' => $xmlData
			];
			$encryptDataBefore = [
				'data'        => $requestData,
				'method'      => 'post',
				'gateway_url' => $url
			];
			$encryptData = encrypt($encryptDataBefore);
			$url = BACKEND_URL.'/callback/httpForm?encryptData='.$encryptData;
			return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
		}else{
			$url = $info_acct['gateway_url'].'/pay/nativePay';
			$ret = $this->_curlPost($url,$xmlData);
			$info = (array)simplexml_load_string($ret);
			if($info['retcode'] == '0'){
				$url = $info['codeUrl']??$info['codeImgUrl'];
				return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
			}else{
				return ['status' => FAILED,'msg' => $info['retmsg']];
			}
		}
	}

	private function _fastPay(){
		$info_acct = $this->payClient->infoAcct;
		$data = [
			'version'     => '2.0',
			'charset'     => 'UTF-8',
			'spid'        => $info_acct['spid'],
			'spbillno'    => $this->payClient->billNo,
			'tranAmt'     => (string)($this->payClient->depositMoney*100),
			'backUrl'     => 'http://'.request()->headers->get('domain'),
			'notifyUrl'   => CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code,
			'productName' => '唐家的小面包',
		];
		$data['sign'] = $this->_sign($data,$info_acct['md5key']);
		$data['signType'] = 'MD5';
		$xmlData = xml_encode($data);

		$url = $info_acct['gateway_url'].'/pay/quickPay';
		$requestData = [
			'req_data' => $xmlData
		];
		$encryptDataBefore = [
			'data'        => $requestData,
			'method'      => 'post',
			'gateway_url' => $url
		];
		$encryptData = encrypt($encryptDataBefore);
		$url = BACKEND_URL.'/callback/httpForm?encryptData='.$encryptData;
		return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
	}

	private function _onLine(){
		$info_acct = $this->payClient->infoAcct;
		$data = [
			'version'     => '2.0',
			'charset'     => 'UTF-8',
			'spid'        => $info_acct['spid'],
			'spbillno'    => $this->payClient->billNo,
			'tranAmt'     => (string)($this->payClient->depositMoney*100),
			'cardType'    => 0,
			'bankCode'    => Helper::getPaymentPlatformBank($this->payment_platform_code,$this->payClient->bankCode),
			'backUrl'     => 'http://'.request()->headers->get('domain'),
			'notifyUrl'   => CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code,
			'productName' => '唐家的小面包',
		];
		$data['sign'] = $this->_sign($data,$info_acct['md5key']);
		$data['signType'] = 'MD5';
		$xmlData = xml_encode($data);

		$url = $info_acct['gateway_url'].'/pay/gatewayPay';
		$requestData = [
			'req_data' => $xmlData
		];
		$encryptDataBefore = [
			'data'        => $requestData,
			'method'      => 'post',
			'gateway_url' => $url
		];
		$encryptData = encrypt($encryptDataBefore);
		$url = BACKEND_URL.'/callback/httpForm?encryptData='.$encryptData;
		return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功'];
	}

	public function thirdCallBack(Request $request){
		$str = file_get_contents("php://input");
		Log::info('YouFu【data】：'.$str);
		$data = (array)simplexml_load_string($str);
		$bill_no = $data['spbillno'];
		if(empty($bill_no)){
			exit('参数错误！');
		}
		$order = DepositApply::where('bill_no',$bill_no)->with('paymentAccount')->lockForUpdate()->first();
		if(empty($order)){
			exit('参数错误！');
		}
		if($order->deposit_status === 'succeed'){
			exit('success');
		}
		$infoAcct = json_decode($order->paymentAccount->info_acct,true);
		$this->checkSign($data,$infoAcct);
		$this->handelDepositThirdCallBack($data);
		//回应第三方
		echo 'success';
	}

	public function handelDepositThirdCallBack($data){
		/* 验证单号和金额 */
		$depositApply = DepositApply::where(['bill_no' => $data['spbillno']])->first();
		if(!$depositApply){
			Log::info('充值订单：'.$data['spbillno'].'未找到');
			exit;
		}
		if($depositApply->deposit_money*100 != $data['payAmt']){
			Log::info('充值订单：'.$data['spbillno'].'金额不对');
			exit();
		}
		if($data['result'] != 'pay_success'){
			exit();
		}

		//调用父方法，插入 mongodb 数据库，根据查询结果，插入或更新 mysql 数据表，再考虑队列
		// 组合 payResult 对象
		$payResult = $this->payResult;

		/* 第三方回调，只给 billNoEncoded 和 金额 */
		$payResult->billNo = $data["spbillno"];

		//$payResult->remittanceInfo = '未来客户提供的可见的存单号';
		$payResult->depositMoney = $data["payAmt"]/100;
		$payResult->paymentPlatformCode = $this->payment_platform_code;
		$payResult->paymentMethodCode = $depositApply->payment_method_code;
		$payResult->dateTime = $depositApply->created_at;
		$payResult->allArgs = json_encode($data);
		$payResult->payStatus = 'success';

		parent::handelThirdCallBack($payResult);
	}

	public function handelDrawThirdCallBack(Request $request){
		//验证单号和金额
		$drawApply = PayOrder::where(['bill_no' => $request['order_id']])->first();
		if(!$drawApply){
			Log::info('出款订单：'.$request['order_id'].'未找到');
			exit;
		}
		if(($drawApply->draw_money-$drawApply->draw_fee)*100 != $request['amount']){
			Log::info('出款订单：'.$request['order_id'].'金额不对');
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
		$payResult->payStatus = $request['status'] == 'verified'?'success':'failed';

		parent::handelThirdDrawCallBack($payResult);
	}

	public function assembleClient(){
		parent::assembleClient();
		$this->payClient->paymentPlatformCode = $this->payment_platform_code;
		//从前台获取的附言和选择的活动
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
			/* 转账汇款的汇款银行  为了和 bank_code 区分，用 transferBank 保存 */
			'bank_code'             => $this->payClient->bankCode,
			'opening_bank'          => $this->payClient->openingBank,
			'payment_account_id'    => $this->payClient->paymentAccountId,
			'deposit_time'          => $this->payClient->depositTime,
			'payment_method_code'   => $this->payClient->paymentMethodCode,
			'payment_platform_code' => $this->payClient->paymentPlatformCode,
			'member_activity_id'    => $this->payClient->memberActivityId,
			'room_code'             => $this->payClient->roomCode,
			//支付方式手续费
			'deposit_fee'           => $this->getDepositFeeRatio($this->payClient),
			//手续费承担对象
			'deposit_fee_bear'      => Helper::getSetting('DEPOSIT_FEE_BEAR',$this->payClient->companyId)
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
		/* 调用方法组合 payClient ，该方法先调用父类的方法，再根据自己情况，调用子类方法 */
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

		/* 订单号检查暂时可不写，因为订单号采用 UUID 方式生成，同时生成 快捷码供用户查询 */
		$bankAccount = $drawApply->bankAccount;
		$data['cid'] = $info_acct['CID'];
		$data['uid'] = $user->login_name;
		$data['time'] = time();
		$data['amount'] = $drawApply->draw_money-$drawApply->draw_fee;
		$data['order_id'] = $drawApply->bill_no;
		$data['to_bank_flag'] = $bankAccount->bank_code;
		$data['to_cardnumber'] = $bankAccount->bank_account_number;
		$data['to_username'] = $bankAccount->bank_account_name;
		$data['out_system'] = $info_acct['out_system'];    /* 指定出款系统: remit=银行卡转账，3rdpay=第三方代付如果没有传入此参数，默认是 银行卡转账 */

		$data = [
			'version'     => '2.0',
			'charset'     => 'UTF-8',
			'spid'        => $info_acct['spid'],
			'spbillno'    => $drawApply->bill_no,
			'tranAmt'     => (string)(($drawApply->draw_money-$drawApply->draw_fee)*100),
			'backUrl'     => 'http://'.request()->headers->get('domain'),
			'notifyUrl'   => CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code,
			'productName' => '唐家的小面包',
		];

		$sign    = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
		$res     = $this->post($info_acct['PAYOUT_API_URL'],json_encode($data),$sign);
		$adminId = Auth::guard('admin')->user()->admin_id??null;
		$result  = json_decode($res,true);
		if(!$result['success']){
			$drawApply->update([
				'rule_handel_time'      => Carbon::now(),
				'payment_account_id'    => $paymentAccount->payment_account_id,
				'payment_platform_code' => $this->payment_platform_code
			]);
			Log::info('汇款失败，错误信息【'.$result['msg'].'】 -- 订单号：'.$drawApply->bill_no);
			return ['status' => FAILED,'content' => null,'msg' => '汇款失败，错误信息【'.$result['msg'].'】'];
		}else{
			$drawApply->update([
				'rule_handel_time'      => Carbon::now(),
				'payment_account_id'    => $paymentAccount->payment_account_id,
				'payment_platform_code' => $this->payment_platform_code,
				'draw_status' => 'audit',
				'admin_id'    => $adminId,
			]);
			return ['status' => SUCCESS,'content' => null,'msg' => '汇款进行中'];
		}
	}

	/* 查询订单状态 */
	public function queryWithdrawal($drawApplyId,$paymentAccount){
		$info_acct = json_decode($paymentAccount->info_acct,true);
		$drawApply = PayOrder::find($drawApplyId);
		$data['cid'] = $info_acct['CID'];
		$data['order_id'] = $drawApply->bill_no;
		$data['time'] = time();
		$sign = base64_encode(hash_hmac('sha1',json_encode($data),$info_acct['AIP_KEY'],true));
		$res = $this->post('https://www.dsdfpay.com/dsdf/api/query_withdraw',json_encode($data),$sign);
		Log::info('取款单：'.$drawApply->bill_no.'--'.$res);
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

	public function checkSign($data,$infoAcct){
		$sign = $data['sign'];
		unset($data['sign'],$data['signType']);
		$data = array_filter($data,function($v){
			if($v === '' || $v === 'null'){
				return false;
			}else{
				return true;
			}
		});
		ksort($data);

		$sign2 = strtoupper(md5(urldecode(http_build_query($data).'&key='.$infoAcct['md5key'])));
		if($sign !== $sign2){
			exit('Signature error!');
		}
	}

	public function _curlPost($url,$xmlData){
		$header[] = "Content-type:text/xml";
		$header[] = "charset:utf-8";
		$header[] = "User-Agent:Mozilla/5.0 (Windows NT 6.2; Win64; x64) 		AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.87 Safari/537.36";
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xmlData);
		$response = curl_exec($ch);
		return $response;
	}
}
