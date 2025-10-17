<?php
/*
|--------------------------------------------------------------------------
| 提现API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use App\Libs\Helper;
use App\Libs\UserHelper;
use App\Models\BankAccount;
use App\Models\PayOrder;
use App\Models\Member;
use App\Models\Level;
use App\Services\Act\Activity\ActivityHelper;
use App\Services\Act\Facades\Act;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use App\Services\Pay\Facades\Pay;

class WithDrawalController extends BaseController{
	protected $memberId;
	protected $member;
	
	public function __construct(){
		$this->middleware(function($request,$next){
			$this->member = $request->user();
			$this->memberId = $this->member->member_id;
			if($this->member){
				return $next($request);
			}else{
				return response()->json(['status' => FAILED,'msg' => '无法获取您的账户信息，请重新登录！']);
			}
		});
	}
	
	//1.绑定银行卡-列表
	public function getBindCard(Request $request){
        $bank_id    = strip_tags($request->input('bank_id',''));
        $company_id = $this->member->company->company_id;
        $use_safety = 1;
        $use_safety = $use_safety?$use_safety:0;
		if(empty($this->member->real_name)){
			return response()->json(['status' => 1001,'content' => null,'msg' => '真实姓名未设置！']);
		}
        if($use_safety==1){
            if(empty($this->member->phone)){
                return response()->json(['status' => 1002,'content' => null,'msg' => '请先设置接收验证码的手机号码！']);
            }
        }

		$selfServiceBanks = Pay::getAllowDrawBanks();
		$data['bankList'] = Helper::collToArray($selfServiceBanks,[
		    'bank_code' => 'bankCode',
            'bank_name' => 'bankName'
        ]);

		if($bank_id!=''){
            $bankBind =  BankAccount::where('member_agent_type','App\Models\Member')
                ->where('is_locked',0)->where('is_allow_draw',1)
                ->where('member_agent_id',$this->memberId)
                ->where('bank_account_id',$bank_id)->get();
        }else{
            $bankBind = BankAccount::where('member_agent_type','App\Models\Member')
                ->where('member_agent_id',$this->memberId)
                ->where('is_locked',0)->where('is_allow_draw',1)->get();
        }

		$data['bankBind'] = array();
		foreach($bankBind as $key => $val){
			if($val->is_locked==1) continue;
			$data['bankBind'][] = [
				'bank_id'       => $val->bank_account_id,
                'bank_city'     => $val->opening_address,
				'bank_account'  => $val->bank_account_name,
				'bank_number'   => mb_substr($val->bank_account_number,0,4).'******'.mb_substr($val->bank_account_number,-4,4),
                'bank_NO_txt'   => $val->bank_account_number,
				'bank_name'     => config('enums.bank_code')[$val->bank_code],
				'bank_code'     => $val->bank_code,
				'bank_opening'  => $val->opening_bank,
				'defaults'      => $val->is_default,
			];
		}
        $data['real_name'] = '';
        if(!empty($this->member->real_name)){
            $data['real_name'] = $this->member->real_name;
        }
		return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);
	}
	
	//2. 银行卡绑定-添加
	public function addBindCard(Request $request){
        $company_id   = $this->member->company->company_id;
        $use_safety   = 1;
        $use_safety   = $use_safety?$use_safety:0;
		$bank_code    = strip_tags($request->input('bank_code',''));
		$acct_name    = strip_tags($request->input('bank_account',''));
        if(!empty($this->member->real_name) && $acct_name<>$this->member->real_name){
            $acct_name = $this->member->real_name;
        }

		$city         = strip_tags($request->input('bank_city',''));
		$bank_number  = strip_tags($request->input('bank_number',''));
		$phone_code   = strip_tags($request->input('phoneCode',''));
		$opening_bank = strip_tags($request->input('bank_opening',''));

        if($use_safety==1){
            if(empty($this->member->phone)){
                return response()->json(['status'=>FAILED,'content'=>'','msg'=>'请先设置接收验证码的手机号码！']);
            }
        }
		if(empty($bank_code)){
			return response()->json(['status' => 1000,'content' => "",'msg' => '请选择银行！']);
		}
        if(empty($city)||$city=='-市辖区-东城区'){
            return response()->json(['status' => 1001,'content' => "",'msg' => '请选择开户行所在城市！']);
        }
		if(empty($acct_name)){
			return response()->json(['status' => 1002,'content' => "",'msg' => '户名不能为空！']);
		}
		if(empty($bank_number)){
			return response()->json(['status' => 1003,'content' => "",'msg' => '银行卡帐号不能为空！']);
		}
		if(empty($opening_bank)){
			return response()->json(['status' => 1004,'content' => "",'msg' => '开户行不能为空！']);
		}
        if($use_safety==1){
            if(empty($phone_code)){
                return response()->json(['status' => 1005,'content' => "",'msg' => '手机验证码不能为空！']);
            }
            $res = Helper::checkPhoneCode($this->member->phone,$phone_code);
            if($res['status']!=SUCCESS){
                return response()->json(['status' => 1006,'content' => "",'msg' => '手机验证码输入错误！']);
            }
        }
        $bankBinds = $this->member->bankAccounts->where('is_allow_draw',1)->where('is_locked',0);
		if(count($bankBinds)>=5) {
			return response()->json(['status'=>1007,'content' =>"",'msg'=>'每个用户最多能绑定5张收款银行卡！']);
		}


		//卡号在公司数据库是否存在--仅限取款卡
		$isBank = BankAccount::where('bank_account_number',$bank_number)
			->where('member_agent_type','App\Models\Member')
			->where('is_allow_draw',1)
			->where('company_id',$company_id)->where('is_locked',0)->first();

		if($isBank){
			log::info('银行卡检查，卡号：'.$isBank->bank_account_number.'=》已存在公司库中，会员Id:'.$isBank->member_agent_id.'，户名：'.$isBank->bank_account_name);
			return response()->json(['status' => 1008,'content' => "",'msg' => '对不起，该银行卡号已被绑定！']);
		}

		//卡号在会员存款卡数据中是否存在
		$isBank = BankAccount::where('bank_account_number',$bank_number)
            ->where('member_agent_type','App\Models\Member')
			->where('is_allow_draw',0)
			->where('member_agent_id',$this->memberId)
            ->where('company_id',$company_id)->where('is_locked',0)->first();

		if($isBank){
			log::info('银行卡检查，卡号：'.$isBank->bank_account_number.'=》已存在在存款卡数据中！');
			$re = $isBank->update([
				'bank_code'         => $bank_code,
				'bank_account_name' => $acct_name,
				'opening_address'   => $city,
				'opening_bank'      => $opening_bank,
				'is_allow_draw'     => 1,
				'is_locked'         => 0,
			]);
			if(empty($this->member->real_name)){
				$this->member->update(['real_name' => $acct_name]);
			}
			log::info('银行卡已存在，更新为新的开户行，开户地：');
			log::info($isBank);
			return response()->json(['status' => SUCCESS,'msg' => '添加银行卡信息成功！']);
		}

		//卡号存在，但已被删除
        $isBankDel = BankAccount::where('bank_account_number',$bank_number)
            ->where('company_id',$company_id)->where('is_locked',1)->where('is_allow_draw',1)
            ->where('member_agent_type','App\Models\Member')->first();

		if($isBankDel){
		    if($isBankDel->member_agent_id==$this->memberId){
			    log::info('银行卡检查，卡号：'.$isBankDel->bank_account_number.'=》已存在，但已删除！');
                $reback = $isBankDel->update([
                    'bank_code'         => $bank_code,
                    'bank_account_name' => $acct_name,
                    'opening_address'   => $city,
                    'opening_bank'      => $opening_bank,
                    'is_default'        => 0,
                    'is_allow_draw'     => 1,
                    'is_locked'         => 0,
                ]);
                if(empty($this->member->real_name)){
                    $this->member->update(['real_name' => $acct_name]);
                }
                if ($reback) {
                    return response()->json(['status' => SUCCESS,'msg' => '添加银行卡信息成功！！']);
                }
                return response()->json(['status' => 1011,'msg' => '添加银行卡信息失败！']);
            }
			log::info('银行卡检查，卡号：'.$isBankDel->bank_account_number.'=》已存在，但已删除，且被其他会员绑定！');
            return response()->json(['status' => 1010,'content' => "",'msg' => '该银行卡号已经被其他会员绑定！']);
        }

		$insert  = [
            'member_agent_id'     => $this->memberId,
            'company_id'          => $this->member->company_id,
            'bank_account_number' => $bank_number,
            'bank_account_name'   => $acct_name,
            'member_agent_type'   => 'App\Models\Member',
            'bank_code'           => $bank_code,
            'opening_bank'        => $opening_bank,
            'opening_address'     => $city,
            'is_default'          => count($bankBinds) == 0 ? '1' : '0',
            'is_allow_draw'       => 1,
            'is_allow_deposit'    => 1
		];
		$newBank = BankAccount::create($insert);
        if(empty($this->member->real_name)){
            $this->member->update(['real_name' => $acct_name]);
        }
		log::info('银行卡检查，卡号：'.$bank_number.'不存在，新增数据：');
        log::info(json_encode($insert));
		if (!$newBank) {
			return response()->json(['status' => 1012,'msg' => '添加银行卡信息失败！']);
		}
		return response()->json(['status' => SUCCESS,'msg' => '添加银行卡信息成功！！！']);
	}

    public function updateBindCard(Request $request){
        $company_id   = $this->member->company->company_id;
        $use_safety   = 1;
        $use_safety   = $use_safety?$use_safety:0;
        $bank_id      = strip_tags($request->input('bank_id',''));
        $bank_code    = strip_tags($request->input('bank_code',''));
        $city         = strip_tags($request->input('bank_city',''));
        $bank_number  = strip_tags($request->input('bank_number',''));
        $phone_code   = strip_tags($request->input('phoneCode',''));
        $opening_bank = strip_tags($request->input('bank_opening',''));

        $bank_info    = BankAccount::where('bank_account_id',$bank_id)
            ->where('member_agent_type','App\Models\Member')
            ->where('member_agent_id',$this->memberId)->first();
        if(!$bank_info){
            return response()->json(['status' => 1010,'content' => "",'msg' => '提交的银行卡信息不存在！']);
        }
        if(empty($bank_code)){
            return response()->json(['status' => 1000,'content' => "",'msg' => '请选择银行！']);
        }
        if(empty($city)){
            return response()->json(['status' => 1001,'content' => "",'msg' => '开户行所在城市不能为空！']);
        }

        if(empty($bank_number)){
            return response()->json(['status' => 1003,'content' => "",'msg' => '银行卡帐号不能为空！']);
        }
        if(empty($opening_bank)){
            return response()->json(['status' => 1004,'content' => "",'msg' => '开户行不能为空！']);
        }
        if($use_safety==1){
            if(empty($phone_code)){
                return response()->json(['status' => 1005,'content' => "",'msg' => '手机验证码不能为空！']);
            }
            //手机验证码验证
            $res = Helper::checkPhoneCode($this->member->phone,$phone_code);
            if($res['status']!=SUCCESS){
                return response()->json(['status' => 1006,'content' => "",'msg' => '手机验证码输入错误！']);
            }
        }

        //卡号在公司数据中是否存在
        $isBank = BankAccount::where('bank_account_number',$bank_number)
            ->where('bank_account_id','<>',$bank_id)->where('member_agent_type','App\Models\Member')
            ->where('company_id',$company_id)->first();
        if($isBank){
            return response()->json(['status' => 1008,'content' => "",'msg' => '对不起，该银行卡号已被绑定！']);
        }

        $data  = [
            'bank_account_number' => $bank_number,
            'bank_code'           => $bank_code,
            'opening_bank'        => $opening_bank,
            'opening_address'     => $city,
        ];
        $res = $bank_info->update($data);

        if (!$res) {
            return response()->json(['status' => 1009,'msg' => '银行卡信息保存失败！']);
        }
        return response()->json(['status' => SUCCESS,'msg' => '银行卡信息保存成功！']);
    }

	//3. 银行卡绑定-删除
	public function delBindCard(Request $request){
		$bank_id = strip_tags($request->input('bank_id',''));
		if(empty($bank_id)){
			return response()->json(['status' => 1001,'content' => "",'msg' => '请选择要删除银行卡！']);
		}
        $bank_info = BankAccount::where('member_agent_type','App\Models\Member')
            ->where('member_agent_id',$this->memberId)
            ->where('bank_account_id',$bank_id)->where('is_locked',0)->first();
		if(empty($bank_info)){
			return response()->json(['status' => 1002,'content' => "",'msg' => '对不起，未找到要删除的银行卡信息！']);
		}
        if($bank_info->is_default==1) {
            return response()->json(['status' => 1003,'content' => "",'msg' => '对不起，默认银行卡不允许删除！']);
        }
		$bank_info->update(['is_locked' => 1]);
		
		return response()->json(['status' => SUCCESS,'content' =>"",'msg' => '银行卡删除成功！']);
	}
	
	//4.设置默认银行卡
	public function defaultBank(Request $request){
		$bank_id = strip_tags($request->input('bank_id',''));
		if(empty($bank_id)){
			return response()->json(['status' => 1001,'content' => "",'msg' => '请选要择设置的银行卡！']);
		}
		
        $bank_info = BankAccount::where('member_agent_type','App\Models\Member')
            ->where('member_agent_id',$this->memberId)
            ->where('bank_account_id',$bank_id)->where('is_locked',0)->first();
		if(empty($bank_info)){
			return response()->json(['status' => 1002,'content' => "",'msg' => '对不起，未找到该银行卡信息！']);
		}
		$defaultBank = $this->member->bankAccounts->where('is_default',1)->first();

		if($defaultBank){
			$defaultBank->update(['is_default' => 0]);
		}

		$res = $bank_info->update(['is_default' => 1]);
		if(!$res) {
			return response()->json(['status' => 1003,'content' => "",'msg' => '默认银行卡设置失败！']);
		}

		return response()->json(['status' => SUCCESS,'content' => '','msg' => '默认银行卡设置成功！']);
	}
	
	//5.取款申请列表
	public function listWithdraw(Request $request){
		$withdrawal = $this->member->drawApplies()->get();
		$drawStatus = config('enums.draw_status');
		$data['list']   = [];
		foreach ($withdrawal as $key => $val){
            $data['list'][]  = [
                'id'         => $val->draw_apply_id,
                'billNo'     => $val->bill_no,
                'bank_id'    => $val->bank_account_id,
                'money'      => $val->draw_money,
                'fee'        => $val->draw_fee,
                'status'     => $drawStatus[$val->draw_status],
                'times'      => $val->draw_time,
                'acceptTime' => $val->accept_time,
                'remark'     => $val->description,
            ];
		}
		
		return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);
		
	}
	
	//6. 新增取款申请页面
	public function getWithdraw(Request $request){
        $company_id = $this->member->company->company_id;
        $use_safety = 1;
        $use_safety = $use_safety?$use_safety:0;
        $data['use_safety'] = $use_safety;
        if($use_safety==1){
            if(empty($this->member->trade_pwd)) {
                return response()->json(['status' => 1001,'content' => "",'msg' => '对不起，请先设置交易密码，再进行该操作！']);
            }
        }
		$mermber_level_id = $this->member->member_level_id;
		$member_level     = Level::where('member_level_id',$mermber_level_id)->first();
		$level_name       = $member_level->member_level_name;
		$draw_times       = $member_level->draw_times??0;
		$draw_money       = $member_level->draw_money??0;
		if($draw_times==0){
			$draw_times = '不限';
		}
		if($draw_money==0){
			$draw_money = '不限';
		}
		$drawMsg = '当前会员等级：'.$level_name.'，日提款次数：'.$draw_times.'，日提款额度：'.$draw_money;
		$data['drawMsg']  = $drawMsg;
        $bankBind = BankAccount::where('member_agent_id',$this->memberId)
            ->where('member_agent_type','App\Models\Member')
            ->where(['is_locked'=>0,'is_allow_draw'=>1])
            ->orderBy('is_default','desc')->get();
		if(count($bankBind)<=0){
			return response()->json(['status' => 1002,'content' => "",'msg' => '对不起，请先绑定取款银行卡，再进行该操作！']);
		}
		$data['billNo']   = Pay::getValidBillNo('Q');
		$bankCode = Pay::getAllowDrawBanks();
		$data['bankList'] = Helper::collToArray($bankCode,['bank_code' => 'bankCode','bank_name' => 'bankName']);
		$data['money'] 	  = $this->member->balance;
		$banks = config('enums.bank_code');
		$data['bankBind'] = array();
		
		foreach($bankBind as $key => $val){
			$data['bankBind'][] = [
				'bank_id'      => $val->bank_account_id,
				'bank_account' => $val->bank_account_name,
				'bank_number'  => mb_substr($val->bank_account_number,0,4).'*****'.mb_substr($val->bank_account_number,-4,4),
				'bank_name'    => $banks[$val->bank_code],
				'bank_code'    => $val->bank_code,
				'bank_opening' => $val->opening_bank,
				'default'      => $val->is_default,
			];
		}
		
		return response()->json(['status' => SUCCESS,'content' => $data,'msg' => 'success']);
	}
	
	//7.取款申请处理
	public function addWithdraw(Request $request){
        $company_id = $this->member->company->company_id;
        $use_safety = 1;
        $use_safety = $use_safety?$use_safety:0;
		$bank_id  = strip_tags($request->input('bank_id',''));
		//$money    = intval($request->input('money',0));
		$money    = floatval($request->input('money',0));
		$password = $request->input('password','');
		$billNo   = strip_tags($request->input('billNo',''));
		if(empty($bank_id)){
			return response()->json(['status' => 1001,'content' => "",'msg' => '未选择收款银行卡！']);
		}
		if(!preg_match("/^[0-9]+$/",$money)){
			return response()->json(['status' => 1003,'content' => "",'msg' => '取款金额只能是整数',]);
		}
		if($money<100){
			return response()->json(['status' => 1002,'msg' => '取款金额不能小于100元！']);
		}

		if(empty($billNo)){
			return response()->json(['status' => 1004,'content' => "",'msg' => '取款订单号不能为空！']);
		}

        if($use_safety==1) {
            if (empty($password)) {
                return response()->json(['status' => 1005, 'content' => "", 'msg' => '交易密码不能为空！']);
            }

            //检查交易状态及密码
            $result = UserHelper::checkMemberTradeFailed($this->memberId);
            if (!$result['result']) {
                Log::info('取款失败：用户交易状态已被锁定！');
                Log::info('用户名：' . $this->member->login_name . '，金额：' . $money . '，单号：' . $billNo);
                //Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18001$$$$$$$$$$$$$$$$$');
                return response()->json(['status' => 1006, 'content' => "", 'msg' => $result['message'],]);             //交易密码错误次数超过限制，已被锁定交易
            }

            $res = \Hash::check($password, $this->member->trade_pwd);
            if (!$res) {
                $limiCount = 5;
                $count = UserHelper::updateMemberTradeFailed($this->memberId);                                          //更新失败次数
                if ($count == $limiCount) {                                                                             //如果达到限制次数，锁定交易
                    $member = $this->member;
                    $member->is_allow_draw = 0;
                    $member->save();
                    $data = [
                        'member_id' => $member->member_id,
                        'commit_type' => 'member_status',
                        'commit' => '交易密码错误' . $limiCount . '次，系统锁定',
                    ];
                    Helper::saveAdminCommit($data);
                }
                Log::info('取款失败：交易密码错误！');
                Log::info('用户名：' . $this->member->login_name . '，金额：' . $money . '，单号：' . $billNo);
                //Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18002$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
                return response()->json(['status' => 1007, 'content' => "", 'msg' => '取款失败：交易密码错误！',]);
            }
            UserHelper::updateMemberTradeSuccess($this->memberId);                                                      //重置交易密码输入错误次数为：0
        }

        //检查是否有未结束的取款订单
        $processingDraw = $this->member->drawApplies->whereIn('draw_status',['apply','accept','audit'])->first();
		if($processingDraw){
            return response()->json(['status'=>1008,'content'=>'','msg'=>'上一笔取款正在处理中，请稍后再申请！']);
        }

		//检查取款订单号是否已存在
		$checkBillNo = $this->member->drawApplies->where('bill_no',$billNo)->first();
		if($checkBillNo){
			Log::info('取款失败：取款款单号已存在！');
			Log::info('用户名：'.$this->member->login_name.'，金额：'.$money.'，单号：'.$billNo);
			//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18003$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
			return response()->json(['status' => 1009,'content' => "",'msg' => '取款订单号已存在，请刷新后重试！',]);
		}
		$member = Member::where('member_id',$this->memberId)->lockForUpdate()->first();
		//余额检查
		if($money>$member->balance){
			Log::info('取款失败：账户余额不足！');
			Log::info('用户名：'.$this->member->login_name.'，金额：'.$money.'，单号：'.$billNo);
			//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18004$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
			return response()->json(['status' => 1010,'content' => "",'msg' => '您的账户余额不足，请输入正确的取款金额！',]);
		}
		
		//银行卡是否可以提款
		$bankBind = BankAccount::where('bank_account_id',$bank_id)
            ->where('member_agent_type','App\Models\Member')
            ->where('member_agent_id',$this->memberId)->first();
		if(!$bankBind){
			Log::info('取款失败：未能找到绑定银行卡！');
			Log::info('用户名：'.$this->member->login_name.'，金额：'.$money.'，银行卡ID：'.$bank_id);
			//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18005$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
			return response()->json(['status' => 1011,'content' => "",'msg' => '对不起，未找到您绑定的银行卡信息！',]);
		}

		//angela 这里要做取款检查吧
		Log::info('drawApplyCheck：取款流水检查！');

		if(!Act::drawApplyCheck($this->memberId)){
			Log::info('取款失败：有活动没有结束！');
			Log::info('用户名：'.$this->member->login_name.'，金额：'.$money.'，单号：'.$billNo);
			//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18005$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
			return response()->json(['status' => 1011,'content' => "",'msg' => '对不起，您有活动没有结束，请先完成流水要求！',]);
		}
		$start_times = date('Y-m-d').' 00:00:01';
		$end_times   = date('Y-m-d').' 23:59:59';
		$mermber_level_id = $this->member->member_level_id;
		$member_level     = Level::where('member_level_id',$mermber_level_id)->first();
		$draw_times       = $member_level->draw_times??0;
		$draw_money       = $member_level->draw_money??0;
		if($draw_times>0){
			$today_draws = PayOrder::where('created_at','>=',$start_times)->where('created_at','<=',$end_times)
				->where('member_agent_id',$this->memberId)->where('draw_status','success')->count();
			Log::info('【取款次数检查】今日已取款次数：'.$today_draws);
			if($today_draws>=$draw_times){
				$msg = '您今日取款次数已达上限，每日取款次数：'.$draw_times;
				Log::info('【取款限制】帐号:'.$this->member->login_name.',已取次数:'.$today_draws.'，会员限次:'.$draw_times);
				return response()->json(['status' => 1022,'content'=> "",'msg' =>$msg]);
			}
		}
		
		if($draw_money>0){
			$today_money = PayOrder::where('created_at','>=',$start_times)->where('created_at','<=',$end_times)
				->where('member_agent_id',$this->memberId)->where('draw_status','success')->sum('draw_money');
			Log::info('【取款金额检查】今日已取款金额：'.$today_money);
			if(($money+$today_money)>=$draw_money){
				$msg = '取款金额不能超过限额，您的会员级别每日取款限额：'.$draw_money.'，已取金额：'.$today_money.'，本次取款金额：'.$money;
				Log::info('【取款限制】帐号：'.$this->member->login_name.'，已取金额：'.$today_money.'，本次取款金额：'.$money.'，会员限额：'.$draw_money);
				return response()->json(['status' => 1023,'content'=> "",'msg' =>$msg]);
			}
		}
		
		$request['withdrawBillNo']  = $billNo ;
		$request['bank_account_id'] = $bank_id;
		$ret = Pay::genDrawApply($request);
		if(!$ret){
			Log::info('取款失败：创建提款记录失败！');
			Log::info('用户名：'.$this->member->login_name.'，金额：'.$money.'，单号：'.$billNo);
			//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18001$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
			return response()->json(['status' => 1012,'content' => "",'msg' => '网络连接失败，请稍后重试！',]);
		}
		
		Log::info('用户提款=》用户名：'.$this->member->login_name.'，金额：'.$money.'，单号：'.$billNo);
		//Log::info('$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$18001$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$');
		
		return response()->json(['status' => SUCCESS,'content' => "",'msg' => '取款申请提交成功！',]);
	}
	
	//8. 取消取款
	public function cancelWithdraw(Request $request){
		$bill_no = strip_tags($request->input('bill_no',''));
		if(empty($bill_no)){
			return response()->json(['status' => 1001,'content' => "",'msg' => '取款订单号不能为空！',]);
		}
		
		$withdrawal = $this->member->drawApplies()->where('bill_no',$bill_no)->first();
		if(empty($withdrawal)){
			return response()->json(['status' => 1002,'content' => '','msg' => '未找到该取款订单！']);
		}
		if($withdrawal->draw_status!='apply'){
			return response()->json(['status' => 1003,'content' => '','msg' => '取款订单当前状态不允许取消！']);
		}
		
		$ret = Pay::cancelDrawApplyToApi($bill_no);
		if($ret){
			return response()->json(['status' => SUCCESS,'content' => '','msg' => '取消取款成功！']);
		}
		
		return response()->json(['status' => 1004,'content' => '','msg' => '取消取款失败！']);
	}
	
}
