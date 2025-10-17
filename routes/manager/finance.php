<?php
    /*
	|--------------------------------------------------------------------------
	| 财务管理
	|--------------------------------------------------------------------------
	*/
    Route::get('/memberAssets','FinanceController@memberAssets')->name('memberAssets');
    Route::get('/assetsInfo','FinanceController@assetsInfo')->name('assetsInfo');                                    //会员电子钱包信息
    Route::post('/updateAssetsInfo','FinanceController@updateAssetsInfo')->name('updateAssetsInfo');                 //更新会员状态
    Route::get('/balanceInfo','FinanceController@balanceInfo')->name('balanceInfo');                                 //额度操作
    Route::post('/updateMemberMoney','FinanceController@updateMemberMoney')->name('updateMemberMoney');              //更新额度操作
    Route::get('/moneyMovement','FinanceController@moneyMovement')->name('moneyMovement');                           //金额变动明细
    //Route::get('/bankAccounts','FinanceController@bankAccounts')->name('bankAccounts');
    //Route::get('/memberBankAccountInfo','FinanceController@memberBankAccountInfo')->name('memberBankAccountInfo');
    //Route::post('/updateMemberBankAccountInfo','FinanceController@updateMemberBankAccountInfo')->name('updateMemberBankAccountInfo');
    
    Route::get('/memberDrawApply','FinanceController@memberDrawApply')->name('memberDrawApply');
    Route::get('/memberDrawApplyInfo','FinanceController@memberDrawApplyInfo')->name('memberDrawApplyInfo');
    Route::post('/updateMemberDrawApplyInfo','FinanceController@updateMemberDrawApplyInfo')->name('updateMemberDrawApplyInfo');
    
     /*Route::get('/betQueue','FinanceController@betQueue')->name('betQueue');
	 Route::get('/reclaimMoney','FinanceController@reclaimMoney')->name('reclaimMoney');
	 Route::get('/memberGiftInfo','FinanceController@memberGiftInfo')->name('memberGiftInfo');
	 Route::post('/memberAddGiftByAdmin','FinanceController@memberAddGiftByAdmin')->name('memberAddGiftByAdmin');
	 Route::post('/updateMemberPoint','FinanceController@updateMemberPoint')->name('updateMemberPoint');
	 Route::get('/depositOnlineApply','FinanceController@depositOnlineApply')->name('depositOnlineApply');
	 Route::get('/depositManualApply','FinanceController@depositManualApply')->name('depositManualApply');
	 Route::get('/depositApplyInfo','FinanceController@depositApplyInfo')->name('depositApplyInfo');
	 Route::post('/updateDepositApplyInfo','FinanceController@updateDepositApplyInfo')->name('updateDepositApplyInfo');
	 Route::get('/memberWithdrawalThird','FinanceController@memberWithdrawalThird')->name('memberWithdrawalThird');
	 Route::post('/subMemberWithdrawalThird','FinanceController@subMemberWithdrawalThird')->name('subMemberWithdrawalThird');
	 Route::get('/queryMemberWithdrawalInfo','FinanceController@queryMemberWithdrawalInfo')->name('queryMemberWithdrawalInfo');
	 Route::get('/roomTransfer','FinanceController@roomTransfer')->name('roomTransfer');
	 Route::get('/queryRoomTransfer','FinanceController@queryRoomTransfer');
	 Route::post('/updateRoomTransfer','FinanceController@updateRoomTransfer')->name('updateRoomTransfer');
	 Route::get('/netDeposit','FinanceController@netDeposit')->name('netDeposit');
	 */

