<?php
//登录，操作日志管理
namespace App\Http\Controllers\Manager;

use App\Libs\Helper;
use App\Models\Admin;
use App\Models\IpList;
use App\Models\IpLog;
use App\Models\LoginLog;
use App\Models\AdminLog;
use App\Models\SmsLog;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class LogController extends Controller{
    protected $adminId;

    public function __construct(){
        parent::__construct();
        $this->middleware(function($request,$next){
            $this->adminId = Auth::guard('admin')->user()->id;
            return $next($request);
        });
    }
    
	//后台操作日志
	public function logOperation(Request $request){
		$page_title['type']    = 'Search';
		$page_title['content'] = 'manager.log.operateSearch';
		$data = $request->except(['_token','page']);
		$page_count = $request->input("page_count",20);
		$page = $request->input("page",1);;
		if(!isset($data['startDate'])){
			$data['startDate'] = date('Y-01-01 00:00:00');
		}
		if(!isset($data['endDate'])){
			$data['endDate'] = date('Y-m-d 23:59:59');
		}
		
		$logOperations = AdminLog::where(function($query) use ($data){
			if(array_key_exists('startDate',$data) && trim($data['startDate']) != ''){
				$query->where('created_at','>=',$data['startDate']);
			}
			
			if(array_key_exists('endDate',$data) && trim($data['endDate']) != ''){
				$query->where('created_at','<=',$data['endDate']);
			}
			if(array_key_exists('keyword',$data) && trim($data['keyword']) != ''){
				$query->where('content','like','%' . trim($data['keyword']) . '%');
			}
		})->orderBy('id','DESC')->paginate($page_count);
		
		if($request->ajax()){
			return view('manager.log.operateList',compact('logOperations','page_title','page','page_count'));;
		}else{
			return View('manager.log.operate',compact('logOperations','page_title','page','page_count'));
		}
	}
	
    //会员/代理登录日志
    public function logLoginMember(Request $request){
        $request['userType'] = 'App\Models\Member';
        $data = $request->except('_token','page');
        return $this->logLogin($request);
    }
    
    //管理员登录日志
    public function logLoginAdmin(Request $request){
        $request['userType'] = 'App\Models\Admin';
        $data = $request->except('_token','page');
        return $this->logLogin($request);
    }
    
	//登录日志查询
	public function logLogin(Request $request){
		$page_title['type']    = 'Search';
		$page_title['content'] = 'manager.log.loginSearch';
		$userType   = $request->input("userType",'App\Models\Member');
		$page_count = intval($request->input('page_count',20));
        $data = $request->except(['_token','page']);
		$page = intval($request->input("page",1));
		
		if(array_key_exists('keyword',$data)){
			if($userType == 'App\Models\Member'){
				$memberIds = Member::where(function($query) use ($data){
                    $query->where(function($query) use ($data){
                        $query->orWhere('login_name','like','%' . trim($data['keyword']) . '%');
                        $query->orWhere('phone','like','%' . trim($data['keyword']) . '%');
                        $query->orWhere('nick_name','like','%' . trim($data['keyword']) . '%');
                        $query->orWhere('register_ip','like','%' . trim($data['keyword']) . '%');
                    });
				})->pluck('id')->toArray();
			}else{
				$memberIds = Admin::where(function($query) use ($data){
					$query->where(function($query) use ($data){
						$query->orWhere('login_name','like','%' . trim($data['keyword']) . '%');
						$query->orWhere('phone','like','%' . trim($data['keyword']) . '%');
					});
				})->pluck('id')->toArray();
			}
			
			$data['userIds'] = $memberIds;
		}
        $logLogins = LoginLog::where(function($query) use ($data){
			if(array_key_exists('userType',$data) && $data['userType'] != ''){
				$query->where('member_type',$data['userType']);
			}
			if(array_key_exists('userIds',$data) && $data['userIds'] != ''){
				$query->whereIn('member_id',$data['userIds']);
			}
			if(array_key_exists('startDate',$data) && $data['startDate'] != ''){
				$query->where('created_at','>=',$data['startDate']);
			}
			if(array_key_exists('endDate',$data) && $data['endDate'] != ''){
				$query->where('created_at','<=',$data['endDate']);
			}
		})->orderBy('created_at','DESC')->paginate($page_count);
		
		if($request->ajax()){
            return view('manager.log.loginList',compact('userType','logLogins','userType','page_title','page','page_count'));
		}else{
			return view('manager.log.login',compact('userType','logLogins','userType','page_title','page','page_count'));
		}
	}
	
	//登录IP记录
	public function ipRegisterLogin(Request $request){
		$data = $request->except(['_token','page']);
		$page_count = intval($request->input('page_count',20));
        $page_title['type']    = 'Title';
        $page_title['content'] = '系统设置-IP记录';
        $page   = intval($request->input('page',1));;
		$ipLogs = IpLog::orderBy('id','ASC')->paginate($page_count);
		
		foreach($ipLogs as $var){
			$ipHost = IpList::where(['ip_addr' => $var->ip_addr,'host_name' => $var->domain])->first();
			$var->blackId = $ipHost ? $ipHost->id : '';
		}
		if($request->ajax()){
            return View('manager.log.IpLogList',compact('ipLogs','page_title','page','page_count'));
		}else{
			return View('manager.log.IpLog',compact('ipLogs','page_title','page','page_count'));
		}
	}
	
	//重置IP
	public function ipReset(Request $request){
		$id    = intval($request->input('id',0));
		$ipLog = IpLog::where('id',$id)->first();
		
		if(empty($ipLog)){
			return response()->json(['status' => FAILED,'msg' => '该IP登录记录未找到!','url' => '/manager/memberAccount']);
		}
		$res = IpLog::where('id',$id)->update(['register_count' => 0,'failed_count' => 0]);
		if($res){
			return response()->json(['status' => SUCCESS,'msg' => '重置成功！']);
		}else{
			return response()->json(['status' => FAILED,'msg' => '重置失败！']);
		}
	}
	
	//添加移除黑名单
	public function addIpBlack(Request $request){
        $id    = intval($request->input('id',0));
        $ipLog = IpLog::where('id',$id)->first();
		if(empty($ipLog)){
			return response()->json(['status' => FAILED,'msg' => '该IP登录记录未找到！']);
		}
		$ipHost = IpList::where(['ip_addr' => $ipLog->ip_addr,'host_name' => $ipLog->domain])->first();
		if($ipHost){
			IpList::destroy($ipHost->id);
            Helper::addAdminLog($this->adminId,'【移除黑名单】IP=》'.$ipLog->ip_addr,'delete');
			return response()->json(['status' => SUCCESS,'msg' => '移除黑名单成功！']);
		}
		$data = [
			'host_type'  => 'web',
			'is_active'  => 1,
			'block_type' => 'black',
			'ip_addr'    => $ipLog->ip_addr,
			'host_name'  => $ipLog->domain
		];
		$result = IpList::create($data);
        Helper::addAdminLog($this->adminId,'【添加黑名单】IP=》'.$ipLog->ip_addr,'add');
		return response()->json($result ? ['status' => SUCCESS,'msg' => '添加黑名单成功！']:[
			'status' => FAILED,'msg' => '添加黑名单失败！'
		]);
	}

    public function listSMS(Request $request){
        $page_title['type']    = 'Search';
        $page_title['content'] = 'manager.log.SMSSearch';
        $data = $request->except(['_token','page']);
        $page_count   = $request->input("page_count",20);
        $current_page = $request->input("page",1);

        $tempPhone = SmsLog::where(function($query) use ($data){
            if(array_key_exists('keyword',$data) && trim($data['keyword']) != ''){
                $query->where('phone','like','%' . trim($data['keyword']) . '%');
            }
            if(array_key_exists('startDate',$data) && trim($data['startDate']) != ''){
                $query->where('time_send','>=',$data['startDate']);
            }
            if(array_key_exists('endDate',$data) && trim($data['endDate']) != ''){
                $query->where('time_send','<=',$data['endDate']);
            }
        })->orderBy('time_send','DESC')->paginate($page_count);

        if($request->ajax()){
            return View::make('manager.log.phoneSMSList')
                ->with([
                    'tempPhone'  => $tempPhone,'page_title' => $page_title,'current_page' => $current_page,
                    'page_count' => $page_count
                ])->render();
        }else{
            return view('manager.log.phoneSMS',compact('tempPhone','page_title','current_page','page_count'));
        }
    }

    //添加短信
    public function addSMSInfo(){
        return View::make('manager.log.phoneSMSInfo')
            ->with(['tempPhone' => null])->render();
    }

	//创建短信
	public function createSMSInfo(Request $request){
		$data = $request->except(['_token','page','uid']);
		try{
			$res = SmsLog::create($data);
		}catch(\Exception $e){
			return response()->json(['status' => FAILED,'content' => null,'msg' => '添加失败！']);
		}
        Helper::addAdminLog($this->adminId,'【添加短信信息】','create');
		return response()->json(['status' => SUCCESS,'content' => null,'msg' => '添加成功！']);
	}

    //编辑短信
    public function editSMSInfo(Request $request){
        $id    = intval($request->input('id',0));
        $tempPhone = SmsLog::where('id',$id)->first();
        return View::make('manager.log.phoneSMSInfo')->with(['tempPhone' => $tempPhone,])->render();
    }

    //更新短信
    public function updateSMSInfo(Request $request){
        $id   = intval($request->input('id',0));
        $data = $request->except(['_token','page']);
        SmsLog::where('id',$id)->first()->update($data);
        Helper::addAdminLog($this->adminId,'【更新短信信息】Id=》'.$id,'update');
        return response()->json(['status' => SUCCESS,'content' => null,'msg' => '更新成功！']);
    }
}
