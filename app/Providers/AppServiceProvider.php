<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider{
	
	public function register(){}
	
	public function boot(){
		//api 除外强转Https
		/*$uri = request()->getRequestUri();
		if(!starts_with($uri,'/api')){
			if(config('app.https') === true){
				\URL::forceScheme('https');
			}
		}*/
	}
}
