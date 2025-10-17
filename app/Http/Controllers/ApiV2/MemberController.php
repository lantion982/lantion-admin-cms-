<?php
/*
|--------------------------------------------------------------------------
| 用户相关API
|--------------------------------------------------------------------------
*/
namespace App\Http\Controllers\ApiV2;

use App\Libs\Helper;
use App\Libs\UserHelper;
use App\Models\LoginLog;
use App\Models\Member;
use App\Models\Message;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends BaseController{
	protected $memberId;
    protected $member;
	
	public function __construct(){
		$this->middleware(function($request,$next){
			$this->member = $request->user();
			$this->memberId = $this->member->member_id;
			if(!empty($this->member)){
				return $next($request);
			}else{
				return response()->json(['status'=>'-1','msg'=>'无法获取您的账户信息，请重新登录!']);
			}
		});
	}
	
	//刷新TOKEN 记录登录信息
	public function addLogin(Request $request){
		$member = $this->member;
		$ipAddr = Helper::getClientIP();
		Helper::recordLogLogin($member->member_id,'App\Models\Member',$member->login_name,$ipAddr,'success');
		Helper::updateIpLoginSuccess($ipAddr);													            //更新ip登录统计，清零
        UserHelper::memberLoginSuccess($member->member_id,$ipAddr);
		return response()->json(['status'=>SUCCESS,'content'=>'','msg'=>'success']);
	}
	
	//1.获得用户信息
	public function getUserInfo(Request $request){
		$gameId    = intval($request->input('gameId',2));
		$member    = Member::where('member_id',$this->memberId)->first();
		$lastLogin = LoginLog::where(['member_admin_id'=>$member->member_id,'login_result'=>'success'])->orderBy('created_at','desc')->first();
		$panArry   = explode(',',$member->pan_id);
		$data['ispa'] = false;
		$data['ispb'] = false;
		$data['ispc'] = false;
		$data['ispd'] = false;
		foreach($panArry as $var){
			switch($var){
				case 1:
					$data['ispa'] = true;
					break;
				case 2:
					$data['ispb'] = true;
					break;
				case 3:
					$data['ispc'] = true;
					break;
				case 4:
					$data['ispd'] = true;
					break;
			}
		}
		$data['realName'] = $member->real_name?:'';
		$data['userName'] = $member->login_name;
		$data['money']  = $member->balance;
		$data['level']  = $member->memberLevel->member_level_name??'';
		$data['phone']  = empty($member->phone)?'':$member->phone;
		$data['weixin'] = $member->wechat;
		$data['qq']     = $member->qq;
		$data['email']  = $member->email;
		$data['birthday']  = $member->birthday;
		$data['infoWater'] = $member->info_water;
		$data['lastLogin'] = isset($lastLogin)?$lastLogin->record_time:'';
		$data['LastIp']    = isset($lastLogin)?$lastLogin->login_ip:'';
		$data['isLogin']   = empty($member->is_allow)?'0':$member->is_allow;
		$data['creditMoney'] = $member->credit_money;
		
		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'success']);
	}
	
	//2.修改用户信息
	public function putUserInfo(Request $request){
		$realName = strip_tags($request->input('realName',''));
		$weixin   = strip_tags($request->input('weixin',''));
		$qq       = strip_tags($request->input('qq',''));
		$email    = strip_tags($request->input('email',''));
		$birthday   = strip_tags($request->input('birthday',''));
		$other_type = strip_tags($request->input('other_type',''));
		$other_vue  = strip_tags($request->input('other_vue',''));
		$data = array();
		//if($realName!='' && empty($this->member->real_name)) $data['real_name'] = $realName;
		if($realName!=''&& empty($this->member->real_name)) $data['real_name'] = $realName;
		if($qq!='' && empty($this->member->qq)) $data['qq'] = $qq;
		if($weixin!='' && empty($this->member->wechat)) $data['wechat'] = $weixin;
		if($email!='' && empty($this->member->email)) $data['email'] = $email;
		if($birthday!=''&& empty($this->member->birthday)) $data['birthday'] = $birthday;
		$data['other_type'] = $other_type;
		$data['other_vue']  = $other_vue;
		//检查是否存在没有设置的内容
		if($realName==''&& empty($this->member->real_name)) {
			return response()->json(['status'=>1001,'content'=>'','msg'=>'请填写您的真实姓名！']);
		}
		if($qq==''&& $weixin==''&&$email==''){
			return response()->json(['status'=>1002,'content'=>'','msg'=>'QQ号/微信号/邮箱至少要填写一项！']);
		}
		if(isset($data['email'])){
            if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$data['email'])) {
                return response()->json(['status'=>1005,'content'=>'','msg'=>'邮箱格式错误，请填写正确邮箱地址！',]);
            }
        }
	    /*if(($birthday==''||$birthday=='0000-00-00') && empty($this->member->birthday)) {
			return response()->json(['status'=>1006,'content'=>'','msg'=>'请填写您的生日日期，方便我们与您一起过庆祝!',]);
		}*/
		if(empty($data)){
			return response()->json(['status'=>1007,'content'=>'','msg'=>'用户信息修改失败!']);
		}
		
		$this->member->update($data);
		$members = $this->member;
		$data = [];
		$data['realName'] = $members->real_name;
		$data['weixin'] = empty($members->wechat)?'点击绑定':mb_substr($members->wechat,0,3).'****'.mb_substr($members->wechat,-3,3);
		$data['qq']     = empty($members->qq)?'点击绑定':mb_substr($members->qq,0,3).'****'.mb_substr($members->qq,-3,3);
		$data['email']  = empty($members->email)?'点击绑定':mb_substr($members->email,0,3).'****'.mb_substr($members->email,-6,6);
		$data['birthday'] = $members->birthday;
		
		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'用户信息修改成功!']);
	}
	
	//4.修改登录密码
	public function putUserPassword(Request $request){
		$password    = $request->input('password','');
		$newPassword = $request->input('newPassword','');
		$validator = Validator::make($request->all(),[
			'password'=>'required',
			'newPassword'=>'required|min:6|max:12',
			'newPassword_confirm'=>'required',
		],[
			'password.required'=>'请输入原登录密码!',
			'newPassword.required'=>'新登录密码不能为空!',
			'newPassword.min'=>'登录密码至少6位字符!',
			'newPassword.max'=>'登录密码不能超过12位字符!',
			'newPassword_confirm.required'=>'确认密码不能为空!',
		]);
		if($validator->fails()){
			return response()->json(['status'=>1001,'msg'=>$validator->errors()->first()]);
		}
		$member = $this->member;
		if(!\Hash::check($password,$member->login_pwd)){
			return response()->json(['status'=>1002,'msg'=>'原密码输入不正确!']);
		}
		if(\Hash::check($newPassword,$member->login_pwd)){
			return response()->json(['status'=>1003,'msg'=>'新密码和原密码相同，请输入不同的新密码!']);
		}
		$member->login_pwd = bcrypt($newPassword);
		$res = $member->save();
		
		if($res) {
			return response()->json(['status'=>SUCCESS,'msg'=>'登录密码修改成功!']);
		}
		return response()->json(['status'=>1004,'msg'=>'密码修改失败!']);
	}
	
	//5.修改交易密码
	public function putTradePwd(Request $request){
		$password    = $request->input('password','');
		$phoneCode   = strip_tags($request->input('phoneCode',''));
		$validator   = Validator::make($request->all(),[
			'password'=>'required|min:6|max:12|confirmed',
			'password_confirmation'=>'required',
			/*'verifyCode'=>'required',*/
			'phoneCode'=>'required'
		],[
			'password.required'=>'交易密码不能为空!',
			'password.min'=>'交易密码不能小于6位字符!',
			'password.max'=>'交易密码不能超过12位字符!',
			'password.confirmed'=>'确认密码输入不一致!',
			'password_confirmation.required'=>'确认密码不能为空!',
			/*'verifyCode.required'=>'验证码不能为空!',*/
			'phoneCode.required'=>'手机验证码不能为空!',
		]);
		
		if($validator->fails()){
			return response()->json(['status'=>1001,'msg'=>$validator->errors()->first()]);
		}
		
		
		$member = $this->member;
		if($member->phone==''||empty($member->phone)){
			return response()->json(['status'=>1002,'msg'=>'还未绑定手机号，请先绑定手机号!']);
		}
		
		$ret = Helper::checkPhoneCode($member->phone,$phoneCode);
		if($ret['status']==FAILED){
			return response()->json(['status'=>1003,'msg'=>$ret['msg']]);
		}
		
		$member->trade_pwd = bcrypt($password);
		$res = $member->save();
		if($res) {
			return response()->json(['status'=>SUCCESS,'msg'=>'交易密码修改成功!']);
		}
		
		return response()->json(['status'=>1004,'msg'=>'交易密码修改失败!']);
	}
	
	//6.获取站内信息
	public function getMessage(Request $request){
		$pid = intval($request->input('pid'),0);
		$member_id  = $this->memberId;
		$company_id = $this->member->company_id;
		$list = Message::where('member_agent_type','App\Models\Member')->where(function($query) use ($member_id,$company_id){
			$query->where("member_agent_id",$member_id);
			$query->orWhere(function($query) use ($company_id){
				$query->where("member_agent_id",'0')->where(function($query) use ($company_id){
					$query->where("company_id",'like','%'.$company_id.'%');
					$query->orWhere("company_id",'all');
				});
			});
		})->where(['message_pid' => $pid])
            ->select([
                'message_id','message_pid','message_body',
                'from_uid','from_username','created_at',
                'message_read','readuid'
            ])->orderBy('created_at','desc')->get();
		if($pid==1){
			$unread = Message::where('member_agent_type','App\Models\Member')->where(function($query) use ($member_id,$company_id){
				$query->where("member_agent_id",$member_id);
				$query->orWhere(function($query) use ($company_id){
					$query->where("member_agent_id",'0')->where(function($query) use ($company_id){
						$query->where("company_id",'like','%'.$company_id.'%');
						$query->orWhere("company_id",'all');
					});
				});
			})->where(['message_read' => 0,'message_pid' => $pid])->count();
			$syscount = Message::where('member_agent_type','App\Models\Member')->where(function($query) use ($member_id,$company_id){
				$query->where("member_agent_id",$member_id);
				$query->orWhere(function($query) use ($company_id){
					$query->where("member_agent_id",'0')->where(function($query) use ($company_id){
						$query->where("company_id",'like','%'.$company_id.'%');
						$query->orWhere("company_id",'all');
					});
				});
			})->where(['message_pid' => 0])->count();
			$unread2 = Message::where('member_agent_type','App\Models\Member')->where(function($query) use ($member_id,$company_id){
				$query->where("member_agent_id",$member_id);
				$query->orWhere(function($query) use ($company_id){
					$query->where("member_agent_id",'0')->where(function($query) use ($company_id){
						$query->where("company_id",'like','%'.$company_id.'%');
						$query->orWhere("company_id",'all');
					});
				});
			})->where('readuid','like','%'.$member_id.'%')->where(['message_pid' => 0])->count();
			$unread2 = $syscount-$unread2;
			$allunread = $unread+$unread2;
			
		}else{
			$unread = Message::where('member_agent_type','App\Models\Member')->where(function($query) use($member_id,$company_id){
				$query->where("member_agent_id",$member_id);
				$query->orWhere(function($query) use($company_id){
					$query->where("member_agent_id",'0')->where(function($query) use($company_id){
						$query->where("company_id",'like','%'.$company_id.'%');
						$query->orWhere("company_id",'all');
					});
				});
			})->where('readuid','like','%'.$member_id.'%')->where(['message_pid'=>$pid])->count();
			$unread  = $list->count()-$unread;
			
			$unread2 = Message::where('member_agent_type','App\Models\Member')->where(function($query) use($member_id,$company_id){
				$query->where("member_agent_id",$member_id);
				$query->orWhere(function($query) use($company_id){
					$query->where("member_agent_id",'0')->where(function($query) use($company_id){
						$query->where("company_id",'like','%'.$company_id.'%');
						$query->orWhere("company_id",'all');
					});
				});
			})->where(['message_read'=>0,'message_pid'=>1])->count();
			$allunread = $unread+$unread2;
		}
		
		$data['list'] = [];
		foreach ($list as $key=>$val){
            $data['list'][$key]['id']  = $val->message_id;
            $data['list'][$key]['uid'] = $val->from_uid;
            $data['list'][$key]['username'] = $val->from_username;
            $data['list'][$key]['info'] = $val->message_body;
            $data['list'][$key]['pid']  = $val->message_pid;
            $data['list'][$key]['read'] = $val->message_read;
			if ($pid==0){
				$data['list'][$key]['read'] = 0;
				if(strpos($val->readuid,$member_id)!==false){
					$data['list'][$key]['read'] = 1;
				}
			}
			$data['list'][$key]['times'] = str_limit($val->created_at,10,'');
		}
		$data['count']  = $list->count();
		$data['unread'] = $unread;
		$data['allunread'] = $allunread;
		
		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'success']);
	}

	public function getTopMessage(Request $request){
		$member_id  = $this->memberId;
		$company_id = $this->member->company_id;
		$list = Message::where('member_agent_type','App\Models\Member')->where('message_read',0)
			->where(function($query) use ($member_id,$company_id){
			$query->where("member_agent_id",$member_id);
			$query->orWhere(function($query) use ($company_id){
				$query->where("member_agent_id",'0')->where(function($query) use ($company_id){
					$query->where("company_id",'like','%'.$company_id.'%');
					$query->orWhere("company_id",'all');
				});
			});
		})->select(['message_body'])->orderBy('created_at','desc')->first();
		$data['data'] = $list;
		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'success']);
	}

	//7.设置信息为已读
	public function readMessage(Request $request){
		$id   = strip_tags($request->input('id'),'');
		$info = Message::where('message_id',$id)->first();
		$member_id  = $this->memberId;
		if($id==''){
			return response()->json(['status'=>1001,'msg'=>'信息ID不能为空!']);
		}
		if (!$info){
			return response()->json(['status'=>1001,'msg'=>'该信息不存在或已经被删除!']);
		}
		$pid = $info->message_pid;
		$readuid = $info->readuid;
		if ($pid==1) {
			$res = Message::where('message_id',$id)->update(['message_read'=>1]);
		}else{
			$readuid = $readuid.$member_id.",";
			$res = Message::where('message_id',$id)->update(['readuid'=>$readuid]);
		}
		if (!$res) {
			return response()->json(['status'=>1002,'msg'=>'信息设置失败!']);
		}
		return response()->json(['status'=>SUCCESS,'msg'=>'信息设置成功!']);
	}
	
	//8.删除站内信息-仅个人信息可删除
	public function delMessage(Request $request){
		$id  = strip_tags($request->input('id'),'');
		if($id==''){
			return response()->json(['status'=>1001,'msg'=>'信息ID未传入!']);
		}
		
		$res = Message::where(['message_id'=>$id,'message_pid'=>1])->update(['deleted_at'=>now()]);
		
		if (!$res) {
			return response()->json(['status'=>1002,'msg'=>'信息删除失败!']);
		}
		return response()->json(['status'=>SUCCESS,'msg'=>'信息删除成功!']);
	}
	
	//10.检查手机系统类型
	/*public function get_device_type(){
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$type  = 'Phone-other';
		if(strpos($agent,'iphone')||strpos($agent,'ipad')){
			$type = 'Phone-ios';
		}
		if(strpos($agent,'android')){
			$type = 'Phone-android';
		}
		return $type;
	}

	//3.修改用户信息--单项
	public function putSingleInfo(Request $request){
		$info  = strip_tags($request->input('info',''));
		$ptype = intval($request->input('ptype',0));

		if($ptype<=0) {
			return response()->json(['status'=>1001,'content'=>'','msg'=>"请指定要更新字段类型!",]);
		}

		switch ($ptype){
			case 1:
				$title = '姓名';
				$filenmae = 'real_name';
				break;
			case 2:
				$title = '微信';
				$filenmae = 'wechat';
				break;
			case 3:
				$title = 'QQ号码';
				$filenmae = 'qq';
				break;
			case 4:
				$title = '邮箱';
				$filenmae = 'email';
				break;
			case 5:
				$title = '生日';
				$filenmae = 'birthday';
				break;
			default:
				$title = '姓名';
				$filenmae = 'real_name';
				break;
		}

		if($info=='') {
			return response()->json(['status'=>1002,'content'=>'','msg'=>"请输入您的".$title."!",]);
		}

		if($ptype==4){
			if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$info)) {
				return response()->json(['status'=>1003,'content'=>'','msg'=>'邮箱格式错误，请正确填写您的邮箱!',]);
			}
		}

		$members = $this->member;
		$data = [];
		$data['realName'] = $members->real_name;
		$data['weixin'] = empty($members->wechat)?'点击绑定':mb_substr($members->wechat,0,3).'****'.mb_substr($members->wechat,-3,3);
		$data['qq']     = empty($members->qq)?'点击绑定':mb_substr($members->qq,0,3).'****'.mb_substr($members->qq,-3,3);
		$data['email']  = empty($members->email)?'点击绑定':mb_substr($members->email,0,3).'****'.mb_substr($members->email,-6,6);
		$data['birthday'] = $members->birthday;

		if(!empty($this->member->$filenmae&&$ptype!=5)) {
			return response()->json(['status'=>1004,'content'=>$data,'msg'=>$title."已定绑定，不允许自行修改!",]);
		}
		$_data[$filenmae] = $info;
		$this->member->update($_data);
		$members = $this->member;
		$data = [];
		$data['realName'] = $members->real_name;
		$data['weixin'] = empty($members->wechat)?'点击绑定':mb_substr($members->wechat,0,3).'****'.mb_substr($members->wechat,-3,3);
		$data['qq']     = empty($members->qq)?'点击绑定':mb_substr($members->qq,0,3).'****'.mb_substr($members->qq,-3,3);
		$data['email']  = empty($members->email)?'点击绑定':mb_substr($members->email,0,3).'****'.mb_substr($members->email,-6,6);
		$data['birthday'] = $members->birthday;

		return response()->json(['status'=>SUCCESS,'content'=>$data,'msg'=>'信息修改成功!',]);

	}*/

	//11.发送发送验证码
	public function getPoneCode(Request $request){
		
		//图片验证码验证
		if ($this->verifyCodeCheck($request)) {
			return response()->json(['status' => 1001, 'msg' => '验证码输入错误!']);
		}
		//发送验证吗
        if (!$this->member->phone) {
            return response()->json(['status' => 1002, 'msg' => '您还未绑定手机号，请先绑定手机号!']);
        }
		$res = Helper::sendPhoneCode($this->member->phone,$this->member->company->name,$this->member->company_id);
		return response()->json($res);
	}
	
	//12.验证码验证
	public function verifyCodeCheck($request){
        $verifyCode = strtolower($request['verifyCode']);
        $verifyKey  = $request['verifyKey'];
        return !\Captcha::check_api($verifyCode,$verifyKey);
	}

}
