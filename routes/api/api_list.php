<?php
/*
|--------------------------------------------------------------------------
| API- 需登录权限验证
|--------------------------------------------------------------------------
*/
//游戏相关
Route::get('getBetResult','GameController@getBetResult');                               //获取最新开奖结果
Route::get('getGameBase','GameController@getGameBase');                                 //获取最新一期开盘信息
Route::get('getGameRate','GameController@getGameRate');                                 //获取赔率
Route::get('getNewBetList','GameController@getNewBetList');                             //获取最新投注信息
Route::post('postBetData','GameController@postBetData');                                //提交投注信息

Route::get('getTeMaRate','GameController@getTeMaRate');                                 //获取特码号码数组及赔率
Route::get('getTeXiaoRate','GameController@getTeXiaoRate');                             //获取特肖头尾数玩法数组及赔率
Route::get('getRateByGroup','GameController@getRateByGroup');                           //获取玩法数组及赔率
Route::get('getHeXiaoRate','GameController@getHeXiaoRate');                             //获取合肖玩法数组及赔率
Route::get('getZeMaTeRate','GameController@getZeMaTeRate');                             //获取正码特玩法数组及赔率
Route::get('getZeMa16Rate','GameController@getZeMa16Rate');                             //获取正码16玩法数组及赔率
Route::get('getZeXiaoRate','GameController@getZeXiaoRate');                             //获取正肖总肖玩法数组及赔率
Route::get('getPTYXWSRate','GameController@getPTYXWSRate');                             //获取平特一肖玩法数组及赔率
Route::get('getWuXingRate','GameController@getWuXingRate');                             //获取五行7色波玩法数组及赔率
Route::get('getLianXiaoRate','GameController@getLianXiaoRate');                         //获取连肖玩法数组及赔率
Route::get('getLianWeiRate','GameController@getLianWeiRate');                           //获取连尾玩法数组及赔率

//用户中心
Route::get('getUserInfo','MemberController@getUserInfo');
Route::post('putUserPassword','MemberController@putUserPassword');
Route::post('addLogin','MemberController@addLogin');

/*Route::post('putUserInfo','MemberController@putUserInfo');
Route::post('putSingleInfo','MemberController@putSingleInfo');
Route::post('checkUserPhone','MemberController@checkUserPhone');
Route::post('putUserPhone','MemberController@putUserPhone');
Route::post('putTradePwd','MemberController@putTradePwd');*/

//记录相关
Route::get('getMvmtList','RecordController@getMvmtList');
Route::get('getBetList','RecordController@getBetList');

//提款相关
Route::get('getBindCard','WithDrawalController@getBindCard');
Route::post('addBindCard','WithDrawalController@addBindCard');
Route::post('updateBindCard','WithDrawalController@updateBindCard');
Route::post('delBindCard','WithDrawalController@delBindCard');
Route::post('defaultBank','WithDrawalController@defaultBank');
Route::get('listWithdraw','WithDrawalController@listWithdraw');
Route::get('getWithdraw','WithDrawalController@getWithdraw');
Route::post('addWithdraw','WithDrawalController@addWithdraw');
Route::post('cancelWithdraw','WithDrawalController@cancelWithdraw');

/*Route::any('getPoneCode','MemberController@getPoneCode');
Route::any('changPhoneVerify','MemberController@changPhoneVerify');
Route::any('newPhoneVerify','MemberController@newPhoneVerify');
Route::get('getMessage','MemberController@getMessage');
Route::get('getTopMessage','MemberController@getTopMessage');
Route::any('readMessage','MemberController@readMessage');
Route::post('delMessage','MemberController@delMessage');*/