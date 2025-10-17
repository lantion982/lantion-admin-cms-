<?php
    /*
    |--------------------------------------------------------------------------
    | 财务管理
    |--------------------------------------------------------------------------
    */
    Route::get('/downList','DownController@downList')->name('downList');
    Route::get('/downAdd','DownController@downAdd')->name('downAdd');
    Route::post('/downCreate','DownController@downCreate')->name('downCreate');
    Route::get('/downEdit','DownController@downEdit')->name('downEdit');
    Route::post('/downUpdate','DownController@downUpdate')->name('downUpdate');
    Route::post('/downDel','DownController@downDel')->name('downDel');
