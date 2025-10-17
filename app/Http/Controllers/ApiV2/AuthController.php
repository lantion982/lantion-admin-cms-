<?php
/*
|--------------------------------------------------------------------------
| 注册登录认证API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use App\Libs\Helper;
use App\Libs\UserHelper;
use App\Models\Member;
use Captcha;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Validator;

class AuthController extends BaseController{

	//获取验证码
	public function getCaptcha(){
        return response()->json(['status'=>SUCCESS,'content'=>Captcha::create('default',true),'msg'=>'success']);
	}
	
	//获取手机验证码
	public function getPhoneVerify(Request $request){
		$_domain     = strip_tags($request->headers->get('domain'));
		$phoneNumber = strip_tags($request->input('phone',''));
		$ret = preg_match("/1[345789]\d{9}$/",$phoneNumber);
		
		if ($this->verifyCodeCheck($request)) {
			return response()->json(['status' => 1005, 'msg' => '验证码输入错误！']);
		}
		
		if(!$ret){
			return response()->json(['status' => 1001,'msg' => '手机号码格式有误!','content' => []]);
		}
		
		$domain = Domain::where(['domain' => $_domain,'is_active' => 1])->first();
		if(!$domain) {
			return response()->json(['status' => 1002,'content' => "",'msg' => '请求域名不存在！']);
		}
		$companyId = $domain->company_id;
		
		//发送验证吗
		try{
			$res = Helper::sendPhoneCode($phoneNumber,$domain->company->name,$companyId);
		}catch(\Exception $e){
			\Log::error('手机验证码发送失败：'.$e->getMessage());
			return response()->json(['status' => 1003,'content' => "",'msg' => '网络错误，手机验证码发送失败！']);
		}
		return response()->json($res);
	}
	
	//登录认证
	public function login(Request $request){
		$domain     = strip_tags($request->headers->get('domain'));
		$username   = strip_tags($request->input('username',''));
		$password   = $request->input('password','');
		$vdres = Validator::make($request->all(),['username' => 'required|min:3','password' => 'required','verifyCode'=>'required']);
		if($vdres->fails()) {
			return response()->json(['status' => 1001,'msg' => '请输入用户名、登录密码及验证码!']);
		}
		
		//图片验证码验证
		if ($this->verifyCodeCheck($request)) {
			return response()->json(['status' => 1005, 'msg' => '验证码输入错误！']);
		}

        /*
        $domainModel = Domain::where('domain',$domain)->first();
		if(!$domainModel){
			return response()->json(['status' => 1002,'msg' => '请求域名不存在！']);
		}
        $company_id   = $domainModel->company->company_id;
        $login_prefix = $domainModel->company->member_prefix;
        */

        $login_limit  = config('auth.LOGIN_FAILED_LIMIT',0);
        $ip_limit     = config('auth.IP_LOGIN_LIMIT',0);
        $ipAddr       = Helper::getClientIP();
        if($ip_limit==1){
            $ret = Helper::checkIpLoginFailed($ipAddr,$domain);
            if($ret['result']==false) {
                return response()->json(['status' => 1003,'msg' => $ret['message']]);
            }
        }
		
		//检测用户的状态是否可用
		$member = Member::where('is_agent',0)
			->where(function($query) use($username){
				$query->where('login_name',$username)->orWhere('phone',$username);
			})->first();
		
		if(!$member) {
			return response()->json(['status' => 1004,'msg' => '登录帐号或登录密码不正确!']);
		}
		
		$username = $member->login_name;
		//启用:登录失败次数检查
        if($login_limit==1){
            $ret = UserHelper::checkMemberLogin ($username);
            if($ret['result']==false){
                return response()->json(['status' => 1006,'msg' => $ret['message']]);
            }
        }

		try{
			$data['username'] = $username;
			$data['password'] = $password;
			$arr = $this->attempt($data,$request);
			//Log::info($arr);
			if(!$arr['result']){
				//记录登录失败日志
				Helper::recordLogLogin($member->member_id,'App\Models\Member',$member->login_name,$ipAddr,'failed');
				//更新ip登录统计，失败增加1
				Helper::updateIpLoginFailed($ipAddr);
				//更新会员表，登录失败增加1
				$count      = UserHelper::memberLoginFailed($member->member_id);
				$limitCount = config('auth.LOGIN_FAILED_COUNT',5);
                if($login_limit==1&&$count >= $limitCount){
	                $data = ['member_id' => $member->member_id,'commit_type'=>'member_status', 'commit'=>'登录密码错误'.$limitCount.'次，系统锁定账号!'];
	                Helper::saveAdminCommit($data);
                }
				return response()->json(['status' => 1007,'msg' => '用户名或密码不正确!']);
			}
			//更新用户登录表 $domain 字段
			UserHelper::updateLoginDomain($member->member_id,$domain);
			//记录登录日志，成功
			Helper::recordLogLogin($member->member_id,'App\Models\Member',$member->login_name,$ipAddr,'success');
			//更新ip登录统计，清零
			Helper::updateIpLoginSuccess($ipAddr);
			//登录失败次数清零
            UserHelper::memberLoginSuccess($member->member_id,$ipAddr);
			
		}catch(\Exception $e){
            Log::error('login:'.$e->getMessage());
			return response()->json(['error' => '无法创建Token'],500);
		}
		//登录成功，生成单点Token
		$singleToken = $this->makeSingleSecret($request,$member);
		$arrToken = ['accessToken' 	 => $arr['accessToken'],
			'refreshToken' 	  => $arr['refreshToken'],
			'singleToken'	  => $singleToken,
			'tokensExpireIn'  => $arr['tokensExpireIn'],
			'refreshExpireIn' => $arr['refreshExpireIn']];
		
		return response()->json(['status'=>SUCCESS,'content'=>$arrToken,'msg'=>'success']);
		
	}

	//登录验证并获取token
	public function attempt(array $data = [],$request){
		$http = new \GuzzleHttp\Client;
		$accessToken = '';
		$arr['result'] = false;
		try{
			$api_url = config('passport.api_url');
			//Log::info('API_URL=>'.$api_url);
			$response = $http->post($api_url,['form_params' => [
				'grant_type'    => 'password',
				'client_id'     => config('passport.client_id'),
				'client_secret' => config('passport.client_secret'),
				'username'      => $data['username'],
				'password'      => $data['password'],
				'scope'         => '',
			],]);

			$accessToken  = Arr::get(json_decode((string)$response->getBody(),true),'access_token');
			$refreshToken = Arr::get(json_decode((string)$response->getBody(),true),'refresh_token');
			$arr['accessToken']     = $accessToken;
			$arr['refreshToken']    = $refreshToken;
			$arr['tokensExpireIn']  = Carbon::now()->addMinutes(config("passport.token_time"))->timestamp;
			$arr['refreshExpireIn'] = Carbon::now()->addDays(config("passport.refresh_time"))->timestamp;
		}catch(\Exception $e){
			Log::error('【会员登录】失败：'.$e->getMessage());
			$arr['result'] = false;
			return $arr;
		}

		if(empty($accessToken)||empty($refreshToken)){
			$arr['result'] = false;
			return $arr;
		}
		$arr['result'] = true;
		return $arr;
	}
	
	//刷新Token
	public function refreshToken(Request $request){
		$refToken = $request->input('refreshToken','');
		if($refToken=='') {
			return response()->json(['status' => 1001,'msg' => '[refreshToken]参数值为空!']);
		}
		$http          = new \GuzzleHttp\Client;
		$arr['result'] = false;
		$api_url = config('passport.api_url','');
		try{
			$response = $http->post($api_url,['form_params' => [
				'grant_type' 	=> 'refresh_token',
				'refresh_token' => $refToken,
				'client_id' 	=> config('passport.client_id'),
				'client_secret' => config('passport.client_secret'),
				'scope' => '',
				],]);
			$accessToken  = Arr::get(json_decode((string)$response->getBody(),true),'access_token');
			$refreshToken = Arr::get(json_decode((string)$response->getBody(),true),'refresh_token');				//获取后，更新 refresh_token
			$arr['accessToken']     = $accessToken;
			$arr['refreshToken']    = $refreshToken;
			$arr['tokensExpireIn']  = Carbon::now()->addMinutes(config("passport.token_time"))->timestamp;
			$arr['refreshExpireIn'] = Carbon::now()->addDays(config("passport.refresh_time"))->timestamp;
		}catch(\Exception $e){
			Log::error('【会员登录】刷新Token失败'.$e->getMessage());
			return response()->json(['status' => 1002,'msg' => '登录已过期，请重新登录！']);
		}
		$arrToken = [
			'accessToken'     => $arr['accessToken'],
			'refreshToken'    => $arr['refreshToken'],
			'tokensExpireIn'  => $arr['tokensExpireIn'],
			'refreshExpireIn' => $arr['refreshExpireIn']
		];
		
		return response()->json(['status' => SUCCESS,'content' => $arrToken,'msg' => 'success']);
	}
	
	//重置密码
	public function restPassword(Request $request){
		$_domain   = strip_tags($request->headers->get('domain'));
		$username  = strip_tags($request->input('username',''));
		$phoneCode = strip_tags($request->input('phoneCode',''));
		$password  = $request->input('password','');
		$validator = Validator::make($request->all(),[
			'username'  => 'required',
			'password'  => 'required|min:6|max:12|confirmed',
			'phoneCode' => 'required',
		],
		[
			'username.required'  => '用户名不能为空',
			'phoneCode.required' => '手机验证码不能为空',
			'password.required'  => '密码不能为空',
			'password.confirmed' => '确认密码输入不一致',
			'password.min'  	 => '密码最少6位字符',
			'password.max'  	 => '密码不能超过12位字符',
		]);
		if($validator->fails()){
			return response()->json(['status' => 1001,'msg' => $validator->errors()->first()]);
		}
		$domain = Domain::where(['domain' => $_domain,'is_active' => 1])->first();
		if(!$domain){
			return response()->json(['status' => 1002,'msg' => '请求域名不存在!']);
		}
		$login_name = $domain->company->member_prefix.$username;
		$member = Member::where(['login_name' => $login_name])->first();
		if(!$member){
			return response()->json(['status' => 1003,'msg' => '输入的用户不存在!']);
		}
		
		//手机验证码验证
		$ret = Helper::checkPhoneCode($member->phone,$phoneCode);
		if($ret['status']!=SUCCESS){
			return response()->json($ret);
		}
		
		$login_pwd = bcrypt($password);
		try{
		    Member::where('member_id',$member->member_id)->update(['login_pwd' => $login_pwd]);
        }catch (\Exception $e){
            return response()->json(['status' => 1005,'msg' => '密码重置失败!']);
        }
        return response()->json(['status' => SUCCESS,'msg' => '密码重置成功!']);
	}
	
	//取回密码-发送手机验证码
	public function getPasswdPhone(Request $request){
		$username = strip_tags($request->input('username',''));
		$domain   = strip_tags($request->headers->get('domain'));
		$verifyCode = strtolower($request['verifyCode']);
		if(empty($username)) {
			return response()->json(['status' => 1001,'content' => '','msg' => '请输入用户账号!']);
		}
		if(empty($verifyCode)) {
			return response()->json(['status' => 1003,'content' => '','msg' => '请输入验证码!']);
		}
		if ($this->verifyCodeCheck($request)) {
			return response()->json(['status' => 1004, 'msg' => '验证码输入错误！']);
		}
		$domain = Domain::where(['domain' => $domain,'is_active' => 1])->first();
		if(!$domain){
			return response()->json(['status' => 1002,'content' => '','msg' => '请求的域名不存在!']);
		}
		
		$preName = $domain->company->member_prefix;
		$member = Member::where(['login_name' => $preName.$username])->first();
		if(empty($member)) {
			return response()->json(['status' => 1005,'content' => '','msg' => '未能找到此用户信息!']);
		}
		if(empty($member->phone)) {
			return response()->json(['status' => 1006,'content' => '','msg' => '该用户名未绑定手机号码，请联系在线客服申请密码找回!']);
		}
		
		//发送验证吗
		try{
			$res = Helper::sendPhoneCode($member->phone,$domain->company->name,$domain->agent->company_id);
		}catch(\Exception $e){
			Log::error('手机验证码发送失败：'.$e->getMessage());
			return response()->json(['status' => 1007,'content' => '','msg' => '网络错误，手机验码发送失败！']);
		}
		return response()->json($res);
	}
	
	//生成单点登录token
	public function makeSingleSecret(Request $request,$member){
		$time = time();
		$singleToken = md5($request->getClientIp().$member->member_id.$time);
		$redis = \RedisServer::connection();
		$redis->set('STRING_SINGLETOKEN_'.$member->member_id,$time);
		$request->session()->put('SINGLETOKEN',$singleToken);
		return $singleToken;
	}
	
	//验证码验证
	public function verifyCodeCheck($request){
		$verifyCode = strtolower($request['verifyCode']);
		$verifyKey = $request['verifyKey'];
		return !Captcha::check_api($verifyCode,$verifyKey);
	}
	
	//手机号是否已被注册
	public function validatePhoneUnique($phone,$company_id){
		$isHave = Member::where(['company_id' => $company_id,'phone' => $phone])->first();
		return $isHave;
	}

	//检查设备
	public function get_device_type(){
		//全部变成小写字母
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$type = 'Phone-other';
		if(strpos($agent,'iphone')||strpos($agent,'ipad')){
			$type = 'Phone-ios';
		}
		if(strpos($agent,'android')){
			$type = 'Phone-android';
		}
		return $type;
	}
}
