<?php
    //新闻公告管理
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Models\Link;
    use App\Models\LinkClass;
    use App\Models\Member;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\View;
    
    class LinkController extends Controller{
        protected $adminId;
        
        public function __construct(){
            parent::__construct();
            $this->middleware(function($request,$next){
                $this->adminId = Auth::guard('admin')->user()->id;
                return $next($request);
            });
        }
        
        //网址列表
        public function linkList(Request $request){
            $pid  = intval($request->input("pid",1));
            $linkClass = LinkClass::where(function($query) use($pid){
                if($pid!=0){
                    $query->where('parent_id',$pid);
                }else{
                    $query->where('parent_id','!=',0);
                }
            })->orderBy('parent_id','asc')->select('id','title')->get();
            $pclass = LinkClass::where('parent_id',0)->orderBy('id','asc')->select('id','title')->get();
            $data   = $request->except(['_token','page']);
            $page   = $request->input("page",1);
            $links  = Link::where('pid',$pid)->where(function($query) use ($data){
                if(array_key_exists('startDate',$data)&&$data['startDate']!=''){
                    $query->where('created_at','>=',$data['startDate']);
                }
                if(array_key_exists('endDate',$data)&&$data['endDate']!=''){
                    $query->where('created_at','<=',$data['endDate']);
                }
                if(array_key_exists('cid',$data)&&$data['cid']!=0){
                    $query->where('cid',$data['cid']);
                }
                $query->where(function($query) use($data){
                    if(array_key_exists('keyword',$data)&&trim($data['keyword'])!=''&&!is_null(trim($data['keyword']))){
                        $query->where('title','like','%'.trim($data['keyword']).'%');
                        $query->orWhere('link','like','%'.trim($data['keyword']).'%');
                    }
                });
            })->orderBy('id','desc')->paginate(20);
           
            $page_title['type']    = 'Search';
            $page_title['content'] = 'manager.link.search';
            $data['cid'] = intval($request->input("page",0));
            if($request->ajax()){
                return View('manager.link.listAjax',compact('links','linkClass','page_title','page','data','pid','pclass'));
            }else{
                return View('manager.link.list',compact('links','linkClass','page_title','page','data','pid','pclass'));
            }
        }
        
        public function linkAdd(Request $request){
            $link      = null;
            $pic_url   = '';
            $picConfig = [];
            return View::make('manager.link.info',compact('link','picConfig','pic_url'));
        }
        
        public function linkCreate(Request $request){
            $data  = $request->except('_token');
            $sorts = $data['$sorts']??0;
            if($sorts>0){
                Link::where('sorts','>=',$sorts)->increment('sorts');
            }
            $res = Link::create($data);
            if($res){
                $log = '【新增网址信息】=》'.$data['news_title'].'，ID：'.$res->id;
                Helper::addAdminLog($this->adminId,$log,'insert');
                return response()->json(['status' => SUCCESS,'msg' => '新增成功!']);
            }
            return response()->json(['status' => FAILED,'msg' => '新增失败!']);
        }
        
        public function linkEdit(Request $request){
            $id   = intval($request->input('id',''));
            $link = Link::where('id',$id)->first();
            $pic_url    = '';
            $picConfig  = [];
            if(!empty($link->icon)){
                $picConfig[]  = ['key'=>$link->icon];
                $pic_url = '/uploads/'.$link->icon;
            }
            return View::make('manager.link.info',compact('link','picConfig','pic_url'));
        }
        
        
        public function linkUpdate(Request $request){
            $id   = intval($request->input('id',''));
            $link = Link::where('id',$id)->first();
            if(!$link){
                return response()->json(['status' => FAILED,'msg' => '该信息未找到或已被删除！']);
            }
            $data = $request->except('_token','id');
            $sort = $data['sorts']??0;
            if($sort>0){
                Link::where('sorts','>=',1)->increment('sorts');
            }
            $result = Link::where('id',$id)->update($data);
            if($result){
                $log = '【网址信息更新成功】=》'.$link->title.'，ID=》'.$link->id;
                Helper::addAdminLog($this->adminId,$log,'update');
                return response()->json(['status' => SUCCESS,'msg' => '网址信息更新成功！']);
            }
            $log = '【网址信息更新失败】=》'.$link->title.'，ID=》'.$link->id;
            Helper::addAdminLog($this->adminId,$log,'update');
            return response()->json(['status' => FAILED,'msg' => '网址信息更新失败！']);
        }
        
        public function linkDel(Request $request){
            $id   = intval($request->input('id',''));
            try{
                Link::where('id',$id)->delete();
                Helper::addAdminLog($this->adminId,'【删除网址信息】ID=》'.$id,'delete');
                return response()->json(['status' => SUCCESS,'msg' => '网址删除成功！']);
            }catch(\Exception $e){
                Log::info('【网址删除失败】=>'.$e->getMessage());
                return response()->json(['status' => FAILED,'msg' => '网址删除失败！']);
            }
        }
        
        public function linkClass(Request $request){
            $page = $request->input("page",1);
            $list = LinkClass::orderBy('id','desc')->paginate(20);
            
            if($request->ajax()){
                return view('manager.link.classAjax',compact('list','page'));
            }else{
                return view('manager.link.class',compact('list','page'));
            }
        }
        
        public function linkClassAdd(Request $request){
            return view('manager.link.classAdd');
        }
        
        public function linkClassCreate(Request $request){
            $id = intval($request->input("id",0));
            $data  = $request->except(['_token','page']);
            $res = LinkClass::create($data);
            Helper::addAdminLog($this->adminId,'【更新导航分类信息】','create');
            if($res){
                return response()->json(['status' => 1,'msg' => '分类信息添加成功！']);
            }
            return response()->json(['status' => 0,'msg' => '分类信息添加失败！']);
        }
        
        public function linkClassDel(Request $request){
            $id  = intval($request->input("id",0));
            $res = LinkClass::where('id',$id)->delete();
            if($res){
                Helper::addAdminLog($this->adminId,'【删除导航分类】id=》'.$id,'delete');
                return response()->json(['status' => 1,'msg' => '分类信息删除成功！']);
            }
            return response()->json(['status' => 0,'msg' => '分类信息删除失败！']);
        }
    }
