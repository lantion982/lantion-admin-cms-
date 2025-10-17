<?php
    
    /*
	|--------------------------------------------------------------------------
	| 新闻资讯
	|--------------------------------------------------------------------------
	*/
    
    Route::get('/newsList','NewsController@newsList')->name('newsList');
    Route::get('/newsAdd','NewsController@newsAdd')->name('newsAdd');
    Route::post('/newsCreate','NewsController@newsCreate')->name('newsCreate');
    Route::get('/newsEdit','NewsController@newsEdit')->name('newsEdit');
    Route::post('/newsUpdate','NewsController@newsUpdate')->name('newsUpdate');
    Route::post('/newsDel','NewsController@newsDel')->name('newsDel');
    
    //站内信息
    Route::get('/messageList','NewsController@messageList')->name('messageList');
    Route::get('/messageAdd','NewsController@messageAdd')->name('messageAdd');
    Route::post('/messageUpdate','NewsController@messageUpdate')->name('messageUpdate');
    Route::post('/messageDel','NewsController@messageDel')->name('messageDel');
