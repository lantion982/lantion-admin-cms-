<?php
    
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Models\Company;
    use App\Models\IpList;
    use App\Models\Setting;
    use Auth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\View;
    use Mockery\Exception;
    
    class ConfigController extends Controller{
        
        protected $adminId;
        
        public function __construct(){
            parent::__construct();
            $this->middleware(function($request,$next){
                $this->adminId = Auth::guard('admin')->user()->id;
                return $next($request);
            });
        }
        
        public function listIP(Request $request){
            $data = $request->except(['_token','page']);
            $page_title['type']    = 'Search';
            $page_title['content'] = 'manager.config.search';
            $page_count = $request->input("page_count",20);
            $page = 1;
            
            $ipList = IpList::where(function($query) use ($data){
                if(array_key_exists('keyword',$data)&&$data['keyword']!=''){
                    $query->where('ip_addr','like','%'.$data['keyword'].'%')->orWhere('host_name','like','%'.$data['keyword'].'%');
                }
            })->orderBy('block_type','asc')->paginate($page_count);
            
            if(isset($request['page'])){
                $page = $request['page'];
            }
            if($request->ajax()){
                return view('manager.config.IPList',compact('ipList','page_title','page','page_count'));
            }else{
                return view('manager.config.IP',compact('ipList','page_title','page','page_count'));
            }
        }
        
        public function addIP(Request $request){
            $ipInfo = null;
            return View('manager.config.IPInfo',compact('ipInfo'));
        }
        
        public function createIP(Request $request){
            $data = $request->except('_token','id');
            if($request->host_type==='web'){
                $data['block_type'] = 'white';
            }
            try{
                $res = IpList::create($data);
                Helper::addAdminLog($this->adminId,"新增IP黑白名单成功=》".$data['ip_addr'],'insert');
                return response()->json(['status' => SUCCESS,'msg' => '新增IP成功!']);
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,"新增IP黑白名单失败=》".$data['ip_addr'],'insert');
                return response()->json(['status' => FAILED,'msg' => '新增IP失败!']);
            }
        }
        
        public function editIP(Request $request){
            $id = intval($request->input('id',0));
            $ipInfo  = IpList::where('id',$id)->first();
            return view('manager.config.IPInfo',compact('ipInfo'));
        }
        
        public function updateIP(Request $request){
            $id = intval($request->input('id',0));
            $ipInfo  = IpList::where('id',$id)->first();
            if(empty($ipInfo)){
                return response()->json(['status' => FAILED,'msg' => 'IP 信息未找到!']);
            }
            try{
                $data = $request->except('id','_token');
                IpList::where('id',$id)->update($data);
                Helper::addAdminLog($this->adminId,'更新IP黑白名单成功!','update');
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,'更新IP黑白名单失败!','update');
                return response()->json(['status' => FAILED,'msg' => '更新失败!'.$e->getMessage()]);
            }
            return response()->json(['status' => SUCCESS,'msg' => '更新成功!']);
        }
        
        public function delIP(Request $request){
            $id = intval($request->input('id',0));
            try{
                IpList::where('id',$id)->delete();
                Helper::addAdminLog($this->adminId,"IP黑白名单删除成功=》ID:".$id,'delete');
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,"IP黑白名单删除失败=》ID:".$id,'delete');
                return response()->json(['status' => FAILED,'msg' => 'IP黑白名单删除失败!']);
            }
            return response()->json(['status' => SUCCESS,'msg' => 'IP黑白名单删除成功!']);
        }
        
    }
