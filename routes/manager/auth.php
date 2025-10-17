<?php
    /*
	|--------------------------------------------------------------------------
	| 登录验证相关
	|--------------------------------------------------------------------------
	*/
    
    Route::get('/login','LoginController@showLoginForm');
    Route::post('/login','LoginController@login');
    Route::post('/mylogin','LoginController@mylogin')->name('mylogin');
    Route::get('/logout','LoginController@logout');
    Route::post('/logout','LoginController@logout');
