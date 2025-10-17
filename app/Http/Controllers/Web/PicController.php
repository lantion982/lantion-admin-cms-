<?php
    //云盘展示
    namespace App\Http\Controllers\Web;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\Auth;

    class PicController extends Controller{
        protected $member;
        public function __construct() {
            $this->middleware('auth.member');
            $this->member = Auth::guard('web')->user();
        }
        public function index(Request $request){
            $picpath  = $this->member->picpath;
            $username = $this->member->nick_name;
            $path     = 'uploads/plmm/';
            if($picpath!=''||!is_null($picpath)){
                $path = 'uploads/'.$picpath.'/';
            }
            $dir  = opendir($path);
            $list = [];
            $i    = 0;
            while(($file = readdir($dir))!== false){
                $varFile = $path.$file;
                if($file!='.'&&$file!='..'){
                    $list[$i]['path'] = $varFile;
                    $list[$i]['name'] = $file;
                    $list[$i]['size'] = filesize($varFile);
                    $list[$i]['type'] = filetype($varFile);
                    $list[$i]['time'] = date("Y/n/t",filemtime($varFile));
                    $i++;
                }
            }
            return view('web.pic.index',compact('list','username'));
        }
    
        public function list(Request $request){
            $username = $this->member->nick_name;
            $path = $request->input('url','');
            $patharr = explode('/',$path);
            $menu = [];
            $l  = 0;
            foreach($patharr as $key=>$val){
                if($key>=2){
                    $menu[$l]['name'] = $val;
                    $tempath = '';
                    for($o=0;$o<=$key;$o++){
                        if($o==$key){
                            $tempath = $tempath.$patharr[$o];
                        }else{
                            $tempath = $tempath.$patharr[$o].'/';
                        }
                    }
                    $menu[$l]['path'] = $tempath;
                    $l++;
                }
            }
            $dir  = opendir($path);
            $list = $data = $dirs = [];
            $i    = 0;
            $j    = 0;
            $page = intval($request->input('page',1));
            $offset = ($page-1)*16+15;
            while(($file = readdir($dir))!== false){
                $varFile = $path.'/'.$file;
                if($file!='.'&&$file!='..'){
                    if(filetype($varFile)=='dir'){
                        $dirs[$j]['path'] = $varFile;
                        $dirs[$j]['name'] = $file;
                        $dirs[$j]['size'] = filesize($varFile);
                        $dirs[$j]['type'] = filetype($varFile);
                        $dirs[$j]['time'] = date("Y/n/t",filemtime($varFile));
                        $j++;
                    }else{
                        $list[$i]['path'] = '/'.$varFile;
                        $list[$i]['name'] = $file;
                        $list[$i]['size'] = filesize($varFile);
                        $list[$i]['type'] = filetype($varFile);
                        $list[$i]['time'] = date("Y/n/t",filemtime($varFile));
                        $i++;
                    }
                }
            }
            
            $data  = array_slice($list,$offset,16);
            $list  = array_slice($list,0,16);
            $count = $i;
            if($request->ajax()){
                $res['json']  = $data;
                $res['count'] = count($data);
                return response()->json(['status'=>1,'content'=>$res,'msg'=>'success']);
            }
            return view('web.pic.list',compact('list','count','path','dirs','username','menu'));
        }
        
        public function del(Request $request){
            $path = $request->input('url');
            $disk = Storage::disk('local');
            try{
                $res  = $disk->delete($path);
                if($res){
                    return response()->json(['status'=>1,'content'=>$path,'msg'=>'图片删除成功！']);
                }
            }catch(\Exception $exc){
                Log::info('【删除图片失败】=》'.$exc->getMessage());
            }
            return response()->json(['status'=>0,'content'=>$path,'msg'=>'图片删除失败！']);
        }
    }
