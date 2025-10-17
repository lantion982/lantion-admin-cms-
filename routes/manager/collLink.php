<?php
    /*
	|--------------------------------------------------------------------------
	| 网址采集
	|--------------------------------------------------------------------------
	*/
    Route::get('collLink','CollLinkController@index')->name('collLink');
    Route::get('zkDown','CollLinkController@zkDown')->name('zkDown');
    Route::get('zkNews','CollLinkController@zkNews')->name('zkNews');
