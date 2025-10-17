<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>{{config('app.name','重要提示：')}}</title>
    <link rel="stylesheet" href="{{'/plus/bootstrap-3.3.6/css/bootstrap.min.css'}}">
    <link href="{{'/css/font-awesome/css/font-awesome.css'}}" rel="stylesheet" />
    <link href="{{'/plus/adminlte2.4.8/dist/css/AdminLTE.css'}}" rel="stylesheet" >
    <link href="{{'/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'}}" rel="stylesheet" >
    <link href="{{'/css/common.css?'.time()}}" rel="stylesheet" />
</head>
<body>
<div id="app">
    @yield('content')
</div>
<script src="{{'/plus/jQuery/jquery-1.11.3.min.js?'.time()}}"></script>
<script src="{{'/plus/bootstrap-3.3.6/js/bootstrap.min.js'}}"></script>
<script src="{{'/js/common.js?'.time()}}"></script>
</body>
</html>
