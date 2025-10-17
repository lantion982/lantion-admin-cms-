<?php
    //会员管理
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Libs\UserHelper;
    use App\Models\AdminCommit;
    use App\Models\LoginLog;
    use App\Models\Member;
    use App\Models\Level;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\View;
    
    class MemberController extends Controller{
        protected $adminId;
        
        public function __construct(){
            parent::__construct();
            $this->adminId = Auth('admin')->user()->id;
        }
        
        public function memberAccount(Request $request){
            $data = $request->except(['_token','page']);
            $page_count = $request->input("page_count",20);
            $page = $request->input("page",1);
            $page_title['type'] = 'Search';
            $page_title['content'] = 'manager.member.search';
            $page_title['title'] = '会员列表';
            
            $startDate = $data['startDate']??'';
            $endDate = $data['endDate']??'';
            
            $results = UserHelper::memberAccount($data,$page_count);
            $members = $results['paginate'];
            unset($results['paginate']);
            $level = Level::orderBy('level_code')->get(['id','level_name']);
            if($request->ajax()){
                return View::make('manager.member.memberAjax')
                    ->with([
                        'members' => $members,'results' => $results,'page_title' => $page_title,
                        'page' => $page,'page_count' => $page_count,'startDate' => $startDate,'endDate' => $endDate,
                    ])->render();
            }else{
                return view('manager.member.member',compact('members','results','page_title','level','page','page_count','startDate','endDate'));
            }
        }
        
        public function addMemberAccount(Request $request){
            $data['member'] = [];
            return View('manager.member.memberAdd',$data);
        }
        
        public function createMemberAccount(Request $request){
            $login_name = $request->input('login_name','');
            $count = Member::where('login_name',$login_name)->count();
            $data  = $request->except('_token');
            if($data['login_name']==''){
                return response()->json(['status' => FAILED,'msg' => '请输入会员帐号！']);
            }
            if($data['login_pwd']==''){
                return response()->json(['status' => FAILED,'msg' => '请输入登录密码！']);
            }
            if($data['phone']==''){
                return response()->json(['status' => FAILED,'msg' => '请输入手机号码！']);
            }
            if($count>0){
                return response()->json(['status' => FAILED,'msg' => '输入的会员帐号已经存在！']);
            }
            try{
                DB::transaction(function() use ($request){
                    $data  = $request->except('_token');
                    if($data['login_pwd']){
                        $data['login_pwd'] = bcrypt($data['login_pwd']);
                    }
                    $levels = Level::orderBy('level_code','ASC')->first();
                    $data['level_id'] = $levels->level_id;
                    $result = Member::create($data);
                    Helper::addAdminLog($this->adminId,'【新增帐号信息】帐号=》'.$data['login_name'],'update');
                });
                return response()->json(['status' => SUCCESS,'msg' => '帐号新增成功！']);
            }catch(\Exception $e){
                Log::info($e->getMessage());
                return response()->json(['status' => FAILED,'msg' => '帐号新增失败！']);
            }
        }
        
        public function memberAccountInfo(Request $request){
            $k = 0;
            $id = intval($request->input('id',0));
            $member = Member::where('id',$id)->first();
            $levels = Helper::getMemberLevel();
            $commits = AdminCommit::where('member_id',$id)->whereIn('commit_type',['member_status','member_event','member_remark'])->orderBy('created_at','desc')->limit(8)->get();
            $member->phone2 = $member->phone;
            return View('manager.member.memberInfo',compact('member','levels','commits'));
        }
        
        public function updateMemberAccountInfo(Request $request){
            $id = intval($request->input('id',0));
            $members = Member::where('id',$id)->first();
            if(empty($members)){
                return response()->json(['status' => FAILED,'msg' => '该会员信息未找到！']);
            }
            $data = $request->all();
            $remark = "【更新会员信息】=》帐号：".$members->login_name.'，更新内容=>'.implode(",",$data);
            if(array_key_exists('phone2',$data)&&!strpos($data['phone2'],'*')){
                $data['phone'] = $data['phone2'];
            }
            if(!empty($data['login_pwd'])){
                $data['login_pwd'] = bcrypt($data['login_pwd']);
            }
            if(array_key_exists('is_allow',$data)){
                if($members->is_allow<>$data['is_allow']){
                    if($data['is_allow']==1){
                        $data['failed_count'] = 0;  //登录失败重置归零
                    }
                }
            }
            $data = array_filter($data,function($v){
                if($v===''||$v===null){
                    return false;
                }else{
                    return true;
                }
            });
            
            $data['member_pic_doc'] = $request->get('member_pic_doc');
            $result = $members->update($data);
            Helper::addAdminLog($this->adminId,$remark,'update');
            return response()->json($result?['status' => SUCCESS,'msg' => '更新成功！']:[
                'status' => FAILED,'msg' => '更新失败!',
            ]);
        }
        
        public function addMemberRemark(Request $request){
            $data = $request->except('_token');
            if(empty($data['commit'])){
                return response()->json(['status' => FAILED,'msg' => '请填写事件内容！']);
            }
            $data['commit_type'] = 'member_event';
            Helper::saveAdminCommit($data);
            Helper::addAdminLog($this->adminId,'添加会员事件=》'.$data['commit'],'insert');
            return response()->json(['status' => SUCCESS,'msg' => '添加成功']);
        }
        
        public function deleteMemberRemark(Request $request){
            $id = intval($request->input('id',0));
            try{
                AdminCommit::where('id',$id)->delete();
            }catch(\Exception $e){
                return response()->json(['status' => FAILED,'msg' => '会员事件删除失败！']);
            }
            Helper::addAdminLog($this->adminId,'【删除会员事件】ID=》'.$id,'delete');
            return response()->json(['status' => SUCCESS,'msg' => '会员事件删除成功！']);
        }
        
        public function getMemberLoginLog(Request $request){
            $data = $request->except(['_token','page']);
            $startDate = $data['startDate']??'';
            $endDate = $data['endDate']??'';
            $keyword = $data['keyword']??'';
            $page = $request->input('page',0);
            $member_id = $request->input('id');
            $page_count = $request->input('page_count',10);
            $logReg = Member::where('id',$member_id)->first();
            $loginLogs = LoginLog::with('member')->where('member_id',$member_id)
                ->where('member_type',Member::class)
                ->where(function($query) use ($data){
                    if(array_key_exists('startDate',$data)&&$data['startDate']!=''){
                        $query->where('created_at','>=',$data['startDate']);
                    }
                    if(array_key_exists('endDate',$data)&&$data['endDate']!=''){
                        $query->where('created_at','<=',$data['endDate']);
                    }
                    if(array_key_exists('keyword',$data)&&trim($data['keyword'])!=''){
                        $query->where('login_ip',trim($data['keyword']));
                    }
                })->orderBy('created_at','desc')->paginate($page_count);
            $loginLogs->appends(['member_id' => $member_id])->render();
            $loginLogs->withPath('/'.$request->route()->uri);
            return View('manager.member.loginLogs',compact('loginLogs','logReg','startDate','endDate','member_id','keyword'));
        }
        
        //会员登录IP 记录
        public function getLoginIpList(Request $request){
            $data = $request->except(['_token','page']);
            $startDate = $data['startDate']??'';
            $endDate = $data['endDate']??'';
            $keyword = $data['keyword']??'';
            $ip = $data['ip']??'';
            $page = $request->input('page',0);
            $page_count = $request->input('page_count',50);
            $memberId = Member::where(function($query) use ($data){
                if(array_key_exists('keyword',$data)&&trim($data['keyword'])!=''){
                    $query->orWhere('login_name','like','%'.trim($data['keyword']).'%');
                    $query->orWhere('nick_name','like','%'.trim($data['keyword']).'%');
                }
            })->pluck('id')->toArray();
            
            $loginLogs = LoginLog::where('member_type',Member::class)
                ->where(function($query) use ($memberId){
                    if(!empty($memberId)){
                        $query->whereIn('member_id',$memberId);
                    }
                })->where(function($query) use ($data){
                    if(array_key_exists('startDate',$data)&&$data['startDate']!=''){
                        $query->where('created_at','>=',$data['startDate']);
                    }
                    if(array_key_exists('endDate',$data)&&$data['endDate']!=''){
                        $query->where('created_at','<=',$data['endDate']);
                    }
                    if(array_key_exists('ip',$data)&&trim($data['ip'])!=''){
                        $query->where('login_ip','like','%'.trim($data['ip'].'%'));
                    }
                })->orderBy('created_at','desc')->paginate($page_count);
            
            $loginLogs->appends(['keyword' => $keyword,'startDate' => $startDate,'endDate' => $endDate])->render();
            $loginLogs->withPath('/'.$request->route()->uri);
            return View('manager.member.loginIpList',compact('loginLogs','startDate','endDate','keyword','ip','page_count','page'));
        }
        
        //会员等级
        public function memberLevel(Request $request){
            $page_title['type']    = 'title';
            $page_title['content'] = '';
            $data = $request->except(['_token','page']);
            $page = $request->input('page',1);
            $page_count = $request->input("page_count",20);
            $levels = Level::orderBy('level_code','ASC')->paginate($page_count);
            if($request->ajax()){
                return View('manager.member.levelAjax',compact('levels','page_title','page','page_count','page_title'));
            }else{
                return view('manager.member.level',compact('levels','page_title','page','page_count','page_title'));
            }
        }
        
        //新增等级
        public function addMemberLevel(Request $request){
            $level = null;
            return View('manager.member.levelInfo',compact('level'));
        }
        
        //创建用户等级
        public function createMemberLevel(Request $request){
            $data       = $request->except('_token');
            $gift_money = $request->input('gift_money',0);
            $data['gift_money'] = $gift_money;
            if(empty($data['level_code'])){
                return response()->json(['status' => FAILED,'msg' => '请选择等级编号！']);
            }
            if(empty($data['level_name'])){
                return response()->json(['status' => FAILED,'msg' => '请输入等级名称！']);
            }
            $hasLevel = Level::where('level_code',$request['level_code'])->first();
            if($hasLevel){
                return response()->json(['status' => FAILED,'msg' => '该会员等级已存在！']);
            }
            try{
                $result = Level::create($request->all());
                if($result){
                    $msg = '【新增会员等级】ID=》'.$result->level_code.'，名称=》'.$result->level_name;
                    Helper::addAdminLog($this->adminId,$msg,'insert');
                    return response()->json(['status' => SUCCESS,'msg' => '新增会员等级成功！']);
                }
                return response()->json(['status' => FAILED,'msg' => '新增会员等级失败！']);
            }catch(Exception $e){
                Log::info('【新增会员等失败】=》'.$e->getMessage());
                return response()->json(['status' => FAILED,'msg' => '新增会员等级失败！']);
            }
        }
        
        //查看用户等级的详情
        public function memberLevelInfo(Request $request){
            $id    = intval($request->input('id',0));
            $level = Level::where('id',$id)->first();
            return View::make('manager.member.levelInfo',compact('level'));
        }
        
        //修改用户等级信息
        public function updateMemberLevelInfo(Request $request){
            $id    = intval($request->input('id',0));
            $data  = $request->except('_token');
            $level = Level::where('id',$id)->first();
            if(empty($level)){
                return response()->json(['status' => FAILED,'msg' => '会员等级未找到！','url' => '/manager/gameRoom']);
            }
            $result = Level::where('id',$id)->update($data);
            Helper::addAdminLog($this->adminId,'【更新会员等级】ID=》'.$id,'update');
            return response()->json($result?['status' => SUCCESS,'msg' => '会员等级更新成功！']:['status' => FAILED,'msg' => '会员等级更新失败！',]);
        }
        
        public function deleteMemberLevel(Request $request){
            $id    = intval($request->input('id',0));
            try{
                Level::where('id',$id)->delete();
                Helper::addAdminLog($this->adminId,'【删除会员等级】id=》'.$id,'delete');
            }catch(\Exception $e){
                Log::info($e->getMessage());
                return response()->json(['status' => FAILED,'msg' => '会员等级删除失败！']);
            }
            return response()->json(['status' => SUCCESS,'msg' => '会员等级删除成功！']);
        }
    }
