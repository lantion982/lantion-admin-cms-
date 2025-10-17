<?php
    /*
	|--------------------------------------------------------------------------
	| Web Routes
	|--------------------------------------------------------------------------
	*/
    
    Route::get('/',function(){
        return view('welcome');
    });
    
    Route::group(['prefix' => 'manager','namespace' => 'Manager','middleware' => ['manageWhite'],],function(){
        require_once __DIR__.'/manager/auth.php';
    });
    
    //需要登录权限路由
    Route::group(['prefix' => 'manager','namespace' => 'Manager','middleware' => ['auth.admin','EntrustAuthorize','manageWhite'],],function(){
        require_once __DIR__.'/manager/manager.php';
    });
    
    Route::group(['prefix' => '/','namespace' => 'Web',],function(){
        require_once __DIR__.'/web/home.php';
    });
    
    Route::group(['prefix' => 'plmm','middleware' => ['auth.member'],'namespace' => 'Web',],function(){
        require_once __DIR__.'/web/plmm.php';
    });
 
