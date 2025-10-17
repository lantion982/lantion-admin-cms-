<?php
//RBAC 权限
namespace App\Services\RBAC\RBAC;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class RBAC{

	protected $permission;
	protected $role;

	//获取所有角色，添加编辑管理员信息时用
	public function getRoles($flag = NULL){
		$allRoles = Role::all(['id','title']);
		$arr = [];
		if($flag == 'all'){
			$arr['all'] = '全部';
		}

		foreach($allRoles as $role){
			$arr[$role['id']] = $role['title'];
		}
		return $arr;
	}

	//获取所有顶级权限
	public function getTopPermissions(){
        $topPermissions = Permission::where(['parent_id'=>'0'])->orderBy('sorts','asc')->orderBy('id','asc')->get();
		$arr = [];
		foreach($topPermissions as $topPermission){
			$arr[$topPermission->id] = $topPermission->title.'['.$topPermission->name.']';
		}
		$arr[0] = '--顶级权限--';
		return $arr;
	}

	//获取当前权限的上级
	public function getParentPermission($id){
		$parent_id = Permission::find($id)->parent_id;
		if($parent_id <> '0'){
			$parent = Permission::find($parent_id);
			return $parent;
		}else{
			return null;
		}
	}

	//左侧菜单，子菜单是否为激活状态
	public function getMenus(){
		$action     = '';
		$controller = '';
		$method     = '';
		$request    = Request::route();
        $admins     = Auth::guard('admin')->user();
		if(!empty($request)){
			$action = $request->getActionName();
		}
		if(str_contains($action,'@')){
			$curRoutes  = explode('@',$action);
			$controller = $curRoutes[0];
			$method     = $curRoutes[1];
		}

		$retMenus = [];
		$menus = Permission::where(['parent_id'=>0,'is_show'=>1,'ptype'=>'menu'])->orderBy('sorts','asc')->orderBy('id','asc')->get()->toArray();
		if(!empty($menus)){
			foreach($menus as $menu){
				$class = '';
				if(!$admins->is_admin && !$admins->can($menu['name'])){
					continue;
				}
				$menu['class'] = $class;
				$menu['href']  = '#';

				if($menu['sub_permission']){
					foreach($menu['sub_permission'] as $key => $subMenu){
						$subMenu['href']  = ($subMenu['name'] == '#')?'#':route($subMenu['name']);
						$subMenu['icon']  = $subMenu['icon_html']?$subMenu['icon_html']:'<i class="fa fa-caret-right"></i>';
						$subMenu['class'] = '';
                        //不显示func
						if($subMenu['ptype'] != 'func' && $subMenu['is_show'] != '0'){
							if(str_is($method,$subMenu['name'])){
								$subMenu['class'] = 'active';
								$menu['class']    = $menu['class'].' active';
							}elseif($subMenu['sub_permission'] && $subMenu['is_show'] != '0'){
								foreach($subMenu['sub_permission'] as $key => $page){
									if(str_is($method,$page['name'])){
										$subMenu['class'] = 'active';
										$menu['class']    = $menu['class'].' active';
										break;
									}
								}
							}
							if(!$admins->is_admin &&!$admins->can($subMenu['name'])){
								continue;
							}
							$menu['subMenu'][] = $subMenu;
						}
					}
					unset($menu['sub_permission']);
				}
				$retMenus[] = $menu;
			}
		}
		return $retMenus;
	}

	//取左侧菜单，取到第三级
	public function getMenuslv3(){
		$admins   = Auth::guard('admin')->user();
		$retMenus = [];
		
		$menus = Permission::where(['parent_id'=>0,'is_show'=>1,'ptype'=>'menu'])->where("name",'<>','dashboard')
			->orderBy('sorts','asc')->orderBy('id','desc')->get()->toArray();
		foreach($menus as $menu){
			if(($admins->is_admin) || $admins->can($menu['name'])){
				$menu['href'] = '#';
				$subMenuLv2 = Permission::where("parent_id",$menu['id'])->where('ptype','<>','func')->where('is_show',1)->orderBy('sorts')->get()->toArray();
				$menu['subMenulv2'] = $subMenuLv2;
				if($menu['subMenulv2']){
					foreach($menu['subMenulv2'] as $key => $subMenu){
						$subMenuLv3 = Permission::where("parent_id",$subMenu['id'])->where('ptype','=','menu')
							->where('is_show',1)
							->orderBy('sorts')->get()->toArray();
						$menu['subMenulv3s'] = $subMenuLv3;
						if($menu['subMenulv3s']){
							foreach($menu['subMenulv3s'] as $key2 => $subMenulv3){
								if(!$admins->is_admin && !$admins->can($subMenulv3['name'])){
									continue;
								}
								$subMenulv3['href'] = route($subMenulv3['name']);
								$subMenu['subMenulv3'][] = $subMenulv3;
							}
						}
						
						if(!$admins->is_admin && !$admins->can($subMenu['name'])){
							continue;
						}
						$subMenu['href'] = route($subMenu['name']);
						unset($menu['subMenulv2']);
						unset($menu['subMenulv3s']);
						unset($menu['sub_permission']);
						$menu['subMenu'][] = $subMenu;
					}
				}
			}else{
				continue;
			}

			$retMenus[] = $menu;
		}
		return $retMenus;
	}


	//根据permissionName取得页面权限
	public function getPages($permissionName){
		$action     = '';
		$controller = '';
		$method     = '';
		$request    = Request::route();
		if(!empty($request)){
			$action = $request->getActionName();
		}
		if(str_contains($action,'@')){
			$curRoutes  = explode('@',$action);
			$controller = $curRoutes[0];
			$method     = $curRoutes[1];
		}
		$retPages = [];
		$menu     = Permission::where(['name'=> $permissionName,'ptype'=>'menu','is_show'=>'1'])->orderBy('sorts','asc')->first();

		if(!empty($menu)){
			if($menu['sub_permission']){
				foreach($menu['sub_permission'] as $key => $page){
					$class = '';
					if($page['is_show'] == '0'){
						continue;
					}
					if(!Auth::guard('admin')->user()->is_admin && !Auth::guard('admin')->user()->can($page['name'])){
						continue;
					}
					if($page['ptype'] == 'func'){
						continue;
					}
					if($method == $page['name']){
						$class = ' active';
					}
					$page['class'] = $class;
					$page['href']  = ($page['name'] == '#')?'#':route($page['name']);
					$retPages[]    = $page;
				}
			}
		}
		return $retPages;
	}
}
