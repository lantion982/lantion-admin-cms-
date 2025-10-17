<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel{
   //全局HTTP中间件堆栈。 这些中间件在每个请求期间运行
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
	    \Barryvdh\Cors\HandleCors::class,
	    \Illuminate\Session\Middleware\StartSession::class,
    ];

    //路由中间件分组.
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
	        \App\Http\Middleware\PassportCustomProviderAccessToken::class,
        ],

        'api' => [
	        \App\Http\Middleware\EncryptCookies::class,
	        \Illuminate\Session\Middleware\StartSession::class,
            'throttle:60,1',
            'bindings',
	        \Barryvdh\Cors\HandleCors::class,
        ],
    ];
	
	//路由中间件
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
	    'auth.admin' => \App\Http\Middleware\AdminAuthMiddleware::class,
	    'auth.member' => \App\Http\Middleware\MemberAuthMiddleware::class,
        'auth.agent' => \App\Http\Middleware\AgentAuthMiddleware::class,
	    'area' => \App\Http\Middleware\IndexMiddleware::class,
	    'manageWhite' => \App\Http\Middleware\IndexWebMiddleware::class,
	    'EntrustAuthorize'    => \App\Http\Middleware\EntrustAuthorize::class,
	    'SingleMiddleware' => \App\Http\Middleware\SingleMiddleware::class,
	    'passport.multiAuth' => \App\Http\Middleware\PassportCustomProvider::class,
	    'passport.refresh' => \App\Http\Middleware\RefreshTokenMiddleware::class,
	    'passport.refreshAndSingle' => \App\Http\Middleware\RefreshTokenAndSingleMiddleware::class,
    ];

    //The priority-sorted list of middleware.
    //This forces non-global middleware to always be in the given order.
   
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
