<?php
//权限检查服务
namespace App\Providers;

use App\Services\RBAC\Services\RBACService;
use App\Services\RBAC\RBAC;
use Illuminate\Support\ServiceProvider;

class RBACServiceProvider extends ServiceProvider{

	public function boot(){ }

	public function register(){
		parent::register();
		
		$this->app->bind('RBAC\RBAC',function($app){
			return new RBAC\RBAC();
		});

		$this->app->singleton('RBACService',function($app){
			return new RBACService($app['RBAC\RBAC']);
		});
	}
}
