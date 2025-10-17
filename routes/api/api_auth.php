<?php
    /*
	|--------------------------------------------------------------------------
	| API -无需登录权限验证
	|--------------------------------------------------------------------------
	*/
    
    Route::get('getCaptcha','AuthController@getCaptcha');                                      //获取图片验证码
    Route::any('getPhoneVerify','AuthController@getPhoneVerify');                              //注册获取手机验证码
    Route::get('checkLoginName','AuthController@checkLoginName');                              //检查用户名是否可用
    Route::post('login','AuthController@login');                                               //获取登录token
    Route::post('refreshToken','AuthController@refreshToken');                                 //刷新token
    Route::post('restPassword','AuthController@restPassword');                                 //重置密码
    
    Route::get('getUserIp','CommonController@getUserIp');                                      //获取用户IP
    
    Route::any('uploadPic','PicController@uploadPic');                                         //保存图片
