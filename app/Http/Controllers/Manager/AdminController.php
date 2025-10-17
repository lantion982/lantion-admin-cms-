<?php
    //后台帐号|权限
    namespace App\Http\Controllers\Manager;
    
    use App\Libs\Helper;
    use App\Models\Admin;
    use App\Models\Permission;
    use App\Models\Role;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\View;
    use App\Services\RBAC\Facades\RBAC;
    
    class AdminController extends Controller{
        protected $adminId;
        
        protected $role;
        protected $permission;
        
        public function __construct(){
            parent::__construct();
            $this->middleware(function($request,$next){
                $this->adminId = Auth::guard('admin')->user()->id;
                return $next($request);
            });
        }
        
        //后台帐号管理
        public function adminList(Request $request){
            $page_title['type']    = 'Search';
            $page_title['content'] = 'manager.admin.adminSearch';
            $data       = $request->except(['_token','page']);
            $page_count = $request->input("page_count",20);
            $page       = $request->input("page",1);;
            $admins = Admin::where('is_show',1)->where(function($query) use ($data){
                    if(array_key_exists('keyword',$data)&&trim($data['keyword'])!=''){
                        $query->where(function($query) use ($data){
                            $query->orWhere('login_name','like','%'.trim($data['keyword']).'%');
                            $query->orWhere('display_name','like','%'.trim($data['keyword']).'%');
                        });
                    }
                    if(array_key_exists('is_allow',$data)&&$data['is_allow']!=''&&$data['is_allow']!='all'){
                        $query->where('is_allow',trim($data['is_allow']));
                    }
                })->orderBy('tb_admin.id','ASC')->paginate($page_count);
            if($request->ajax()){
                return view('manager.admin.adminListAjax',compact('admins','page_title','page','page_count'));
            }else{
                return view('manager.admin.adminList',compact('admins','page_title','page','page_count'));
            }
        }
        
        //添加新帐号
        public function addAdminInfo(){
            $roles  = RBAC::getRoles();
            $admins = null;
            return view('manager.admin.adminInfo',compact('admins','roles'));
        }
        
        public function createAdminInfo(Request $request){
            $data = $request->except(['_token','page']);
            $data['login_pwd'] = bcrypt($request['login_pwd']);
            if(auth('admin')->user()->is_admin||auth('admin')->user()->can('assignRoles')){
                if(!isset($data['roles_id'])){
                    return response()->json(['status' => FAILED,'msg' => '请选择帐号的角色！']);
                }
                $data['roles'] = explode(',',$data['roles_id']);
            }
            $data['register_ip'] = Helper::getClientIP();
            $res = Admin::where('login_name',$data['login_name'])->count();
            if($res>0){
                return response()->json(['status' => FAILED,'msg' => '您输入的登录帐号已经存在，请重新输入！']);
            }
            try{
                $admin = Admin::create($data);
                if($admin){
                    if(count($data['roles'])>0){
                        $admin->attachRoles($data['roles']);
                    }
                }
                Helper::addAdminLog($this->adminId,'后台账户创建成功','create');
            }catch(\Exception $e){
                Log::error('【新增管理网账户失败】=》'.$e->getMessage());
                Helper::addAdminLog($this->adminId,'后台账户创建失败','create');
                return response()->json(['status' => FAILED,'msg' => '新增账户失败！']);
            }
            return response()->json(['status' => SUCCESS,'msg' => '新增账户成功！']);
        }
        
        //编辑帐号信息
        public function adminInfo(Request $request){
            $id    = intval($request->input('admin_id',0));
            $admin = Admin::find($id);
            $data['admins'] = $admin;
            $data['roles']  = RBAC::getRoles();
            return View('manager.admin.adminInfo',$data);
        }
        
        public function updateAdminInfo(Request $request){
            $id    = intval($request->input('admin_id',0));
            $admin = Admin::where('id',$id)->first();
            if(empty($admin)){
                return response()->json(['status' => FAILED,'msg' => '账号未找到！','url' => '/manager/adminAccount']);
            }
            if(!isset($request['login_pwd'])||trim($request['login_pwd'])==''){
                $data = $request->except('login_pwd');
            }else{
                $data = $request->all();
                $data['login_pwd'] = bcrypt($request['login_pwd']);
            }
            if(auth('admin')->user()->is_admin||auth('admin')->user()->can('assignRoles')){
                if(!isset($data['roles_id'])){
                    return response()->json(['status' => FAILED,'msg' => '请选择帐号的角色！']);
                }
                $data['roles'] = explode(',',$data['roles_id']);
            }
            
            if($request['login_pwd']==null){
                $pwd = null;
            }else{
                $pwd = '　更改了用户登录密码！';
            }
            
            try{
                $admin->update($data);
                $admin->roles()->detach();
                if(isset($data['roles'])){
                    $admin->attachRoles($data['roles']);
                }
                Helper::addAdminLog($this->adminId,'用户更新成功=》ID：'.$id.$pwd,'update');
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,'更新用户失败','update');
                return response()->json(['status' => FAILED,'msg' => '更新失败！']);
            }
            return response()->json(['status' => SUCCESS,'msg' => '更新成功！']);
        }
    
        //删除也帐号，要删除用户user_role 中的关联
        public function delAdminInfo(Request $request){
            $id = intval($request->input('admin_id',0));
            try{
                $admin = Admin::where('id',$id)->first();
                if(!$admin){
                    return response()->json(['status' => FAILED,'msg' => '帐号信息未找到，删除失败！']);
                }
                $admin->roles()->detach();
                $admin->delete();
                Helper::addAdminLog($this->adminId,'后台帐号删除成功=》ID：'.$id,'delete');
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,'后台帐号删除失败=》ID：'.$id,'delete');
                return response()->json(['status' => FAILED,'msg' => '帐号删除失败！']);
            }
            return response()->json(['status' => SUCCESS,'msg' => '帐号删除成功！']);
        }
        
        //更新状态
        public function updateAdminStatus(Request $request){
            $id    = intval($request->input('admin_id',0));
            $admin = Admin::where('id',$id)->first();
            if(empty($admin)){
                return response()->json(['status' => FAILED,'msg' => '账号未找到！','url' => '/manager/adminAccount']);
            }
            $data = $request->all();
            $data['failed_count'] = 0;
            try{
                $admin->update($data);
                Helper::addAdminLog($this->adminId,'后台帐号更新成功：ID：'.$id,'update');
            }catch(\Exception $e){
                Helper::addAdminLog($this->adminId,'后台用户更新失败','update');
                return response()->json(['status' => FAILED,'msg' => '更新失败！']);
            }
            return response()->json(['status' => SUCCESS,'msg' => '更新成功！']);
        }
        
        //修改密码页面
        public function adminPassword(Request $request){
            $admin = auth('admin')->user();
            return View::make('manager.admin.adminEditPwd')->with(['entrustAdmin' => $admin])->render();
        }
        
        public function updateAdminPassword(Request $request){
            $admin = auth('admin')->user();
            $data  = [];
            if($request['new_pass']){
                if(!$request['old_pass']){
                    return response()->json(['status' => FAILED,'msg' => '请输入原登录密码！']);
                }
                if($request['new_pass']!=$request['cof_pass']){
                    return response()->json(['status' => FAILED,'msg' => '确认密码输入不一致！']);
                }
                if(!\Hash::check($request['old_pass'],$admin->login_pwd)){
                    return response()->json(['status' => FAILED,'msg' => '原登录密码错误！']);
                }
                $data['login_pwd'] = \Hash::make($request['new_pass']);
            }
            if(empty($data)){
                return response()->json(['status' => FAILED,'msg' => '请输入要修改的内容！']);
            }
            $admin->update($data);
            if($request['new_pass']){
                auth('admin')->logout();
                $request->session()->flush();
                $msg = '修改成功，请重新登录！';
            }else{
                $msg = '修改成功！';
            }
            Helper::addAdminLog($this->adminId,'登录密码修改','update');
            return response()->json(['status' => SUCCESS,'msg' => $msg]);
        }
        
        //角色管理
        public function role(Request $request){
            $page_title['type']    = 'Title';
            $page_title['content'] = '';
            $data = $request->except(['_token','page']);
            $page = $request->input("page",1);
            $page_count = $request->input("page_count",20);
            if(array_key_exists('page_count',$data)&&$data['page_count']!=''){
                $page_count = $data['page_count'];
            }
            $roles = Role::orderBy('id','asc')->paginate($page_count);
            if($request->ajax()){
                return view('manager.admin.roleListAjax',compact('roles','page_title','page','page_count'));
            }else{
                return view('manager.admin.roleList',compact('roles','page_title','page','page_count'));
            }
        }
        
        //新建角色
        public function addRole(){
            $roles = null;
            return view('manager.admin.roleInfo',compact('roles'));
        }
        
        public function createRole(Request $request){
            if(trim($request->input("title"))===''){
                return response()->json(['status' => FAILED,'msg' => '请输入角色名称！','url' => '/manager/role']);
            }
            $title = $request['title'];
            $res = Role::where(['title' => $title])->count();
            if($res>0){
                return response()->json(['status' => FAILED,'msg' => '角色名称已经存在，请重新输入！']);
            }
            $data = $request->except('_token','id');
            $result = Role::create($data);
            if($result){
                Helper::addAdminLog($this->adminId,'新增角色成功=>ID：'.$result->id.'名称：'.$title,'insert');
                return response()->json(['status' => SUCCESS,'msg' => '新增成功！']);
            }
            Helper::addAdminLog($this->adminId,'新增角色失败','insert');
            return response()->json(['status' => FAILED,'msg' => '新增失败！']);
        }
        
        //编辑角色信息
        public function roleInfo(Request $request){
            $id    = intval($request->input('id',0));
            $roles = Role::where('id',$id)->first();
            return view('manager.admin.roleInfo',compact('roles'));
        }
        
        public function updateRoleInfo(Request $request){
            if(trim($request->input("title"))===''){
                return response()->json(['status' => FAILED,'msg' => '请输入角色名称！','url' => '/manager/role']);
            }
            $id   = intval($request->input('id',0));
            $role = Role::find($id);
            if(empty($role)){
                return response()->json(['status' => FAILED,'msg' => '角色未找到！','url' => '/manager/role']);
            }
            $title = $request['title'];
            $res   = Role::where(['title' => $title])->where('id','<>',$id)->count();
            if($res>0){
                return response()->json(['status' => FAILED,'msg' => '角色名称已经存在，请重新输入！']);
            }
            $result = $role->update($request->all());
            if($result){
                Helper::addAdminLog($this->adminId,'角色更新成功=> 角色ID：'.$id,'update');
            }else{
                Helper::addAdminLog($this->adminId,'角色更新失败=> 角色ID：.$id','update');
            }
            return response()->json($result?['status' => SUCCESS,'msg' => '更新成功！']:['status' => FAILED,'msg' => '更新失败！']);
        }
        
        //删除角色
        public function deleteRole(Request $request){
            $id  = intval($request->input('id',0));
            $res = Role::where('id',$id)->delete();
            if($res){
                Helper::addAdminLog($this->adminId,'角色删除成功： 角色ID：'.$id,'delete');
                return response()->json(['status' => SUCCESS,'msg' => '删除成功！']);
            }
            Helper::addAdminLog($this->adminId,'角色删除失败=》：'.$id,'delete');
            return response()->json(['status' => FAILED,'msg' => '删除失败！']);
           
        }
    
        //角色权限管理
        public function rolePermission(Request $request){
            $page_title['type']    = 'Title';
            $page_title['content'] = '权限管理->角色权限';
            $id    = intval($request->input('id',0));
            $role  = Role::where('id',$id)->first();
            $roleArr = [];
            $permIds = Role::find($id)->perms()->get();
            foreach ($permIds as $item){
                $roleArr[$item->id] = $item->name;
            }
            $listZtree = $this->permissionsTree($roleArr);
            return view('manager.admin.rolePermission',compact('role','page_title','listZtree','role'));
        }
    
        //获取角色权限树
        public function permissionsTree($arr){
            $permissions = Permission::orderBy('sorts','asc')->get();
            $str  = "[";
            $last = end($permissions);
            foreach($permissions as $key => $var){
                $checked = '';
                if(array_key_exists($var->id,$arr)){
                    $checked  = ',checked:true';
                }
                $title = $var->title;
                if($last==$var){
                    $str .= "{id:'".$var->id."',pId:'".$var->parent_id."',name:'".$title."'".$checked."}";
                }else{
                    $str .= "{id:'".$var->id."',pId:'".$var->parent_id."',name:'".$title."'".$checked."},";
                }
            }
            $str .= ']';
            return $str;
        }
        
        //更新角色权限管理
        public function updateRolePermission(Request $request){
            $id = intval($request->input('id',0));
            if(!empty($request->input('checkNodes',[]))){
                $perms  = explode(",",$request->input('checkNodes'));
                $role   = Role::find($id);
                $result = $role->perms()->sync($perms);
                Helper::addAdminLog($this->adminId,'更新角色权限成功=>ID：'.$id,'update');
                return response()->json($result?['status' => SUCCESS,'msg' => '更新成功！']:['status' => FAILED,'msg' => '更新失败！',]);
            }else{
                Helper::addAdminLog($this->adminId,'更新角色权限失败=》：'.$id,'update');
                return response()->json(['status' => SUCCESS,'msg' => '权限没有更改或没有选中权限！']);
            }
        }
        
        //权限管理
        public function permission(Request $request){
            $page_title['type']    = 'Title';
            $page_title['content'] = '系统设置-权限管理';
            $page = '1';
            if(isset($request['page'])){
                $page = $request['page'];
            }
            $data = $request->except(['_token','page']);
            $page_count = $request->input("page_count",20);
            if(array_key_exists('page_count',$data)&&$data['page_count']!=''){
                $page_count = $data['page_count'];
            }
            $permissions = Permission::where('parent_id','0')->orderBy('sorts','asc')->paginate($page_count);
            if($request->ajax()){
                return view('manager.admin.permissionList',compact('permissions','page_title','page','page_count'));
            }else{
                return view('manager.admin.permission',compact('permissions','page_title','page','page_count'));
            }
        }
        
        //新增权限
        public function addPermission(){
            $topPermissions = RBAC::getTopPermissions();
            $permission     = null;
            return view('manager.admin.permissionInfo',compact('permission','topPermissions'));
        }
        
        //新增下级权限
        public function addSubPermission(Request $request){
            $id = intval($request->input('id',0));
            $parentPermission = Permission::find($id);
            $permission       = null;
            return view('manager.admin.permissionInfo',compact('permission','parentPermission'));
        }
        
        public function createPermission(Request $request){
            $data   = $request->except('_token','id');
            $result = Permission::create($data);
            if($result){
                Helper::addAdminLog($this->adminId,'新增权限成功=>ID：'.$result->id.'|路由*：'.$data['name'],'insert');
                return response()->json(['status' => SUCCESS,'content' => ['parent_id' => $result->parent_id],'msg' => '权限新增成功！']);
            }
            Helper::addAdminLog($this->adminId,'新增权限失败','insert');
            return response()->json(['status' => FAILED,'msg' => '权限新增失败！']);
        }
        
        //编辑权限信息
        public function permissionInfo(Request $request){
            $id = intval($request->input('id',0));
            $parentPermission = RBAC::getParentPermission($id);
            if(empty($parentPermission)){
                //是顶级权限
                $parentPermission = new \App\Models\Permission();
                $parentPermission->id = '0';
                $parentPermission->title = '--顶级权限--';
            }
            $permission = Permission::find($id);
            return view('manager.admin.permissionInfo',compact('permission','parentPermission'));
        }
        
        public function updatePermissionInfo(Request $request){
            $id   = intval($request->input('id',0));
            $data = $request->except('_token','id','parent_id','parent_name');
            $permission = Permission::where('id',$id)->first();
            if(empty($permission)){
                return response()->json(['status' => FAILED,'msg' => '权限未找到！','url' => '/manager/entrustPermission']);
            }
            $result = Permission::where('id',$id)->update($data);
            if($result){
                Helper::addAdminLog($this->adminId,'权限更新成功=》ID：'.$id,'update');
                return response()->json(['status' => SUCCESS,'content' => ['parent_id' => $permission->parent_id],'msg' => '更新成功！']);
            }
            Helper::addAdminLog($this->adminId,'权限更新失败=》ID：'.$id,'update');
            return response()->json(['status' => FAILED,'msg' => '更新失败！']);
        }
    
        //删除权限
        public function deletePermission(Request $request){
            $id  = intval($request->input('id',0));
            $res = Permission::where('id',$id)->delete();
            if($res){
                Helper::addAdminLog($this->adminId,'权限删除成功=> ID：'.$id,'delete');
                return response()->json(['status' => SUCCESS,'msg' => '权限删除成功！']);
            }
            Helper::addAdminLog($this->adminId,'权限删除失败=> ID：'.$id,'delete');
            return response()->json(['status' => FAILED,'msg' => '权限删除失败！']);
        }
        
        //页面权限
        public function permPageFunc(Request $request){
            $data = $request->except(['_token','page']);
            $id   = $request['id'];
            $page_count = $request->input("page_count",15);
            $page_title['type']    = 'Title';
            $page_title['content'] = '系统设置-页面权限';
            $parentPermission = Permission::find($id);
            $permissions      = Permission::where('parent_id', $id)->orderBy('sorts', 'ASC')->paginate($page_count);
            //$permissions->appends(['id' => $id]);
            
            if($request->ajax()){
                return view('manager.admin.permPageFuncList',compact('parentPermission','permissions','page_title','page_count'));
            }else{
                return view('manager.admin.permPageFunc',compact('parentPermission','permissions','page_title','page_count'));
            }
        }
    }
