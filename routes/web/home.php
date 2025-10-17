<?php
    
    /*
	|--------------------------------------------------------------------------
	| 首页
	|--------------------------------------------------------------------------
	*/
    
    Route::get('/index','PicController@index')->name('home');
    
    Route::get('/login','LoginController@showLoginForm');
    Route::post('/checkLogin','LoginController@checkLogin')->name('userLogin');
    Route::any('/logout','LoginController@logout');
