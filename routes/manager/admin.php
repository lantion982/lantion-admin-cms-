<?php
    /*
	|--------------------------------------------------------------------------
	| 后台帐号&权限管理 相关路由
	|--------------------------------------------------------------------------
	*/
    //后台网帐号
    Route::get('/adminList','AdminController@adminList')->name('adminList');
    Route::get('/addAdminInfo','AdminController@addAdminInfo')->name('addAdminInfo');
    Route::post('/createAdminInfo','AdminController@createAdminInfo')->name('createAdminInfo');
    Route::get('/adminInfo','AdminController@adminInfo')->name('adminInfo');
    Route::post('/updateAdminInfo','AdminController@updateAdminInfo')->name('updateAdminInfo');
    Route::post('/delAdminInfo','AdminController@delAdminInfo')->name('delAdminInfo');
    Route::post('/updateAdminStatus','AdminController@updateAdminStatus')->name('updateAdminStatus');
    Route::get('/adminPassword','AdminController@adminPassword');
    Route::post('/updateAdminPassword','AdminController@updateAdminPassword');
    
    //角色定义
    Route::get('/role','AdminController@role')->name('role');
    Route::post('/updateSortRole','AdminController@updateSortRole')->name('updateSortRole');
    Route::get('/addRole','AdminController@addRole')->name('addRole');
    Route::post('/createRole','AdminController@createRole')->name('createRole');
    Route::get('/roleInfo','AdminController@roleInfo')->name('roleInfo');
    Route::post('/updateRoleInfo','AdminController@updateRoleInfo')->name('updateRoleInfo');
    Route::post('/deleteRole','AdminController@deleteRole')->name('deleteRole');
    
    //权限定义
    Route::get('/permission','AdminController@permission')->name('permission');
    Route::get('/addPermission','AdminController@addPermission')->name('addPermission');
    Route::get('/addSubPermission','AdminController@addSubPermission')->name('addSubPermission');
    Route::post('/createPermission','AdminController@createPermission')->name('createPermission');
    Route::get('/permissionInfo','AdminController@permissionInfo')->name('permissionInfo');
    Route::post('/updatePermissionInfo','AdminController@updatePermissionInfo')->name('updatePermissionInfo');
    Route::post('/deletePermission','AdminController@deletePermission')->name('deletePermission');
    
    //权限页面的func配置
    Route::get('/permPageFunc','AdminController@permPageFunc')->name('permPageFunc');
    Route::post('/updateSortPermPageFunc','AdminController@updateSortPermPageFunc')->name('updateSortPermPageFunc');
    
    //角色的权限关联
    Route::get('/rolePermission','AdminController@rolePermission')->name('rolePermission');
    Route::post('/updateRolePermission','AdminController@updateRolePermission')->name('updateRolePermission');
