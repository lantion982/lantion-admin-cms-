<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>管理网后台</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- HTML5 容错 && Respond.js IE8 HTML5 多媒体支持 -->
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.min.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->
    @include('manager.layouts.common')
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    @include('manager.layouts.header')
    @include('manager.layouts.sidebar')
    <div class="content-wrapper">
        <div class="content-tabs">
            <button class="roll-nav roll-left tabLeft" onclick="scrollTabLeft()">
                <i class="fa fa-backward"></i>
            </button>
            <nav class="page-tabs menuTabs tab-ui-menu" id="tab-menu">
                <div class="page-tabs-content" style="margin-left: 0;">
                </div>
            </nav>
            <button class="roll-nav roll-right tabRight" onclick="scrollTabRight()">
                <i class="fa fa-forward" style="margin-left:3px;"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown tabClose" data-toggle="dropdown">
                    操作<i class="fa fa-caret-down" style="padding-left:3px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" style="min-width:128px;">
                    <li><a class="tabReload" href="javascript:refreshTab();">刷新</a></li>
                    <li><a class="tabCloseCurrent" href="javascript:closeCurrentTab();">关闭</a></li>
                    <li><a class="tabCloseAll" href="javascript:closeOtherTabs(true);">全部</a></li>
                    <li><a class="tabCloseOther" href="javascript:closeOtherTabs();">其他</a></li>
                </ul>
            </div>
            <button class="roll-nav roll-right fullscreen" ><i class="fa fa-arrows-alt"></i></button>
        </div>

        <div class="content-iframe " style="background-color: #ffffff;">
            <div class="tab-content " id="tab-content">
            </div>
        </div>
    </div>
    <div class="control-sidebar-bg"></div>
</div>
@include('manager.layouts.script')
</body>
</html>
