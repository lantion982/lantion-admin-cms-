<?php
//注册身份验证/授权服务
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider{
	protected $policies = [
		'App\Model' => 'App\Policies\ModelPolicy',
	];
	
	public function boot(){
		$this->registerPolicies();
		\Route::group(['middleware' => 'passport.multiAuth'], function () {
			Passport::routes();
		});
		
		Passport::tokensExpireIn(Carbon::now()->addMinutes(config('passport.token_time')));
		Passport::refreshTokensExpireIn(Carbon::now()->addDays(config('passport.refresh_time')));
		Passport::pruneRevokedTokens();
		\Auth::provider('auth-eloquent', function ($app, $config) {
			return new AuthEloquentUserProvider($this->app['hash'], $config['model']);
		});
	}
}
