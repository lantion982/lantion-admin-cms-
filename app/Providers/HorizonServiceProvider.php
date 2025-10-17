<?php
//Horizon service
namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider{

	public function boot(){
		parent::boot();
		
		// Horizon::night();
	}

	//Register the Horizon gate.
	protected function gate(){
		Gate::define('viewHorizon',function($username){
			return in_array($username,['lantion']);
		});
	}

	protected function authorization(){
		$this->gate();
		Horizon::auth(function($request){
			if(auth('admin')->check()){
				return true;
			}
			return false;
		});
	}
}
