<?php
    /*
	|--------------------------------------------------------------------------
	| 系统设置/IP黑白名单
	|--------------------------------------------------------------------------
	*/
    
    //系统设置
    Route::get('/listIP','ConfigController@listIP')->name('listIP');
    Route::get('/addIP','ConfigController@addIP')->name('addIP');
    Route::post('/createIP','ConfigController@createIP')->name('createIP');
    Route::get('/editIP','ConfigController@editIP')->name('editIP');
    Route::post('/updateIP','ConfigController@updateIP')->name('updateIP');
    Route::post('/delIP','ConfigController@delIP')->name('delIP');
