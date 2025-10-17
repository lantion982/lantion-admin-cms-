<?php
//汇天付

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

class HTPay extends BasePay{
	public $flag;
	public $needActivity          = false;
	public $payment_platform_code = 'HTPay';

	public function __construct(PayClient $payClient,PayResult $payResult){
		parent::__construct($payClient,$payResult);
	}

	//支付入口
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

	//收款支付执行
	public function doPayIn(){
		$info_acct = $this->payClient->infoAcct;
		//Log::info('【汇天付】配置参数=》'.json_encode($info_acct));
		$apiKey      = $info_acct['API_KEY']??'';
		$P_CID       = $info_acct['CID']??'';
		$payUrl      = $info_acct['PayIn_URL'];
		$resultUrl   = CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code;
		$backUrl     = 'https://'.request()->headers->get('domain');
		$P_CardId    = '';
		$P_CardPass  = '';
		$amount      = $this->payClient->depositMoney;
		$order_id    = $this->payClient->billNo;
		$payInUrl    = '';
		$signKey     = '';
		$P_ChannelId = 1;                                //支付方式，默认在线支付
		$P_Description = '10001';                        //银行编码，默认工商银行
		if($apiKey == '' || $P_CID == ''){
			Log::error('【汇天付】商户号或密钥未配置！');
			return ['status' => FAILED,'msg' => '该支付通道相关参数不完整，请选择别的支付方式！'];
		}
		if($this->payClient->paymentMethodCode == 'OnLine'){
			$P_Description = $this->payClient->bankCode!=''?$this->payClient->bankCode:'10001';
			$P_ChannelId   = 1;
		}elseif($this->payClient->paymentMethodCode == 'AliPay'){
			$P_ChannelId = 2;
		}elseif($this->payClient->paymentMethodCode == 'QQPay'){
			$P_ChannelId = 89;
		}elseif($this->payClient->paymentMethodCode == 'WeiXin'){
			$P_ChannelId = 21;
		}elseif($this->payClient->paymentMethodCode == 'FastPay'){
			$P_ChannelId = 95;
		}elseif($this->payClient->paymentMethodCode == 'Counter'){
			$P_ChannelId = 95;
		}else{
			return ['status' => FAILED,'msg' => '不支持该支付方式！'];
		}

		$str = $P_CID.'|'.$order_id.'|'.$P_CardId.'|'.$P_CardPass.'|'.$amount.'|'.$P_ChannelId.'|'.$apiKey;
		$signKey   = strtolower(md5($str));                               //支付签名字符串
		$payInUrl  = $payUrl."?P_UserId=".$P_CID;                         //商户号
		$payInUrl .= "&P_OrderId=".$order_id;                             //商户订单号
		$payInUrl .= "&P_CardId=".$P_CardId;                              //充值卡卡号
		$payInUrl .= "&P_CardPass=".$P_CardPass;                          //充值卡密码
		$payInUrl .= "&P_FaceValue=".$amount;                             //金额
		$payInUrl .= "&P_ChannelId=".$P_ChannelId;                        //支付方式，
		$payInUrl .= "&P_Description=".$P_Description;                    //银行编码
		$payInUrl .= "&P_Price=".$amount;                                 //商品售价
		$payInUrl .= "&P_Quantity=1";                                     //商品数量
		$payInUrl .= "&P_PostKey=".$signKey;                              //支付签名字符串
		$payInUrl .= "&P_Result_URL=".$resultUrl;                         //支付后异步通知地址
		$payInUrl .= "&P_Notify_URL=".$backUrl;                           //支付后返回的商户显示页面
		$payInUrl .= "&P_Subject=Htpay01";
		Log::info('【汇天付】=>URL:'.$payInUrl);
		return ['status' => SUCCESS,'content' => ['url' => $payInUrl],'msg' => '获取url成功！'];
	}

	//第三方回调
	public function thirdCallBack(Request $request){
		//$results = $request->getContent();
		$results = $request->all();
		Log::info('【汇天付回调】Data：'.json_encode($results));
		//如果是出款回调
		/*$payType = $request['payType'];
		if($payType=='out'){
			$this->handelDrawThirdCallBack($request);
		}*/
		$bill_no = $results['P_OrderId'];
		if(empty($bill_no)){
			Log::info('【汇天付回调】参数错误=》订单号：P_OrderId 为空');
			exit('-err');
		}
		$order = DepositApply::where('bill_no',$bill_no)->with('paymentAccount')->first();
		if(empty($order)){
			Log::info('【汇天付回调】错误=》订单号未找到，$bill_no：'.$bill_no);
			exit('-err');
		}
		$info_acct = json_decode($order->paymentAccount->info_acct,true);
		$apiKey    = $info_acct['API_KEY']??'';
		$UserId    = $results['P_UserId'];
		$OrderId   = $results["P_OrderId"];
		$CardId    = $results["P_CardId"];
		$CardPass  = $results["P_CardPass"];
		$FaceValue = $results["P_FaceValue"];
		$ChannelId = $results["P_ChannelId"];
		$price     = $results["P_Price"];
		$ErrCode   = $results["P_ErrCode"];
		$PostKey   = $results["P_PostKey"];
		$payMoney  = $results["P_PayMoney"];
		$ErrMsg    = $results["P_ErrMsg"];
		$signStr   = $UserId."|".$OrderId."|".$CardId."|".$CardPass."|".$FaceValue."|".$ChannelId."|".$payMoney."|".$ErrCode."|".$apiKey;
		$signKey   = strtolower($signStr);
		if($signKey != $PostKey){
			Log::info('【汇天付回调】签名验证失败=》P_OrderId：'.$OrderId);
			echo "-签名验证失败";
		}
		if($ErrCode == "0"){                                              //支付成功
			if($payMoney>=$order->deposit_money){
				Log::info('【汇天付回调】成功=》加入队列执行上分，P_OrderId：'.$OrderId);
				$payResult = $this->payResult;
				$data = $request->all();
				$payResult->billNo = $order->bill_no;
				$payResult->depositMoney = $order->deposit_money;
				$payResult->paymentPlatformCode = $this->payment_platform_code;
				$payResult->paymentMethodCode = $order->payment_method_code;
				$payResult->dateTime = date('Y-m-d H:i:s');
				$payResult->allArgs = json_encode($data);
				$payResult->payStatus = 'success';
				app('BasePay')->handelThirdCallBack($payResult);
				echo "errCode=0";                                           //回应第三方
			}else{
				echo "-err";
				Log::info('【汇天付回调】失败=》P_OrderId'.$OrderId.'=》已付金额和订单金额不符！');
			}
		}else{
			Log::info('【汇天付回调】失败=》P_OrderId'.$OrderId.'=》'.$ErrMsg);
			echo "-err";                                               //支付失败
		}
	}

	//检查是否有关联的存款活动
	public function assembleClient(){
		parent::assembleClient();
		$this->payClient->paymentPlatformCode = $this->payment_platform_code;
		if(!empty($this->payClient->activityId)){
			$this->needActivity = true;
		}
	}

	//创建存款订单
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
			'deposit_fee'           => $this->getDepositFeeRatio($this->payClient),
			//手续费承担对象
			'deposit_fee_bear'      => Helper::getSetting('DEPOSIT_FEE_BEAR',$this->payClient->companyId)
		];

		$depositApply = DepositApply::create($data);
		return $depositApply;
	}

	//获取会员活动
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

	//出款支付执行
	public function payOut($drawApplyId,$paymentAccount){
		$drawApply = PayOrder::find($drawApplyId);
		if(!$drawApply){
			return ['status' => FAILED,'msg' => '取款订单未找到！'];
		}
		if($drawApply->draw_status != 'accept'){
			return ['status' => FAILED,'msg' => '该订单状态下不能进行出款！'];
		}
		if($drawApply->member_agent_type == Member::class){
			$user = Member::find($drawApply->member_agent_id);
		}else{
			$user = Agent::find($drawApply->member_agent_id);
		}

		$info_acct   = json_decode($paymentAccount->info_acct,true);
		$version     = 2;                                                   //接口版本号
		$batchType   = 2;                                                   //付款类型:2=付款到银行帐户
		$pay_apiUrl  = $info_acct['PayOut_URL'];                            //付款API
		$agent_id    = $info_acct['CID'];                                   //商户号
		$apiKey      = $info_acct['API_KEY'];                               //密钥
		$batch_no    = $drawApply->bill_no;                                 //商家订单ID
		$batch_amt   = $drawApply->draw_money-$drawApply->draw_fee;         //付款金额
		$batch_num   = 1;                                                   //付款总笔数
		$ext_param1  = 'HtpayOut';                                          //商户自定义原样返回字符串
		$signKey     = '';                                                  //签名字符串
		$notify_url  = CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code.'/?payType=out';             //支付后后台处理通知地址
		$detail_data = '';   //付款数据：流水号^银行编号^对公对私^收款人帐号^收款人姓名^付款金额^付款理由^省份^城市^支行
		$signStr  = '';
		$signStr .= 'agent_id='.$agent_id;
		$signStr .= '&batch_amt='.$batch_amt;
		$signStr .= '&batch_no='.$batch_no;
		$signStr .= '&batch_num='.$batch_num;

		$orderSn      = "KTPO".date('YmdHis'.rand(1001,9999),time());
		$bankAccount  = $drawApply->bankAccount;
		$cidyArr      = explode('-',$bankAccount->opening_address);

		$province     = $cidyArr[0]??'未知';
		$city         = $cidyArr[0]??'未知';
		$bankCode     = Helper::getPaymentPlatformBank($this->payment_platform_code,$bankAccount->bank_code);
		$detail_data  = $orderSn.'^'.$bankCode.'^0^'.$bankAccount->bank_account_number.'^';
		$detail_data .= $bankAccount->bank_account_name.'^'.$batch_amt.'^withdraw^'.$province.'^';
		$detail_data .= $city.'^'.$bankAccount->opening_bank;
		$payOut_url   = $signStr;
		log::info('$detail_data=>'.$detail_data);
		//$payOut_url  .= '&detail_data='.strtolower(urlencode(iconv("GBK","UTF-8",$detail_data)));
		$payOut_url  .= '&detail_data='.strtolower(urlencode($detail_data));
		$payOut_url  .= '&notify_url='.$notify_url;
		$payOut_url  .= '&ext_param1='.$ext_param1;
		$payOut_url  .= '&version='.$version;

		$signStr .= '&detail_data='.strtolower(urlencode($detail_data));
		$signStr .= '&ext_param1='.$ext_param1;
		$signStr .= '&key='.$apiKey;
		$signStr .= '&notify_url='.$notify_url;
		$signStr .= '&version='.$version;
		$signKey  = md5(strtolower($signStr));
		$payOut_url .= "&sign=".strtolower($signKey);
		$payOut_url  = $pay_apiUrl."?".$payOut_url;
		log::info('$payOut_url=>'.$payOut_url);
		$res    = $this->myXMLPost($payOut_url);
		//$res    = iconv('UTF8','GBK', $res);
		$result = $this->xmlToArray($res);
		if(empty($result)){
			return ['status' => FAILED,'content' => null,'msg' => '出款提交失败=》'.json_encode($res)];
		}
		if($result['ret_code']=='0000'){
			$drawApply->update([
				'rule_handel_time'      => Carbon::now(),
				'payment_account_id'    => $paymentAccount->payment_account_id,
				'payment_platform_code' => $this->payment_platform_code,
				'draw_status'           => 'audit'
			]);
			return ['status' => SUCCESS,'content' => null,'msg' => '出款申请已提交，正在处理汇款中...'];
		}else{
			$drawApply->update([
				'rule_handel_time'      => Carbon::now(),
				'payment_account_id'    => $paymentAccount->payment_account_id,
				'payment_platform_code' => $this->payment_platform_code
			]);
			Log::info('【汇天付】出款失败=》错误信息【' . $result['msg'] . '】 -- 订单号：' . $drawApply->bill_no);
			return ['status' => FAILED,'content' => null,'msg' => '出款失败，错误信息【' . $result['ret_msg'] . '】'];
		}
	}

	//出款第三方回调
	public function handelDrawThirdCallBack(Request $request){
		//暂未实现
		/*$drawApply = DrawApply::where(['bill_no' => $request['order_id']])->first();
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

		parent::handelThirdDrawCallBack($payResult);*/
	}

	//存款订单状态查询//未调用
	public function queryDeposit($depositApplyId,$paymentAccount){
		$order     = PayOrder::where('deposit_apply_id',$depositApplyId)->first();
		$info_acct = json_decode($paymentAccount->info_acct,true);
		$query_URL = $info_acct['PayInQuery_URL'];
		$apiKey    = $info_acct['API_KEY']??'';
		$P_CID     = $info_acct['CID']??'';
		$P_CardId  = '';
		$payment_method_code = $order->payment_method_code;
		if($payment_method_code == 'OnLine'){
			$P_ChannelId = 1;
		}elseif($payment_method_code == 'AliPay'){
			$P_ChannelId = 2;
		}elseif($payment_method_code == 'QQPay'){
			$P_ChannelId = 89;
		}elseif($payment_method_code == 'WeiXin'){
			$P_ChannelId = 21;
		}elseif($payment_method_code == 'FastPay'){
			$P_ChannelId = 32;
		}else{
			return ['status' => FAILED,'msg' => '不支持该支付方式！'];
		}
		$order_id  = $order->bill_no;
		$depMoney  = $order->deposit_money;
		$queryStr  = 'P_UserId='.$P_CID;
		$queryStr .= '&P_OrderId='.$order_id;
		$queryStr .= '&P_ChannelId='.$P_ChannelId;
		$queryStr .= '&P_CardId='.$P_CardId;
		$queryStr .= '&P_FaceValue='.$depMoney;
		$signStr   = $queryStr.'&P_PostKey='.$apiKey;
		$singKey   = strtolower(md5($signStr));
		$P_PostKey = iconv('UTF-8','gb2312//IGNORE',$singKey);
		$queryUrl  = $query_URL.'?'.$queryStr.'&P_PostKey='.$P_PostKey;
		$result    = $this->myPost($queryUrl);
		parse_str($result,$result_arr);
		$flag      = $result_arr['P_flag'];
		$status    = $result_arr['P_status'];

		if($flag == 0){
			return ['status' => FAILED,'content' => '','msg' => '订单处理中...'];
		}else{
			if($status == 0){
				//后期考虑加个签名验证
				return ['status' => SUCCESS,'content' => '','msg' => '订单支付成功！'];
			}
		}

		return ['status' => FAILED,'content' => '','msg' => '订单支付失败！'];
	}

	//出款订单状态查询，在后台取款单中手工调用
	public function queryWithdrawal($drawApplyId,$paymentAccount){
		$drawApply = PayOrder::where('deposit_apply_id',$drawApplyId)->first();
		if(empty($drawApply)){
			Log::info('【汇天付】取款单查询失败=》订单号：'.$drawApplyId.'=》不存在！');
			return ['status' => FAILED,'msg' => '订单不存在！'];
		}
		$info_acct = json_decode($paymentAccount->info_acct,true);
		$query_URL = $info_acct['PayOutQuery_URL'];
		$apiKey    = $info_acct['API_KEY']??'';
		$P_CID     = $info_acct['CID']??'';
		$signKey   = '';
		$version   = 2;
		$signStr   = '';

		$signStr = $signStr .'agent_id='.$P_CID;
		$signStr = $signStr .'&batch_no='.$drawApply->bill_no;
		$signStr = $signStr .'&key=' . $apiKey;
		$signStr = $signStr .'&version=2';
		$signKey = md5(strtolower($signStr));                               //验签字符串


		$url = $query_URL;
		$url = $url."?version=".$version;
		$url = $url."&agent_id=".$P_CID;
		$url = $url."&batch_no=".$drawApply->bill_no;
		$url = $url."&sign=".strtolower($signKey);
		$res = $this->mypost($url);
		$result = $this->xmlToArray($res);

		Log::info('【汇天付】取款单查询=》订单号：'.$drawApply->bill_no.'|结果：'.$res);
		Log::info('【汇天付】取款单查询=》订单号：'.$drawApply->bill_no.'|处理后结果：'.json_encode($result));
		if($result['ret_code']=='0000'){
			if($result['status']==1){
				$signBack = 'ret_code='.$result['ret_code'].'&ret_msg='.$result['ret_msg'];
				$signBack = $signBack.'&agent_id='.$result['agent_id'].'&hy_bill_no='.$result['hy_bill_no'];
				$signBack = $signBack.'&status='.$result['status'].'&batch_no='.$result['batch_no'];
				$signBack = $signBack.'&batch_amt='.$result['batch_amt'].'&batch_num='.$result['batch_num'];
				$signBack = $signBack.'&detail_data='.$result['detail_data'].'&&ext_param1='.$result['&ext_param1'];
				$signBack = $signBack.'&&key=='.$apiKey;
				$signKey  = md5(strtolower($signBack));
				Log::info('【汇天付】取款单查询=》signKey：'.$signKey.'，返回的sing：'.$result['sign']);
				if($signKey==$result['sign']&&$result['batch_no']==$drawApply->bill_no){
					return ['status' => SUCCESS,'content' => '','msg' => '订单已经出款成功！'];
				}else{
					Log::info('【汇天付】取款单查询=》订单号：'.$drawApply->bill_no.'，验签失败或单号不符');
					return ['status' => FAILED,'content' => '','msg' => '查询结果：验签失败或单号不符！'];
				}
			}elseif($result['status']==0){
				return ['status' => FAILED,'content' => '','msg' => '订单处理中...'];
			}else{
				return ['status' => FAILED,'content' => '','msg' => '订单无效！'];
			}
		}else{
			return ['status' => SUCCESS,'content' => '','msg' => '查询失败：'.$result['ret_msg']];
		}
	}

	//模拟POST请求
	public static function myPost($url){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		$output = curl_exec($ch);

		curl_close($ch);
		return $output;
	}

	//模拟POST请求
	public static function myXMLPost($url){
		$ch = curl_init();
		$header[] = "Content-type: text/xml";
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		$output = curl_exec($ch);

		curl_close($ch);
		return $output;
	}

	//XML TO Array
	public static function xmlToArray($xml){
		try{
			libxml_disable_entity_loader(true);                              //禁止引用外部xml实体
			$xmlstr = $xml;
			$tem    = simplexml_load_string($xmlstr);
			log::info('simplexml_load_string:');
			log::info($tem);
			$result = json_decode(json_encode($tem),true);
			return $result;
		}catch(\Exception $ex){
			Log::info('【汇天付】转帐数据失败：'.$ex->getMessage());
		}
		return '';
	}
}
