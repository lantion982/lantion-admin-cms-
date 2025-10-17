<?php
/*
|--------------------------------------------------------------------------
| 日志管理相关路由
|--------------------------------------------------------------------------
*/

Route::get('/ipRegisterLogin','LogController@ipRegisterLogin')->name('ipRegisterLogin');
Route::post('/ipReset','LogController@ipReset')->name('ipReset');
Route::post('/addIpBlack','LogController@addIpBlack')->name('addIpBlack');

Route::get('/logLogin','LogController@logLogin')->name('logLogin');
Route::get('/logLoginMember','LogController@logLoginMember')->name('logLoginMember');                               //会员登录日志
Route::get('/logLoginAdmin','LogController@logLoginAdmin')->name('logLoginAdmin');                                  //管理员登录日志
Route::get('/logOperation','LogController@logOperation')->name('logOperation');                                     //操作日志
//手机短信
Route::get('/listSMS','LogController@listSMSInfo')->name('listSMS');
Route::get('/addSMSInfo','LogController@addSMSInfo')->name('addSMSInfo');
Route::post('/createSMSInfo','LogController@createSMSInfo')->name('createSMSInfo');
Route::get('/editSMSInfo','LogController@editSMSInfo')->name('editSMSInfo');
Route::post('/updateSMSInfo','LogController@updateSMSInfo')->name('updateSMSInfo');

