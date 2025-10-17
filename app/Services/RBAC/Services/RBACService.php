<?php
//RBAC 权限

namespace App\Services\RBAC\Services;

use App\Services\RBAC\RBAC;

class RBACService{

	protected $rbac;

	public function __construct(RBAC\RBAC $rbac){
		$this->rbac = $rbac;
	}

	public function getRoles($flag = NULL){
		return $this->rbac->getRoles($flag);
	}

	public function getTopPermissions(){
		return $this->rbac->getTopPermissions();
	}

	public function getParentPermission($id){
		return $this->rbac->getParentPermission($id);
	}

	public function getMenus(){
		return $this->rbac->getMenus();
	}

	public function getMenuslv3(){
		return $this->rbac->getMenuslv3();
	}

	public function getPages($permissionName){
		return $this->rbac->getPages($permissionName);
	}
}
