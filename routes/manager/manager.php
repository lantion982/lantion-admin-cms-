<?php
    /*
	|--------------------------------------------------------------------------
	| 后台路由
	|--------------------------------------------------------------------------
	*/
    Route::get('/','IndexController@index');
    Route::get('/home','IndexController@index');
    Route::any('/dashboard','IndexController@dashboard')->name('dashboard');
    Route::get('/logs','\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('logs');
    Route::any('/checkInfo','IndexController@checkInfo');
    
    //后台帐号/权限
    require_once __DIR__.'/admin.php';
    //会员管理
    require_once __DIR__.'/member.php';
    //财务管理
    require_once __DIR__.'/finance.php';
    //系统设置
    require_once __DIR__.'/config.php';
    //新闻公告
    require_once __DIR__.'/news.php';
    //日志管理
    require_once __DIR__.'/log.php';
    //客户管理-意见反馈
    require_once __DIR__.'/customer.php';
    //采集
    require_once __DIR__.'/collLink.php';
    //网址
    require_once __DIR__.'/link.php';
    //下载
    require_once __DIR__.'/down.php';

