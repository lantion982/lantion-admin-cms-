<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Debugbar 设置
     |--------------------------------------------------------------------------
     |
     | DebugBar 默认是启用的.
     | 可以设置被忽略的URI  (如：'api/*')
     |
     */

    //'enabled' => env('APP_DEBUG', null),
    'enabled' => false,
    'except'  => [
    ],

    /*
     |--------------------------------------------------------------------------
     | 存储设置
     |--------------------------------------------------------------------------
     |
     | DebugBar 数据存储：session/ajax 会话.
     | You can disable this, so the debugbar stores data in headers/session,
     | but this can cause problems with large data collectors.
     | 默认情况下，是使用文件存储.
     | 也可以使用：Redis 和 PDO. 使用PDO, run the package migrations first
     |
     */
    'storage' => [
        'enabled'    => true,
        'driver'     => 'file',                                 // redis, file, pdo, custom
        'path'       => storage_path('debugbar'),         // For file driver
        'connection' => null,                                   // Leave null for default connection (Redis/PDO)
        'provider'   => ''                                      // Instance of StorageInterface for custom driver
    ],

    /*
     |--------------------------------------------------------------------------
     | 供应商
     |--------------------------------------------------------------------------
     |
     | 默认情况下包含供应商文件，但可以将其设置为false。.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
     | and for js: jquery and and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery是不能与现有的jQuery脚本冲突。
     |
     */

    'include_vendors' => true,

    /*
     |--------------------------------------------------------------------------
     | 捕获的Ajax请求
     |--------------------------------------------------------------------------
     |
     | DebugBar可以捕捉的Ajax请求，并显示出来。如果你不想要这个（因为IE错误）,
     | 你可以禁用此选项，禁止向headers 发送数据.
     |
     | 可选,你也可以发送 ServerTiming headers on ajax requests for the Chrome DevTools.
     */

    'capture_ajax' => true,
    'add_ajax_timing' => false,

    /*
     |--------------------------------------------------------------------------
     | 自定义错误 Handler for 警告信息
     |--------------------------------------------------------------------------
     |
     | 当启用时，DebugBar 将在消息选项卡Symfony组件显示该警告
     |
     */
    'error_handler' => false,
    
    /*
     |--------------------------------------------------------------------------
     | 装置整合
     |--------------------------------------------------------------------------
     |
     | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
     | Extension, without the server-side code. It uses Debugbar collectors instead.
     |
     */
    'clockwork' => false,

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'phpinfo'         => true,  // Php version
        'messages'        => true,  // Messages
        'time'            => true,  // Time Datalogger
        'memory'          => true,  // Memory usage
        'exceptions'      => true,  // Exception displayer
        'log'             => true,  // Logs from Monolog (merged in messages if enabled)
        'db'              => true,  // Show database (PDO) queries and bindings
        'views'           => true,  // Views with their data
        'route'           => true,  // Current route information
        'auth'            => true, // Display Laravel authentication status
        'gate'            => true, // Display Laravel Gate checks
        'session'         => true,  // Display session data
        'symfony_request' => true,  // Only one can be enabled..
        'mail'            => true,  // Catch mail messages
        'laravel'         => false, // Laravel version and environment
        'events'          => false, // All events fired
        'default_request' => false, // Regular or special Symfony request logger
        'logs'            => false, // Add the latest log messages
        'files'           => false, // Show the included files
        'config'          => false, // Display config settings
        'cache'           => false, // Display cache events
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'auth' => [
            'show_name' => true,   // Also show the users name/email in the debugbar
        ],
        'db' => [
            'with_params'       => true,   // Render SQL with the parameters substituted
            'backtrace'         => true,   // Use a backtrace to find the origin of the query in your files.
            'timeline'          => false,  // Add the queries to the timeline
            'explain' => [                 // Show EXPLAIN output on queries
                'enabled' => false,
                'types' => ['SELECT'],     // ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
            ],
            'hints'             => true,    // Show hints for common mistakes
        ],
        'mail' => [
            'full_log' => false
        ],
        'views' => [
            'data' => false,    //Note: Can slow down the application, because the data can be quite large..
        ],
        'route' => [
            'label' => true  // show complete route on bar
        ],
        'logs' => [
            'file' => null
        ],
        'cache' => [
            'values' => true // collect cache values
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Inject Debugbar in Response
     |--------------------------------------------------------------------------
     |
     | Usually, the debugbar is added just before </body>, by listening to the
     | Response after the App is done. If you disable this, you have to add them
     | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
     |
     */

    'inject' => true,

    /*
     |--------------------------------------------------------------------------
     | DebugBar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by DebugBar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => '_debugbar',

    /*
     |--------------------------------------------------------------------------
     | DebugBar route domain
     |--------------------------------------------------------------------------
     |
     | By default DebugBar route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'route_domain' => null,
];
