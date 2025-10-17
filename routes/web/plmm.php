<?php
    
    /*
	|--------------------------------------------------------------------------
	| 云盘图片展示
	|--------------------------------------------------------------------------
	*/
    Route::get('/index','PicController@index')->name('picindex');
    Route::get('/','PicController@index')->name('plmm');
    Route::any('/list','PicController@list')->name('piclist');
    Route::post('/del','PicController@del')->name('picdel');
