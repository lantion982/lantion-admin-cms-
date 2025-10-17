<?php

//RBAC 权限
namespace App\Services\RBAC\Facades;

use Illuminate\Support\Facades\Facade;

class RBAC extends Facade{

	protected static function getFacadeAccessor(){
		return 'RBACService';
	}
}