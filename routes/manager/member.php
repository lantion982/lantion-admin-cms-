<?php
    /*
	|--------------------------------------------------------------------------
	| 会员帐号管理相关路由
	|--------------------------------------------------------------------------
	*/
    
    //会员帐号
    Route::get('/membermenu','MemberController@memberAccount')->name('membermenu');
    Route::get('/memberAccount','MemberController@memberAccount')->name('memberAccount');
    Route::get('/addMemberAccount','MemberController@addMemberAccount')->name('addMemberAccount');
    Route::post('/createMemberAccount','MemberController@createMemberAccount')->name('createMemberAccount');
    Route::get('/memberInfo','MemberController@memberAccountInfo')->name('memberInfo');
    Route::post('/updateMemberInfo','MemberController@updateMemberAccountInfo')->name('updateMemberInfo');
    Route::post('/updateRateInfo','MemberController@updateRateInfo')->name('updateRateInfo');
    Route::post('/addMemberRemark','MemberController@addMemberRemark')->name('addMemberRemark');
    Route::post('/deleteMemberRemark','MemberController@deleteMemberRemark')->name('deleteMemberRemark');
    Route::get('/getMemberLoginLog','MemberController@getMemberLoginLog')->name('getMemberLoginLog');
    Route::get('/getLoginIpList','MemberController@getLoginIpList')->name('getLoginIpList');
    
    Route::any('/memberAddPic','MemberController@memberAddPic')->name('memberAddPic');
    Route::post('/memberAddPicSave','MemberController@memberAddPicSave')->name('memberAddPicSave');
    Route::post('/memberPicDel','MemberController@memberPicDel')->name('memberPicDel');
    Route::post('/uploadMemberPicDoc','MemberController@uploadMemberPicDoc')->name('uploadMemberPicDoc');
    Route::post('/deleteMemberPicDoc','MemberController@deleteMemberPicDoc')->name('deleteMemberPicDoc');
    
    //会员等级设置
    Route::get('/memberLevel','MemberController@memberLevel')->name('memberLevel');
    Route::get('/addMemberLevel','MemberController@addMemberLevel')->name('addMemberLevel');
    Route::post('/createMemberLevel','MemberController@createMemberLevel')->name('createMemberLevel');
    Route::get('/memberLevelInfo','MemberController@memberLevelInfo')->name('memberLevelInfo');
    Route::post('/updateMemberLevelInfo','MemberController@updateMemberLevelInfo')->name('updateMemberLevelInfo');
    Route::post('/deleteMemberLevel','MemberController@deleteMemberLevel')->name('deleteMemberLevel');
