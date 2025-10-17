<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 加密密钥
    |--------------------------------------------------------------------------
    |Passport在为生成安全访问令牌时使用加密密钥
	|默认情况下，密钥存储为本地文件，但是可以在更方便的时候通过环境变量进行设置。
    |
    */
    'private_key'   => env('PASSPORT_PRIVATE_KEY'),
    'public_key'    => env('PASSPORT_PUBLIC_KEY'),
	/*
	|--------------------------------------------------------------------------
	| Personal Access Client
	|--------------------------------------------------------------------------
	| If you enable client hashing, you should set the personal access client
	| ID and unhashed secret within your environment file. The values will
	| get used while issuing fresh personal access tokens to your users.
	*/
	/*'personal_access_client' => [
		'id'     => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
		'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
	],*/
	'api_url'       => 'http://pic.lantion9.cn/oauth/token',                  //请求令牌Token地址
	'client_id'     => '6',                                              //client_id
	'client_secret' => 'H5y5c4RAgj5h2n0KTtk7euD8Ux9hKY4LEm1CLUpb',       //client_secret
	'ag_cl_id'      => '7',                                              //代理client_id
	'ag_cl_secret'  => '5TOwcNQPfjpYZ9lxnYXKOma67jbZFRPjgZYo0KGZ',       //代理client_secret
	'token_time'    => 60,                                               //token 过期时间，单位分：钟
	'refresh_time'  => 15,                                               //refresh_token刷新时间，单位：天
];
