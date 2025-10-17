<?php
//CCEX

namespace App\Services\Pay\Pay\ThirdPay;

use App\Models\Agent;
use App\Models\DepositApply;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\MemberActivity;
use App\Models\PaymentAccount;
use App\Models\PaymentThirdAcctTmp;
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
use Auth;

class CCEX extends BasePay{
	public $flag;
	public $needActivity          = false;
	public $payment_platform_code = 'CCEX';
	public $detect = null;

	public function __construct(PayClient $payClient,PayResult $payResult){
		parent::__construct($payClient,$payResult);
		$this->detect = new Mobile_Detect();
	}

	//支付入口
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

	//支付-存入
	public function doPayIn(){
		$this->flag  = ' 987';
		$info_acct   = $this->payClient->infoAcct;
		$merchant_id = $info_acct['merchant_id'];
		$token_id    = $info_acct['token_id_in'];
		$order_money = number_format($this->payClient->depositMoney,2,'.','');
		$order_no    = $this->payClient->billNo;
		$notify_url  = CALLBACK_URL.'/callback/thirdCallBack/'.$this->payment_platform_code;
		$md5key      = $info_acct['md5key']??'';

		$preAction = false;
		//如果是银行存款，要先提交存款账号信息
		if($this->payClient->paymentMethodCode == 'NCManual'){
			$data['merchant_id'] = $merchant_id;
			$data['muid'] = $this->payClient->login_name;
			$data['paytype_id'] = $this->_getPayType();
			$data['payment_info'] = config('enums.bank_code')[$this->payClient->bankCode];
			$data['payment_account'] = $this->payClient->bankAccountNumber;
			$data['account_name'] = $this->payClient->bankAccountName;
			$data['nonstr'] = substr(Uuid::uuid4()->getHex(),0,16);

			$data2 = array_filter($data,function($v){
				if($v !== '' && $v !== null){
					return true;
				}
			});
			ksort($data2);
			$sign2 = strtoupper(md5(urldecode(http_build_query($data2).'&key='.$md5key)));
			$data['sign'] = $sign2;

			//Log::info('CCEX 银行存款提交存款银行帐号信息=》参数：'.json_encode($data));

			$myCurl = new MyCurl();
			$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/merchant/user/addpaymentinfo',$data);

			$json = json_decode($result,true);
			$code = $json['code'];
			if(trim($code) == '0'){
				$preAction = true;
			}else{
				Log::error('【CCEX】提交存款银行帐号信息失败=》返回：'.$result);
				return ['status' => SUCCESS,'content' => null,'msg' => '请检查存款账号信息！'];
			}
		}else{
			$preAction = true;
		}

		//调用支付提单接口，【最近提交的是唯一有效的】
		if($preAction == true){
			$data3['out_trade_no'] = $order_no;                                          //订单号
			$data3['merchant_id']  = $merchant_id;                                       //商户号
			$data3['token_id']   = $token_id;
			$data3['muid']       = $this->payClient->login_name;
			$data3['nonstr']     = substr(Uuid::uuid4()->getHex(),0,16);
			$data3['body']       = '娱乐';
			$data3['detail']     = '娱乐';
			$data3['amount']     = $order_money;
			$data3['notify_url'] = $notify_url;
			$data3['valid_time'] = 900;
			$data3['ccp']        = 1;                                                    //跳转支付，返回code为0

			//支付成功跳转地址
			if($this->detect->isiOS() || $this->detect->isAndroidOS()){
				//Log::info('CCEX【drvice】：android or iOS'.$this->payClient->domain.'/#/user');
				$data3['redirect_url'] = 'https://'.$this->payClient->domain.'/#/user';
			}else{
				//Log::info('CCEX【drvice】：PC'.$this->payClient->domain.'/#/user/info');
				$data3['redirect_url'] = 'https://'.$this->payClient->domain.'/#/user/info';
			}
			
			$data4 = array_filter($data3,function($v){
				if($v !== '' && $v !== null){
					return true;
				}
			});
			ksort($data4);
			$sign = strtoupper(md5(urldecode(http_build_query($data4).'&key='.$md5key)));
			$data3['sign'] = $sign;

			$myCurl = new MyCurl();
			$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/unifiedorder',$data3);
			
			$json = json_decode($result,true);
			$code = $json['code'];

			if(trim($code) == '0'){
				$third_order_no = $json['desc']['order_no'];
				//Log::info('CCEX【提交订单成功，order_no】：' . $third_order_no);
				$this->updateDepositBillAtOnce($third_order_no);

				//准备 muid 和 sign 签名
				$data5['merchant_id'] = $merchant_id;
				$data5['muid'] = $this->payClient->login_name;
				$data5['nonstr'] = substr(Uuid::uuid4()->getHex(),0,16);
				$data5['ts']         = time();
				//$data5['paytype_id'] = $this->_getPayType();

				$data6 = array_filter($data5,function($v){
					if($v !== '' && $v !== null){
						return true;
					}
				});
				ksort($data6);
				$h5Sign = strtoupper(md5(urldecode(http_build_query($data6).'&key='.$md5key)));

				if($this->detect->isiOS() || $this->detect->isAndroidOS()){
					$url = $info_acct['gateway_url'].'/#/?ccp='.$third_order_no.'&muid='.$this->payClient->login_name.
						'&sign='.$h5Sign.'&nonstr='.$data5['nonstr'].'&ts='.$data5['ts'].'&paytype_id='.$this->_getPayType();
				}else{
					$url = $info_acct['gateway_url'].'/#/?ccp='.$third_order_no.'&muid='.$this->payClient->login_name.
						'&sign='.$h5Sign.'&nonstr='.$data5['nonstr'].'&ts='.$data5['ts'].'&paytype_id='.$this->_getPayType();
				}

				//Log::info('CCEX【支付获取地址成功】：'.$url);
				return ['status' => SUCCESS,'content' => ['url' => $url],'msg' => '获取url成功！'];
			}else{
				Log::error('【CCEX】提交订单失败=》返回：'.$result);
				Log::info('CCEX【url】：' . $info_acct['api_url'].'/api/unifiedorder');
				Log::info('CCEX【提单参数】：' . json_encode($data3));
				return ['status' => FAILED,'content' => ['url' => ''],'msg' => '获取url失败！'];
			}
		}else{
			Log::info('CCEX 提交失败，存款银行帐号信息绑定失败！');
			return ['status' => SUCCESS,'content' => null,'msg' => '请检查存款账号！'];
		}
	}

	//获取存款类型
	private function _getPayType(){
		$paytype_id = '0';
		$info_acct  = $this->payClient->infoAcct;
		$paytype_id = $info_acct['paytype_id']??'0';
		if($paytype_id!='0'){
			return $paytype_id;
		}
		switch($this->payClient->paymentMethodCode){
			case 'NCManual':
				$paytype_id = '3';
				break;
			case 'WeiXin':
				$paytype_id = '1';
				break;
			case 'AliPay':
				$paytype_id = '2';
				break;
			case 'other':
				$paytype_id = '11';
				break;
		}
		return $paytype_id;
	}

	//更新存款订单third_no
	public function updateDepositBillAtOnce($third_order_no){
		$order = DepositApply::query()->where('bill_no',$this->payClient->billNo)->first();
		if(empty($order)){
			Log::info('CCEX【存款订单'.$this->payClient->billNo.'，更新third_no失败】：未找到存款订单！');
			exit('未找到存款订单！');
		}else{
			$order->update(['third_no' => $third_order_no]);
		}
	}

	//插入第三方AcctTmp
	public function insertThirdAcctTmpOnce($third_order_no,$otc_no,$payment_info,$payment_account,$payment_account_name){
		$paymentThirdAcctTmp = PaymentThirdAcctTmp::create([
			'login_name'           => $this->payClient->login_name,
			'company_id'           => $this->payClient->companyId,
			'order_no'             => $this->payClient->billNo,
			'payment_method_code'  => $this->payClient->paymentMethodCode,
			'third_order_no'       => $third_order_no,
			'otc_no'               => $otc_no,
			'payment_info'         => $payment_info,
			'payment_account'      => $payment_account,
			'payment_account_name' => $payment_account_name,
			'add_time'             => $this->payClient->depositTime,
		]);
		return $paymentThirdAcctTmp;
	}

	//更新出款订单third_no
	public function updateDrawBillAtOnce($bill_no,$third_order_no){
		$order = PayOrder::where('bill_no',$bill_no)->first();
		if(empty($order)){
			Log::info('CCEX【出款订单'.$bill_no.'更新third_no失败】：未找到出款订单！');
			exit('未找到出款订单！');
		}else{
			$order->update(['third_no' => $third_order_no]);
		}
	}

	//第三方回调
	public function thirdCallBack(Request $request){
		$res  = $request->getContent();
		$data = json_decode($res,true);
		//Log::info('【CCEX】第三方回调返回数据：'.$res);
		//Log::info($data);
		if(empty($data['order_no'])){
			//Log::info('CCEX【第三方回调失败】setp2：回调数据单号为空！');
			exit('回调数据单号为空！');
		}
		$order_no = $data['order_no'];
		$deposit  = DepositApply::with('memberActivity')->whereIn('deposit_status',['applied','expired']) //处理申请中及过期取消的订单
		//$deposit  = DepositApply::with('memberActivity')->where('deposit_status','applied')                            //仅处理申请中的订单
			->where('payment_platform_code','CCEX')->where('third_no',$order_no)->first();
		if(!$deposit){
			//Log::info('【CCEX】第三方回调失败：未找到对应的订单或订单状态已改变，order_no:'.$order_no);
			$ret['code'] = 0;
			$ret['desc'] = 'ok';
			//$ret ="{'code':0,'desc':'ok'}";
			//Log::Info(json_encode($ret));
			exit(json_encode($ret));
		}

		$member     = Member::where('member_id',$deposit->member_id)->first();
		$payAccount = PaymentAccount::where('payment_account_id',$deposit->payment_account_id)->first();
		$info_acct  = json_decode($payAccount->info_acct,true);
		$md5key     = $info_acct['md5key'] ?? '';
		$data       = array(
			'order_no'    => $deposit->third_no,
			'merchant_id' => $info_acct['merchant_id'],
			'nonstr'      => substr(Uuid::uuid4()->getHex() , 0 , 16),
			'muid'        => $member->login_name,
		);
		$data2 = array_filter($data,function($v){
			if($v !== '' && $v !== null){
				return true;
			}
		});
		ksort($data2);
		$sign   = strtoupper(md5(urldecode(http_build_query($data2) . '&key=' . $md5key)));
		$data['sign'] = $sign;
		$myCurl = new MyCurl();
		$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/queryorder',$data);
		Log::info('【CCEX】回调查询结果：'.$result);
		$json = json_decode($result, true);
		$code = $json['code'];
		if(trim($code) == '0'){
			//Log::info('CCEX 查询【code=》0 desc】setp3：' . json_encode($json['desc']));
			$state = $json['desc']['state'];
			//如果返加CCEX订态状为已支付，更新本站存款订单为:success
			if(strtoupper(trim($state))=='PAID'){
				$deposit = DepositApply::where('deposit_status','applied')->where('third_no',$deposit->third_no)->first();
				if(!$deposit){
					Log::info('【CCEX】【订单'.$order_no.'】回调失败：订单号不存在，或订单状态已经改变！');
					$ret['code'] = 0;
					$ret['desc'] = 'ok';
					//$ret ="{'code':0,'desc':'ok'}";
					Log::Info(json_encode($ret));
					//$ret ='{"code":0,"desc":"ok"}';
					exit(json_encode($ret));
				}
				if(number_format($deposit->deposit_money,2,'.', '') ==  number_format($json['desc']['amount'],2,'.', '') && $deposit->bill_no ==  $json['desc']['out_trade_no']){
					$payResult = new PayResult();
					$payResult->billNo       = $deposit->bill_no;
					$payResult->depositMoney = $deposit->deposit_money;
					$payResult->paymentPlatformCode = $deposit->payment_platform_code;
					$payResult->paymentMethodCode   = $deposit->payment_method_code;
					$payResult->dateTime  = date('Y-m-d H:i:s');
					$payResult->allArgs   = json_encode($json);
					$payResult->payStatus = 'success';
					Log::info('CCEX 更新【订单'.$deposit->bill_no.'】执行handelThirdCallBack');
					//Log::info(json_encode($payResult));
					app('BasePay')->handelThirdCallBack($payResult);
					$ret['code'] = 0;
					$ret['desc'] = 'ok';
					exit(json_encode($ret));
				}else{
					Log::info('CCEX回调查询失败【错误的订单号或金额错误】：'.'bill_no'. $deposit->bill_no .'  third_no:'.$deposit->third_no);
				}
			}else{
				Log::info('CCEX回调查询失败【存款订单未支付】：'.'bill_no'. $deposit->bill_no .'  third_no:'.$deposit->third_no);
			}
		}else{
			Log::info('CCEX回调查询失败【code!=0】：'.'bill_no'. $deposit->bill_no .'  third_no:'.$deposit->third_no);
		}
	}
	
	//第三方回调
	public function PayOutCallBack(Request $request){
		$res  = $request->getContent();
		$data = json_decode($res,true);
		Log::info('【CCEX】下发回调返回数据：'.$res);
		Log::info($data);
		if(empty($data['out_trade_no'])){
			//Log::info('CCEX【第三方回调失败】setp2：回调数据单号为空！');
			exit('回调数据单号为空！');
		}
		$order_no = $data['out_trade_no'];
		$drawapply  = PayOrder::whereIn('draw_status',['accept','audit'])->where('bill_no',$order_no)->first();
		if(!$drawapply){
			Log::info('【CCEX】下发回调失败：未找到对应的订单或订单状态已改变，order_no:'.$order_no);
			$ret['code'] = 0;
			//$ret['desc'] = 'ok';
			//$ret ="{'code':0,'desc':'ok'}";
			//Log::Info(json_encode($ret));
			exit(json_encode($ret));
		}
		if($data['finish_date']!='0'){
			if(number_format($data['trans_fer_amount'],2,'.','')==($drawapply->draw_money-$drawapply->draw_fee)){
				$payResult = new PayResult();
				$payResult->memberId  = $drawapply->member_agent_id;
				$payResult->billNo    = $drawapply->bill_no;
				$payResult->drawMoney = $drawapply->draw_money;
				$payResult->paymentPlatformCode = $drawapply->payment_platform_code;
				$payResult->dateTime  = date('Y-m-d H:i:s');
				$payResult->allArgs   = json_encode($data);
				$payResult->payStatus = 'success';
				Log::info('【CCEX】 更新【出款订单'.$drawapply->bill_no.'】执行handelThirdDrawCallBack，将执行队列');
				app('BasePay')->handelThirdDrawCallBack($payResult);
				$ret['code'] = 0;
				exit(json_encode($ret));
			}
			Log::info('【CCEX】下发回调失败：订单金额不一致，order_no:'.$order_no);
		}else{
			Log::info('【CCEX】下发回调失败：未完成下发，order_no:'.$order_no);
		}
	}
	
	//充值第三方回调执行，未调用，直接在thirdCallBack 执行了。
	public function handelDepositThirdCallBack(Request $request,$depositApply){
		$str = "CCEX三方回调执行，订单号：".$request["order_no"];
		Log::info('&&&&& '.$str);
		// 组合 payResult 对象
		$payResult = $this->payResult;
		$data = $request->all();
		$payResult->billNo = $data["order_no"];
		$payResult->depositMoney = $data["order_money"];
		$payResult->paymentPlatformCode = $this->payment_platform_code;
		$payResult->paymentMethodCode = $depositApply->payment_method_code;
		$payResult->dateTime = date('Y-m-d H:i:s');
		$payResult->allArgs = json_encode($data);
		$payResult->payStatus = $request['order_status'] == 'success'?'success':'failed';
		parent::handelThirdCallBack($payResult);
	}

	//指定支付渠道码，活动检查
	public function assembleClient(){
		parent::assembleClient();
		$this->payClient->paymentPlatformCode = $this->payment_platform_code;
		if(!empty($this->payClient->activityId)){
			$this->needActivity = true;
		}
	}

	//生成订单
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
		];

		$depositApply = DepositApply::create($data);
		return $depositApply;
	}

	//会员参与存款活动--生成活动记录
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

	//出款方式，走一步到底的出款
	public function payOut($drawApplyId,$paymentAccount){

		//出款前，要先绑定收款账号信息
		$info_acct   = json_decode($paymentAccount->info_acct,true);
		$merchant_id = $info_acct['merchant_id'];
		$token_id    = $info_acct['token_id_out'];
		$md5key      = $info_acct['md5key']??'';
		$notify_url  = CALLBACK_URL.'/callback/PayOutCallBack/'.$this->payment_platform_code;
		$adminId     = Auth::guard('admin')->user()->admin_id??null;
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

		$data['merchant_id'] = $merchant_id;
		$data['muid'] = $user->login_name;
		$data['paytype_id'] = '3';
		$data['payment_info'] = config('enums.bank_code')[$bankAccount->bank_code];
		$data['payment_account'] = $bankAccount->bank_account_number;
		$data['account_name'] = $bankAccount->bank_account_name;
		$data['nonstr'] = substr(Uuid::uuid4()->getHex(),0,16);

		$data2 = array_filter($data,function($v){
			if($v !== '' && $v !== null){
				return true;
			}
		});
		ksort($data2);
		$sign2 = strtoupper(md5(urldecode(http_build_query($data2).'&key='.$md5key)));
		$data['sign'] = $sign2;

		Log::info('【CCEX】出款订单参数：'.json_encode($data));

		$myCurl = new MyCurl();
		$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/merchant/user/addpaymentinfo',$data);
		Log::info('【CCEX】出款订单返回结果:'.$result);

		$json = json_decode($result,true);
		$code = $json['code'];
		if(trim($code) == '0'){
			//然后调用出款接口，【最近提交的会是唯一有效的】
			Log::info('【CCEX】 出款订单返回：success');
			$data3['merchant_id'] = $merchant_id;
			$data3['out_trade_no'] = $order_no;
			$data3['muid'] = $user->login_name;
			$data3['token_id'] = $token_id;
			$data3['amount'] = number_format($drawApply->draw_money-$drawApply->draw_fee,2,'.','');
			$data3['nonstr'] = substr(Uuid::uuid4()->getHex(),0,16);
			$data3['notify_url'] = $notify_url;
			$data3['body']   = '娱乐';
			$data3['detail'] = '娱乐';

			$data4 = array_filter($data3,function($v){
				if($v !== '' && $v !== null){
					return true;
				}
			});
			ksort($data4);
			$sign4 = strtoupper(md5(urldecode(http_build_query($data4).'&key='.$md5key)));
			$data3['sign'] = $sign4;
			//$data3['ccs'] = '1';
			Log::info('【CCEX】出款提交参数:'.json_encode($data3));
			$myCurl = new MyCurl();
			$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/refund',$data3);
			Log::info('【CCEX】出款结果:'.$result);

			$json = json_decode($result,true);
			$code = $json['code'];
			if(trim($code) == '0'){
				Log::info('【CCEX】出款订单：'.$order_no.' 开始出款...');
				$out_trade_no = $json['desc']['out_trade_no'];
				if($out_trade_no == $order_no){
					// 直接更新
					$drawApply = PayOrder::where(['bill_no' => $order_no])->first();
					if(!$drawApply){
						Log::info('【CCEX】出款订单：'.$order_no.'未找到');
						exit;
					}
					$transfer_amount = $json['desc']['transfer_amount'];
					if(($drawApply->draw_money-$drawApply->draw_fee) != number_format($transfer_amount,2,'.','')){
						Log::info('【CCEX】出款订单：'.$order_no.'金额不对');
						exit();
					}
					// 先把订单改成 audit 状态
					$drawApply->update([
						'rule_handel_time'      => Carbon::now(),
						'payment_account_id'    => $paymentAccount->payment_account_id,
						'payment_platform_code' => $this->payment_platform_code,
						'draw_status'           => 'audit',
						'admin_id'              => $adminId,
					]);
					// 剩下的是主动查询，或者后台定时查询
					return ['status' => SUCCESS,'content' => null,'msg' => '提交成功'];
				}else{
					Log::info('【CCEX】出款订单：'.$order_no.'提交有返回，但是没有订单信息');
					return ['status' => SUCCESS,'content' => null,'msg' => '提交失败'];
				}
			}else{
				if($code == '-14'){
					Log::info('【CCEX】商户余额不足：'.$order_no.'not enough money');
					return ['status' => SUCCESS,'content' => null,'msg' => '商户余额不足'];
				}else{
					if($code == '-11'){
						Log::info('【CCEX】重复提交：'.$order_no.'duplicated order');
						return ['status' => SUCCESS,'content' => null,'msg' => '请勿重复提交'];
					}else{
						Log::info('【CCEX】出款订单：'.$order_no.'提交失败');
						return ['status' => SUCCESS,'content' => null,'msg' => '提交失败'];
					}
				}
			}
		}else{
			Log::info('【CCEX】payout addpaymentinfo result：failed');
			return ['status' => SUCCESS,'content' => null,'msg' => '请检查收款账号'];
		}
	}

	//更新会员出款银行卡信息
	public function updateDrawBankInfo($drawApplyId,$paymentAccount){
		$info_acct = json_decode($paymentAccount->info_acct,true);
		$merchant_id = $info_acct['merchant_id'];
		$token_id = $info_acct['token_id'];
		$md5key = $info_acct['md5key']??'';

		$drawApply = PayOrder::find($drawApplyId);
		if(!$drawApply){
			return ['status' => FAILED,'msg' => '未找到该订单！'];
		}
		if($drawApply->draw_status == 'success'){
			return ['status' => FAILED,'msg' => '当前订单状态不允许更新会员银行信息！'];
		}
		if($drawApply->member_agent_type == Member::class){
			$user = Member::find($drawApply->member_agent_id);
		}else{
			$user = Agent::find($drawApply->member_agent_id);
		}

		$order_no = $drawApply->bill_no;
		$bankAccount = $drawApply->bankAccount;

		$data['merchant_id'] = $merchant_id;
		$data['muid'] = $user->login_name;
		$data['paytype_id'] = '3';
		$data['payment_info'] = config('enums.bank_code')[$bankAccount->bank_code];
		$data['payment_account'] = $bankAccount->bank_account_number;
		$data['account_name'] = $bankAccount->bank_account_name;
		$data['nonstr'] = substr(Uuid::uuid4()->getHex(),0,16);

		$payData = array_filter($data,function($v){
			if($v !== '' && $v !== null){
				return true;
			}
		});
		ksort($payData);
		$sign2 = strtoupper(md5(urldecode(http_build_query($payData).'&key='.$md5key)));
		$data['sign'] = $sign2;

		Log::info('CCEX 更新出款银行信息提交参数:'.json_encode($data));

		$myCurl = new MyCurl();
		$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/merchant/user/addpaymentinfo',$data);
		Log::info('CCEX 更新出款银行信息结果:'.$result);

		$json = json_decode($result,true);
		$code = $json['code'];
		if(trim($code) == '0'){
			return ['status' => SUCCESS,'msg' => '会员银行信息更新成功！'];
		}
		return ['status' => FAILED,'msg' => '会员银行信息更新失败！'];
	}

	//查询订单状态
	public function queryWithdrawal($drawApplyId,$paymentAccount){
		$info_acct = json_decode($paymentAccount->info_acct,true);
		$merchant_id = $info_acct['merchant_id'];
		$md5key = $info_acct['md5key']??'';
		$drawApply = PayOrder::find($drawApplyId);
		if(!$drawApply){
			return ['status' => FAILED,'msg' => '未找到该订单！'];
		}
		$order_no = $drawApply->bill_no;

		$data = array(
			'merchant_id'  => $merchant_id,
			'out_trade_no' => $order_no,
			'nonstr'       => substr(Uuid::uuid4()->getHex(),0,16),
		);
		$data2 = array_filter($data,function($v){
			if($v !== '' && $v !== null){
				return true;
			}
		});
		ksort($data2);
		$sign = strtoupper(md5(urldecode(http_build_query($data2).'&key='.$md5key)));
		$data['sign'] = $sign;

		//Log::info('CCEX payout query param:' . json_encode($data));

		$myCurl = new MyCurl();
		$result = $myCurl->postHtmlBbin($info_acct['api_url'].'/api/refund/query',$data);
		\Log::info('CCEX payout query result:'.$result);
		$json = json_decode($result,true);
		$code = $json['code'];
		//Log::info('CCEX payout query code:' . $code);
		if(trim($code) == '0'){
			$refund_currency_list = $json['desc']['refund_currency_list'];
			if(empty($refund_currency_list)){
				return ['status' => SUCCESS,'content' => '','msg' => '处理中，挂单状态'];
			}
			$calc_amount = 0;
			foreach($refund_currency_list as $key => $obj){
				$buyer_paid = $obj['buyer_paid'];
				$seller_confirmed = $obj['seller_confirmed'];
				$currency_amount = number_format($obj['currency_amount'],2,'.','');

				if($buyer_paid == '1' && $seller_confirmed == '1'){
					$calc_amount = $calc_amount+$currency_amount;
				}
			}

			if(number_format($calc_amount,2,'.','') ==
				number_format($drawApply->draw_money-$drawApply->draw_fee,2,'.','')){
				return ['status' => SUCCESS,'content' => '','msg' => '订单已经完成'];
			}else{
				return ['status' => SUCCESS,'content' => '','msg' => '处理中，已经交收'.$calc_amount.'元'];
			}
		}else{
			return ['status' => SUCCESS,'content' => '','msg' => '查询失败'];
		}
	}

}
