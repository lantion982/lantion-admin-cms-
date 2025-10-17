<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>图库展示</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- HTML5 容错 && Respond.js IE8 HTML5 多媒体支持 -->
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.min.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->
    <!--CSS-->
    <link href="{{'/css/font-awesome/css/font-awesome.css'}}" rel="stylesheet"/>
    <link href="{{'/plus/adminlte2.4.8/dist/css/AdminLTE.css'}}" rel="stylesheet">
    <link href="{{'/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'}}" rel="stylesheet">
    <link href="{{'/css/common.css'}}" rel="stylesheet"/>
    <link href="{{'/plus/bootstrap-3.3.6/css/bootstrap.min.css'}}" rel="stylesheet">
    <!--JS-->
    <script src="{{'/plus/jQuery/jquery-1.11.3.min.js'}}"></script>
    <script src="{{'/plus/bootstrap-3.3.6/js/bootstrap.min.js'}}"></script>
    <script src="{{'/js/common.js'}}"></script>
    <style>
        .row{height:calc(100% - 20px);margin:10px;}
        .topbox,.box{height:100%;}
        .box-body{height:calc(100% - 40px);}
        .box-body{padding:0;}
        .mg0{margin:0!important;}
        @media (max-width: 767px){
            .row {height:100%!important;}
            .box-body{padding:10px;}
        }
        .fixlist{display:-webkit-flex;display:flex;flex-wrap:wrap;align-content:flex-start}
        .path{flex:none;margin:10px;height:80px;}
        .path .pic{width:60px;margin:0 auto;}
        .path .pic img{width:100%}
        .path .name{margin-top:3px;font-size:16px;text-align:center;}
        .path .name a{color:#000;text-decoration:none;}
    </style>
</head>
<body>
<div class="row">
    <div class="topbox">
        <div class="box box-info box-solid mg0">

            <div class="box-header">
                <h5 class="box-title">{{$username}} | 相册展示</h5>
                <div class="box-tools pull-right">
                    <button type="button" title="刷新" class="btn btn-box-tool" onclick="location.reload();">
                        <i class="fa fa-refresh"></i></button>
                    <button type="button" title="展开&收缩" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="box-body">
                <div class="menu">
                    <ol class="breadcrumb mb1">
                        <li>
                            <a href="{{route('picindex')}}">目录列表</a>
                        </li>
                        <li>首页</li>
                    </ol>
                </div>
                <div class="row fixlist">
                    @foreach($list as $val)
                    <div class="path">
                        <div class="pic"><a href="{{route('piclist',['url'=>$val['path']])}}"><img src="/images/path.png"></a></div>
                        <div class="name"><a href="{{route('piclist',['url'=>$val['path']])}}">{{$val['name']}}</a></div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
