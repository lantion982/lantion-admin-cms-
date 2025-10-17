<?php
/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

namespace App\Libs;

use App\Models\AdminCommit;
use App\Models\IpList;
use App\Models\IpLog;
use App\Models\LoginLog;
use App\Models\AdminLog;
use App\Models\SmsLog;
use App\Models\Member;
use App\Models\Level;
use App\Models\MoneyLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Helper{

	//返回ip
	public static function getClientIP(){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if(!stripos($clientIP,','))
				return $clientIP;
			$array_ips = explode(',',$clientIP);
			$clientIP = trim($array_ips[0]);
			$clientIP = trim($clientIP,',');
			return $clientIP;
		}
		if(isset($_SERVER['HTTP_X_REAL_IP'])){
			return $_SERVER['HTTP_X_REAL_IP'];
		}
		if(isset($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		}
		return '';
	}

	public static function html($str){
		$str = preg_replace("/<(\/)?([a-zA-Z]+)[^>]*>/","",htmlspecialchars_decode($str));
		$str = str_replace(PHP_EOL,'',$str);
		$str = trim($str,"\t\n\r\0\x0B");
		$str = str_replace("\t","",$str);
		$str = htmlspecialchars($str);
		return $str;
	}

	//公用GET请求方法
	public static function getHtml($url,$data = '',$cookie = '',$header = '',$redirect = '',$referer = ''){
		$ch  = curl_init($url);
		$ssl = substr($url,0,8)=="https://"?true:false;
		if($ssl){
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		}
		curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.2; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,CURL_TIMEOUT);
		if($cookie!=''){
			curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		}
		if($header!=''){
			curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		}
		if($referer!=''){
			curl_setopt($ch,CURLOPT_REFERER,$referer);
		}
		if($redirect!=''){
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
			$text = curl_exec($ch);
			$headers = curl_getinfo($ch);
			return $headers['url']?$headers['url']:'e';
		}
		$text = curl_exec($ch);
		if(curl_errno($ch)){
			log::info('【GET_Curl_error】：'.curl_error($ch));
			return curl_error($ch);
		}
		curl_close($ch);
		return $text;
	}

	//公用POST请求方法
	public static function postHtml($url,$data = '',$cookie = '',$header = '',$redirect = '',$referer = ''){
		$ch  = curl_init($url);
		$ssl = substr($url,0,8)=="https://"?true:false;
		curl_setopt($ch,CURLOPT_POST,1);
		if($data!=''){
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}
		if($ssl){
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		}
		curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.2; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,CURL_TIMEOUT);
		if($cookie!=''){
			curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		}
		if($header!=''){
			curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		}
		if($referer!=''){
			curl_setopt($ch,CURLOPT_REFERER,$referer);
		}
		if($redirect!=''){
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
			$text    = curl_exec($ch);
			$headers = curl_getinfo($ch);
			return $headers['url']?$headers['url']:'e';
		}
		$text = curl_exec($ch);
		if(curl_errno($ch)){
			log::info('【POST_Curl_error】：'.curl_error($ch));
			return curl_error($ch);
		}
		curl_close($ch);
		return $text;
	}

	//POST JSON 数据公共方法
	public static function postJson($url,$data = ''){
		$ch = curl_init($url);
		$ssl = substr($url,0,8)=="https://"?true:false;
		if($data!=''){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}
		if($ssl){
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		}
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,CURL_TIMEOUT);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json; charset=utf-8','Content-Length:'.strlen($data)));
		$text = curl_exec($ch);
		if(curl_errno($ch)){
			log::info('postJson Curl error：'.curl_error($ch));
			//return '99999';
		}
		curl_close($ch);
		return $text;
	}

	//将数据库模型对象，转化为数组
	public static function collToArray(Collection $clo,Array $columns){
		$arr = [];
		if(count($clo)){
			$i = 0;
			foreach($clo as $key=>$value){
				foreach($columns as $k=>$v){
					$arr[$i][$v] = $value[$k];
				}
				$i++;
			}
		}
		return $arr;
	}

	//将数据库模型对象，转化为:array
	public static function collToArrayByKey(Collection $clo,$key,$value){
		$arr = [];
		if(count($clo)){
			foreach($clo as $k=>$v){
				$arr[$v[$key]] = $v[$value];
			}
		}
		return $arr;
	}

	//发送手机短信
	public static function sendPhoneCode($phone,$companyName,$companyId){
		//生成验证码
		$code = rand(1000,9999);
		$text = '您的验证码为:'.$code.'【在线娱乐】';
		$SMS_KEY = '4458c00f9fea12f507227eb495241949';
		$send_phone = SmsLog::where(['phone'=>$phone])->first();
		if(!empty($send_phone)){
			if($send_phone->time_out>\Carbon\Carbon::now()&&$send_phone->is_active==1){
				return ['status'=>FAILED,'content'=>null,'msg'=>'前次发送验证码未过期，请用前次发送验证码验证。'];
			}
			if($send_phone->send_today>5){
				return ['status'=>FAILED,'content'=>null,'msg'=>'您的号码超过每日发送次数限制，请明天再试'];
			}
		}
		//发送验证码
		try{
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,"http://sms-api.luosimao.com/v1/send.json");
			curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,CURL_TIMEOUT);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($ch,CURLOPT_HEADER,FALSE);
			curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
			curl_setopt($ch,CURLOPT_USERPWD,'api:key-'.$SMS_KEY);
			curl_setopt($ch,CURLOPT_POST,TRUE);
			curl_setopt($ch,CURLOPT_POSTFIELDS,array('mobile'=>$phone,'message'=>$text));
			$res = curl_exec($ch);
			$result = json_decode($res);
			curl_close($ch);
			if(empty($result->msg)){
				Log::info('号码:'.$phone.'=>短信发送失败=>result:'.$result);
				if(!empty($send_phone)){
				    $data = ['code'=>$code,'time_send'=>date('Y-m-d H:i:s'),'time_out'=>date('Y-m-d H:i:s',time()+900),'is_active'=>'1'];
					SmsLog::where(['phone'=>$phone])->update($data);
				}else{
					$sendData['phone'] = $phone;
					$sendData['code']  = $code;
					$sendData['company_id'] = $companyId;
					$sendData['time_send']  = date('Y-m-d H:i:s',time());
					$sendData['time_out']   = date('Y-m-d H:i:s',time()+900);
					SmsLog::create($sendData);
				}
				return ['status'=>FAILED,'content'=>null,'msg'=>'短信通道维护中，请联系客服获取！'];
			}
		}catch(\Exception $e){
			Log::error('号码:'.$phone.'=>短信发送失败=>:err'.$e->getMessage());
			return ['status'=>FAILED,'content'=>null,'msg'=>'网络错误，短信发送失败，请稍后重试！'];
		}
		Log::info('短信发送:'.$phone.json_encode($result));
        //短信发送成功
		if($result->error===0){
			if(!empty($send_phone)){
			    $updata = ['code'=>$code,'time_send'=>date('Y-m-d H:i:s'),'time_out'=>date('Y-m-d H:i:s',time()+120),'is_active'=>'1'];
				SmsLog::where(['phone'=>$phone])->update($updata);
				SmsLog::where(['phone'=>$phone])->increment('send_total',1);
				if(date('Y-m-d',time())==date('Y-m-d',strtotime($send_phone->time_send))){
					SmsLog::where(['phone'=>$phone])->increment('send_today',1);    //记录该号码今天发送次数
				}
			}else{
				$sendData['phone'] = $phone;
				$sendData['code']  = $code;
				$sendData['company_id'] = $companyId;
				$sendData['time_send']  = date('Y-m-d H:i:s',time());
				$sendData['time_out']   = date('Y-m-d H:i:s',time()+120);
				SmsLog::create($sendData);
			}
			return ['status'=>SUCCESS,'content'=>null,'msg'=>'短信验证码发送成功！'];
		}
		Log::info('【短信发送失败】=>号码:'.$phone.'，返回结果:'.$result->msg);
		return ['status'=>FAILED,'content'=>null,'msg'=>'发送失败:'.$result->msg.'，请稍后重试'];
	}

	//验证手机验证码
	public static function checkPhoneCode($phone,$code){
		$uc_temp_phone = SmsLog::where(['phone'=>$phone,'code'=>$code,'is_active'=>'1'])->first();
		if(empty($uc_temp_phone)){
			Log::info('【手机验证码验证失败】未找到手机号对应的验证码：手机号=》'.$phone."，验证码=》".$code);
			return ['status'=>FAILED,'content'=>null,'msg'=>'手机验证码不正确，请认真核对后输入！'];
		}
		if(strtotime($uc_temp_phone->time_out)<time()){
			Log::info('【手机验证码验证失败】验证码过期：手机号=》'.$phone."，验证码=》".$code);
			return ['status'=>FAILED,'content'=>null,'msg'=>'验证码过期，请从新获取验证码！'];
		}
		return ['status'=>SUCCESS,'content'=>null,'msg'=>'验证码验证成功！'];
	}

	//获取IP地理位置
	public static function getIpInfo($ip){
		if(empty($ip)){
			$ip = self::getClientIP();
		}
  
		//通过太平洋接口
        $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.$ip;
		$result = file_get_contents($url);
		$result = json_decode($result,true);
		if($result['code']!==0 || !is_array($result['data'])){
			return false;
		}
		return $result['pro'].$result['city'];
	}

	public static function upMoney($member_id,$money,$moveType,$remarks=null,$billNo=null,$admin_id=null,$sort = 0){
		$user = Member::query()->lockForUpdate()->find($member_id);
		if(!$user){
			Log::info('【金额变动失败】会员信息不存在，memberId：'.$member_id);
			return false;
		}
		switch($moveType){
			case 'deposit':             //充值
				break;
			case 'money_draw':          //取款
				$money = $money*-1;
				break;
			case 'gift_money':          //赠送
				break;
			case 'admin_money_inc':     //管理员赠加
				break;
			case 'admin_money_dec':     //管理员减少
				$money = $money*-1;
				break;
			case 'buy_money':           //购买扣除
				$money = $money*-1;
				break;
			case 'win_money':           //中奖结算
				break;
			default:
				return false;
		}
		$money_before = $user->balance;
		if($moveType=='money_draw'){
			$m = $money*-1;
		}else{
			if($money<0&&$user->balance<abs($money)){
				$money = $user->balance*-1;
			}
			$user->increment('balance',$money);
		}
		$user = Member::find($member_id);
		$money_after = $user->balance;
		$mvTime = date('Y-m-d H:i:s');
		$sort   = MoneyLog::where('created_at',$mvTime)->count();
		$sort   = $sort + 1;
		log::info('【会员金额变动】帐号：'.$user->login_name.'，变动参数：');
		$attribute = ['admin_id'=>$admin_id,'member_id'=>$member_id,'bill_no'=>$billNo,
			'move_type'=>$moveType,'money_before'=>$money_before,'money_change'=>$money,'money_after'=>$money_after,
			'created_at'=>$mvTime,'sorts'=>$sort,'remarks'=>$remarks?:config('enums.move_type')[$moveType]];
		log::info($attribute);
		return MoneyLog::create($attribute);
	}

	public static function isAdmin(){
		return Auth('admin')->user()->is_admin===1;
	}

	public static function getMicrotime(){
		$time  = explode(" ",microtime());
		$time  = $time [1].($time [0]*1000);
		$time2 = explode(".",$time);
		$time  = $time2 [0];
		$time  = (int)$time;
		return $time;
	}

	public static function getMemberLevel(){
		$memberLevel = Level::orderBy('level_code','asc')->get(['id','level_name']);
		$arr[0] = '请选择';
		foreach($memberLevel as $item){
			$arr[$item['id']] = $item['level_name'];
		}
		return $arr;
	}

	public static function getMemberLevelName($level_id){
		$level_name = Level::where('id',$level_id)->value('level_name');
		return $level_name;
	}

	//记录操作日记
	public static function addAdminLog($admin_id,$content,$optype){
		$data = [
		    'admin_id' => $admin_id,
            'content'  => $content,
            'ip_addr'  => Helper::getClientIP(),
            'optype'   => $optype,
        ];
		AdminLog::create($data);
	}

	//记录登陆日记
	public static function recordLogLogin($member_id,$member_type,$login_name,$login_ip,$login_result){
		$data = [];
		$data['member_id']    = $member_id;
		$data['member_type']  = $member_type;
		$data['login_name']   = $login_name;
		$data['login_ip']     = $login_ip;
		$data['login_area']   = '中国';//Helper::getIpInfo($login_ip);
		$data['login_result'] = $login_result;
		LoginLog::create($data);
	}

	public static function checkIpLoginFailed($ipAddr,$domain){
		$Iplog  = IpLog::where(['ip_addr'=>$ipAddr,'domain'=>$domain])->first();
		$fcount = config('auth.LOGIN_FAILED_COUNT',5);
		if($Iplog){
			if($Iplog->failed_count >= $fcount){
				return ['result'=>false,'content'=>'','message'=>'登录失败，该IP错误次数超过'.$fcount.'次，已被锁定!'];
			}
		}else{
			$data = ['ip_addr'=>$ipAddr,'failed_count'=>1,'domain'=>$domain];
			IpLog::create($data);
		}
		return ['result'=>true,'content'=>'','message'=>''];
	}

	public static function updateIpLoginFailed($ipAddr){
		$Iplog = IpLog::where(['ip_addr'=>$ipAddr])->first();
		if($Iplog){
			$Iplog->increment('failed_count',1);
		}
		$count = $Iplog->failed_count;
		return $count;
	}

	public static function updateIpLoginSuccess($ipAddr){
		$Iplog = IpLog::where(['ip_addr'=>$ipAddr])->first();
		if($Iplog){
			$Iplog->update(['failed_count'=>0]);
		}
	}

	public static function updateIpRegisterCountInc($ipAddr,$domain){
		$Iplog = IpLog::where(['ip_addr'=>$ipAddr,'domain'=>$domain])->first();
		if($Iplog){
			$Iplog->increment('register_count',1);
		}
	}

	public static function getIpBlackWhiteHost($hostName,$ipAddr,$hostType,$blockType){
		$res = IpList::where(['host_name'=>$hostName,'ip_addr'=>$ipAddr,'host_type'=>$hostType,'block_type'=>$blockType,'is_active'=>'1'])->first();
		return $res;
	}

	//保存操作备注
	public static function saveAdminCommit(array $attributes){
		if(empty($attributes['commit'])) return false;
        $data['member_id']   = $attributes['member_id'];
		$data['commits']     = $attributes['commit'];
		$data['commit_type'] = $attributes['commit_type']??null;
		$data['admin_id']    = Auth::guard('admin')->user()->id??'computer';
		try{
			AdminCommit::create($data);
			return true;
		}catch(\Exception $e){
			Log::info('保存操作备注】失败=>'.$e->getMessage());
			return false;
		}
	}
    
    public static function getBillNo($bCode){
        $yCode   = array('A','B','C','D','E','F','G','H','I','J','L','M','N');
        $orderSn = $bCode.$yCode[intval(date('Y'))-2011].date('mdHi').substr(microtime(),2,3).rand(10,99);
        return $orderSn;
    }

	//会员金额变动记录
	public static function getMoneyLog($limit=null,$param,$totalColumns=[],$columns=['*']){
		$builder = MoneyLog::with('admin')->where(function($query)use($param){
			if(array_key_exists('startDate',$param)&&!empty($param['startDate'])){
				$query->where('created_at','>=',$param['startDate']);
			}
			if(array_key_exists('endDate',$param)&&!empty($param['endDate'])){
				$query->where('created_at','<=',$param['endDate']);
			}
			if(array_key_exists('member_id',$param)&&!empty($param['member_id'])){
				$query->whereIn('member_id',$param['member_id']);
			}
			if(array_key_exists('billNo',$param)&&!empty($param['billNo'])){
				$query->where('bill_no','like','%'.$param['billNo'].'%');
			}
			if(array_key_exists('move_type',$param)&&$param['move_type'][0]!=''){
				$query->where(function($query) use ($param){
					foreach($param['move_type'] as $var){
						$query->orWhere('move_type','=',$var);
					}
				});
			}
		})->orderBy('created_at','desc')->orderBy('sorts','desc');
		$res = [];
		if(!empty($totalColumns)){
			foreach($totalColumns as $totalColumn){
				$res[$totalColumn] = $builder->sum($totalColumn);
			}
		}
		$res['paginate'] = $builder->paginate($limit,$columns);
		return $res;
	}
}
