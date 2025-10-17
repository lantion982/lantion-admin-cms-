<?php
//短信相关服务

namespace App\Providers;

use App\Services\SMS\Services\SMSService;
use Illuminate\Support\ServiceProvider;
use App\Services\SMS\Http;

class SMSServiceProvider extends ServiceProvider{

	public function boot(){
		$this->publishes([
			__DIR__.'/config/config.php' => base_path('config/andySMS.php'),
		]);
	}

	public function register(){
		$this->app->bind('Http',function($app){
			return new Http\Http();
		});

		$this->app->singleton('SMSService',function($app){
			return new SMSService($app['Http']);
		});
	}
}
