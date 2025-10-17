<?php
    /*
    |--------------------------------------------------------------------------
    | 新闻资讯
    |--------------------------------------------------------------------------
    */
    Route::get('/linkList','LinkController@linkList')->name('linkList');
    Route::get('/linkAdd','LinkController@linkAdd')->name('linkAdd');
    Route::post('/linkCreate','LinkController@linkCreate')->name('linkCreate');
    Route::get('/linkEdit','LinkController@linkEdit')->name('linkEdit');
    Route::post('/linkUpdate','LinkController@linkUpdate')->name('linkUpdate');
    Route::post('/linkDel','LinkController@linkDel')->name('linkDel');
