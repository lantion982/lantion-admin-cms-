<?php
    //新闻公告管理
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Models\Member;
    use App\Models\Message;
    use App\Models\News;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\View;
    
    class NewsController extends Controller{
        protected $adminId;
        
        public function __construct(){
            parent::__construct();
            $this->middleware(function($request,$next){
                $this->adminId = Auth::guard('admin')->user()->id;
                return $next($request);
            });
        }
        
        //新闻公告列表
        public function newsList(Request $request){
            $data = $request->except(['_token','page']);
            $page = $request->input("page",1);
            $page_count = $request->input("page_count",20);
            
            $news = News::where(function($query) use ($data){
                if(array_key_exists('startDate',$data)&&$data['startDate']!=''){
                    $query->where('created_at','>=',$data['startDate']);
                }
                if(array_key_exists('endDate',$data)&&$data['endDate']!=''){
                    $query->where('created_at','<=',$data['endDate']);
                }
                if(array_key_exists('keyword',$data)&&trim($data['keyword'])!=''&&!is_null(trim($data['keyword']))){
                    $query->where('title','like','%'.trim($data['keyword']).'%');
                }
                
                if(array_key_exists('is_show',$data)&&$data['is_show']!=''){
                    $query->where('is_show',$data['is_show']);
                }
            })->orderBy('id','desc')->paginate($page_count);
            
            $startDate = $data['startDate']??'';
            $endDate   = $data['endDate']??'';
            $page_title['type']    = 'Search';
            $page_title['content'] = 'manager.news.search';
            $newsType  = [];
            if($request->ajax()){
                return View('manager.news.listAjax',compact('news','page_title','page','page_count','startDate','endDate','newsType'));
            }else{
                return View('manager.news.list',compact('news','page_title','page','page_count','startDate','endDate','newsType'));
            }
        }
        
        //添加新闻公告
        public function newsAdd(Request $request){
            $news      = null;
            $pic_url   = '';
            $picConfig = [];
            return View::make('manager.news.info',compact('news','picConfig','pic_url'));
        }
        
        //创建新闻公告
        public function newsCreate(Request $request){
            $data  = $request->all();
            $sorts = $data['$sorts']??0;
            if($sorts==1){
                News::where('sorts','>=',1)->increment('sorts');
            }
            $result = News::create($data);
            if($result){
                $log = '【新增新闻公告】=》'.$data['news_title'].'，ID：'.$result->id;
                Helper::addAdminLog($this->adminId,$log,'insert');
                return response()->json(['status' => SUCCESS,'msg' => '新增成功！']);
            }
            return response()->json(['status' => FAILED,'msg' => '新增失败！']);
        }
        
        //编辑新闻
        public function newsEdit(Request $request){
            $id   = intval($request->input('id',''));
            $news = News::where('id',$id)->first();
            $pic_url    = '';
            $picConfig  = [];
            if(!empty($news->pic)){
                $picConfig[]  = ['key'=>$news->pic];
                $pic_url = '/uploads/'.$news->pic;
            }
            return View::make('manager.news.info',compact('news','picConfig','pic_url'));
        }
        
        //更新新闻信息
        public function newsUpdate(Request $request){
            $id   = intval($request->input('id',''));
            $news = News::where('id',$id)->first();
            if(!$news){
                return response()->json(['status' => FAILED,'msg' => '该资讯信息未找到或已被删除！']);
            }
            $data = $request->except('_token','id');
            $sort = $data['sorts']??0;
            if($sort==1){
                news::where('sorts','>=',1)->increment('sorts');
            }
            $result = News::where('id',$id)->update($data);
            if($result){
                $log = '【资讯更新成功】=》'.$news->title.'，ID：'.$news->id;
                Helper::addAdminLog($this->adminId,$log,'update');
                return response()->json(['status' => SUCCESS,'msg' => '资讯信息更新成功！']);
            }
            $log = '【资讯更新失败】=》'.$news->title.'，ID：'.$news->id;
            Helper::addAdminLog($this->adminId,$log,'update');
            return response()->json(['status' => FAILED,'msg' => '资讯信息更新失败！']);
        }
        
        //删除公告新闻D
        public function newsDel(Request $request){
            $id   = intval($request->input('id',''));
            try{
                News::where('id',$id)->delete();
                Helper::addAdminLog($this->adminId,'【删除新闻公告】ID=》'.$id,'delete');
                return response()->json(['status' => SUCCESS,'msg' => '删除成功!']);
            }catch(\Exception $e){
                Log::info('【新闻公告删除失败】=>'.$e->getMessage());
                return response()->json(['status' => FAILED,'msg' => '删除失败!']);
            }
        }
        
        //站内信息-列表
        public function messageList(Request $request){
            $keyword = $request->input("kewywrod","");
            $page    = $request->input("page",1);
            $list = Message::where(function($query) use ($keyword){
                if($keyword!=''){
                    $query->where('message_body','like','%'.$keyword.'%');
                }
            })->orderBy('created_at','desc')->paginate(20);
            if($request->ajax()){
                return view('manager.message.listAjax',compact('list','page','keyword'));
            }else{
                return view('manager.message.list',compact('list','page','keyword'));
            }
        }
        
        //添加站内信息
        public function messageAdd(Request $request){
            $company = ['0'=>'指定会员','1'=>'所有会员'];
            return view('manager.message.add',compact('company'));
        }
        
        //保存站内信息
        public function messageUpdate(Request $request){
            $id = intval($request->input("id",0));
            $alluser  = $request->input("alluser",0);
            $username = $request->input("username","");
            $data  = $request->except(['_token','page']);
            $users = explode('|',$username);
            $data['from_uid']      = $this->adminId;
            $data['from_username'] = auth('admin')->user()->login_name;
            $data['message_ip']    = getip();
            if(isset($data['company_id'])){
                $data['member_agent_id'] = 0;
                $data['member_pid']      = 0;
                $data['member_agent_name'] = '所有';
                $res = Message::create($data);
                if($res){
                    return response()->json(['status' => 1,'msg' => '信息发送成功！']);
                }
                return response()->json(['status' => 0,'msg' => '信息发送失败！']);
            }
            DB::beginTransaction();
            try{
                $nouser = '';
                $scount = 0;
                $fcount = 0;
                foreach($users as $var){
                    $member_id = Member::where("login_name",$var)->value('id');
                    if(empty($member_id)){
                        $nouser = $nouser.$var.',';
                        $fcount = $fcount+1;
                        continue;
                    }
                    $data['member_id'] = $member_id;
                    $data['member_name'] = $var;
                    $res = Message::create($data);
                    $scount = $scount+1;
                }
                DB::commit();
                $msg = '';
                if($scount>0){
                    $msg = $msg.$scount.'条信息发送成功！ ';
                }
                if($fcount>0){
                    $msg = $msg.$fcount.'条信息发送失败，帐号：'.$nouser.'不存在！';
                }
                Helper::addAdminLog($this->adminId,'【发送站内信息】','create');
                return response()->json(['status' => 1,'msg' => $msg]);
            }catch(\Exception $e){
                DB::rollBack();
                return response()->json(['status' => 0,'msg' => '信息发送失败！']);
            }
        }
        
        //删除站内信息
        public function messageDel(Request $request){
            $id = intval($request->input("id",0));
            $res = Message::where('id',$id)->update(['deleted_at' => now()]);
            
            if($res){
                Helper::addAdminLog($this->adminId,'【删除站内信息】id=》'.$id,'delete');
                return response()->json(['status' => 1,'msg' => '信息删除成功!']);
            }
            return response()->json(['status' => 0,'msg' => '信息删除失败!']);
        }
    }
