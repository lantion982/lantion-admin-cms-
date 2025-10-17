<?php
    /*
	|--------------------------------------------------------------------------
	| 客户管理-反馈|回访
	|--------------------------------------------------------------------------
	*/
    
    //意见反馈
    Route::get('feedBack','CustomerController@feedBack')->name('feedBack');
    //回访中心
    Route::get('/callCenter','CustomerController@callCenter')->name('callCenter');
    //查看备注
    Route::get('/callCenterInfo','CustomerController@callCenterInfo')->name('callCenterInfo');
    //拨号
    Route::get('/voiceStar','CustomerController@voiceStar')->name('voiceStar');
    //记录拨打时间
    Route::post('/userIsDialed','CustomerController@userIsDialed')->name('userIsDialed');
    //更新备注
    Route::post('/userRemark','CustomerController@userRemark')->name('userRemark');
    //更新真实虚假用户
    Route::post('/userIsReal','CustomerController@userIsReal')->name('userIsReal');
    //更新是否已添加微信
    Route::post('/userIsAdd','CustomerController@userIsAdd')->name('userIsAdd');
    //添加标签
    Route::post('/userAddTabs','CustomerController@userAddTabs')->name('userAddTabs');
    
    Route::get('/memberTagsInfo','CustomerController@memberTagsInfo')->name('memberTagsInfo');

