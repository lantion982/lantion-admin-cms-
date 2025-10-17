<?php
/*
|--------------------------------------------------------------------------
|  API接口 Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace'  => 'ApiV2',],function(){
	//登录前不需要权限控制的
	Route::group([],function(){
		require_once __DIR__ . '/api/api_auth.php';
	});

	//登录后需要权限控制的
	Route::group(['middleware' => ['before' => 'auth:api', /*'after' => 'SingleMiddleware'*/]],function(){
		require_once __DIR__ . '/api/api_list.php';
	});
});

