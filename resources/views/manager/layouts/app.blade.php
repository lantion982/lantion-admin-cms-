<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{config('app.name','重要提示：')}}</title>
    <link href="{{'/plus/bootstrap-3.3.6/css/bootstrap.min.css'}}" rel="stylesheet">
    <link href="{{'/css/font-awesome/css/font-awesome.css'}}" rel="stylesheet"/>
    <!--AdminLTE CSS-->
    <link href="{{'/plus/adminlte2.4.8/dist/css/AdminLTE.css'}}" rel="stylesheet">
    <link href="{{'/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'}}" rel="stylesheet">
    <!--common CSS-->
    <link href="{{'/css/common.css'}}" rel="stylesheet"/>
</head>
<body>
    <div id="app">
        @yield('content')
    </div>
    <!--jQuery-->
    <script src="{{'/plus/jQuery/jquery-1.11.3.min.js'}}"></script>
    <!-- Bootstrap 3.3.6 -->
    <script src="{{'/plus/bootstrap-3.3.6/js/bootstrap.min.js'}}"></script>
    <!--自定义共用js-->
    <script src="{{'/js/manager.common.js'}}"></script>
</body>
</html>
