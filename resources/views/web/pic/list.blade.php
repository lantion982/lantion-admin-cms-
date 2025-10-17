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
    <script src="/js/plmm/css3-mediaqueries.js"></script>
    <![endif]-->
    <!--CSS-->
    <link href="{{'/css/font-awesome/css/font-awesome.css'}}" rel="stylesheet"/>
    <link href="{{'/plus/adminlte2.4.8/dist/css/AdminLTE.css'}}" rel="stylesheet">
    <link href="{{'/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'}}" rel="stylesheet">
    <link href="{{'/plus/bootstrap-3.3.6/css/bootstrap.min.css'}}" rel="stylesheet">
    <link href="{{'/plus/view/viewer.min.css'}}" rel="stylesheet">
    <!--瀑布流样式-->
    <link href="/css/plmm/base.css" rel="stylesheet">
    <link href="/css/plmm/index.css" rel="stylesheet">
    <!--JS-->
    <script src="{{'/plus/jQuery/jquery-1.11.3.min.js'}}"></script>
    <script src="{{'/plus/bootstrap-3.3.6/js/bootstrap.min.js'}}"></script>
    <script src="/plus/view/viewer.min.js" type="text/javascript"></script>
    <script src="{{'/js/common.js'}}"></script>
    <!--瀑布流主插件函数必须-->
    <script src="/js/plmm/jQueryColor.js" type="text/javascript"></script>
    <script src="/js/plmm/jquery.masonry.min.js" type="text/javascript"></script>
    <!--扩展animate 函数动态效果-->
    <script src="/js/plmm/jQeasing.js" type="text/javascript"></script>

    <style>
        .row{height:calc(100% - 20px);margin:10px;}
        .topbox{height:100%;}
        .box{height:auto;}
        .box-body{height:calc(100% - 40px);}
        .box-body{padding:0;}
        .mg0{margin:0!important;}
        @media (max-width: 767px){
            .row {height:100%!important;}
            .box-body{padding:10px;}
        }
        .breadcrumb li{font-size:14px;}
        .nodata{font-size:16px;color:#f00;line-height:200px;padding:20px;text-align:center;}
        .fixlist{display:-webkit-flex;display:flex;flex-wrap:wrap;align-content:flex-start;}
        .path{flex:none;margin:10px;height:80px;}
        .path .pic{width:60px;margin:0 auto;}
        .path .pic img{width:100%}
        .path .name{margin-top:3px;text-align:center;}
        .path .name a{color:#000;text-decoration:none;font-size:16px;}
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
                            <a href="{{route('picindex')}}">首页</a>
                        </li>
                        @foreach($menu as $val)
                            <li><a href="{{route('piclist',['url'=>$val['path']])}}">{{$val['name']}}</a></li>
                        @endforeach
                    </ol>
                </div>
                <div class="row fixlist">
                    @foreach($dirs as $val)
                        <div class="path">
                            <div class="pic"><a href="{{route('piclist',['url'=>$val['path'],'name'=>$val['name']])}}"><img src="/images/path.png"></a></div>
                            <div class="name"><a href="{{route('piclist',['url'=>$val['path'],'name'=>$val['name']])}}">{{$val['name']}}</a></div>
                        </div>
                    @endforeach
                </div>
                <div class="content">
                    @if($count==0)
                    <div class="nodata">该目录暂无图片！</div>
                    @endif
                    <div class="waterfull clearfloat" id="waterfull">
                        <ul id="piclist">
                        @foreach($list as $val)
                        <li class="item">
                            <a href="javascript:" class="a-img">
                                <img src="{{$val['path']}}" alt="{{$val['name']}}">
                            </a>
                            <div class="qianm clearfloat">
                                <span class="sp1">{{$val['size']}}K</span>
                                <span class="sp2" onclick="delpic('{{$val['path']}}')">删除</span>
                                <span class="sp3">{{$val['time']}}&nbsp;</span>
                            </div>
                        </li>
                        @endforeach
                        </ul>
                    </div>
                </div>
                <div id="imloading" class="loading">加载中.....</div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    @if($count>0)
    //基于masonry的瀑布流，很是简单的，通过扩展animate,能实现瀑布流布局的晃动、弹球等效果。
    var viewer = new Viewer(document.getElementById('piclist'),{
        url:'src'
    });

    $(function(){
        var container = $('.waterfull ul');
        var loading   = $('#imloading');
        var page      = 1;
        //var counts    = '{$count}';
        //初始化loading状态
        loading.data('on',true);

        //判断瀑布流最大布局宽度，最大为1280
        function tores(){
            var tmpWid = $(window).width();
            if(tmpWid>1280){
                tmpWid = 1280;
            }else{
                var column = Math.floor(tmpWid/320);
                console.log(column);
                tmpWid = column*320;
            }
            $('.waterfull').width(tmpWid);
        }
        tores();
        $(window).resize(function(){
            tores();
        });
        container.imagesLoaded(function(){
            container.masonry({
                columnWidth:320,
                itemSelector:'.item',
                isFitWidth:true,    //是否根据浏览器窗口大小自动适应默认false
                isAnimated:true,    //是否采用jquery动画进行重拍版
                isRTL:false,        //设置布局的排列方式，即：定位砖块时，是从左向右排列还是从右向左排列。默认值为false，即从左向右
                isResizable:true,   //是否自动布局默认true
                animationOptions:{
                    duration:800,
                    easing:'easeInOutBack', //如果你引用了jQeasing这里就可以添加对应的动态动画效果，如果没引用删除这行，默认是匀速变化
                    queue:true              //是否队列，从一点填充瀑布流
                }
            });
        });
        //用sqlJson来模拟数据
        //var sqlJson = JSON.parse('{$json}');
        $(window).scroll(function(){
            /*if(sqlJson.length==0){
                loading.data('on',false).fadeIn(800);
                loading.text('没有更多图片了...');
                return;
            }*/
            if(!loading.data('on')){
                return;
            }
            //计算瀑布流块中距离顶部最大，进而在滚动条滚动时，来进行请求
            var itemNum = $('#waterfull').find('.item').length;
            var itemArr = [];
            itemArr[0] = $('#waterfull').find('.item').eq(itemNum-1).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
            itemArr[1] = $('#waterfull').find('.item').eq(itemNum-2).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
            itemArr[2] = $('#waterfull').find('.item').eq(itemNum-3).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;
            itemArr[3] = $('#waterfull').find('.item').eq(itemNum-4).offset().top+$('#waterfull').find('.item').eq(itemNum-1)[0].offsetHeight;

            var maxTop = Math.max.apply(null,itemArr);
            //加载更多数据
            if(maxTop<($(window).height()+$(window).scrollTop())){
                loading.data('on',false).fadeIn(800);
                let posturl = '{{route('piclist',['url'=>$path])}}';
                $.ajax({type:'post',url:posturl,data:{'page':page,'_token':'{{csrf_token()}}'},dataType:'json',success:function(data){
                        if(data.status==1){
                            if(data.content.count>0){
                                let sqlJson = data.content.json;
                                var html = '';
                                var tem  = '';
                                for(var i in sqlJson){
                                    html += '<li class="item"><a href="javascript:" class="a-img"><img src="'+sqlJson[i].path+'"></a>';
                                    tem   = '<span class="sp1">\'+sqlJson[i].size+\'k</span>';
                                    tem  += '<span class="sp2" onclick="delpic(\''+sqlJson[i].path+'\')">删除</span>';
                                    tem  += '<span class="sp3">'+sqlJson[i].time+'</span>';
                                    html += '<div class="qianm clearfloat">'+tem+'</div></li>';
                                }
                                //模拟ajax请求数据时延时800毫秒
                                var time = setTimeout(function(){
                                    $(html).find('img').each(function(index){
                                        loadImage($(this).attr('src'));
                                    });
                                    var $newElems = $(html).css({opacity:0}).appendTo(container);
                                    $newElems.imagesLoaded(function(){
                                        $newElems.animate({opacity:1},800);
                                        container.masonry('appended',$newElems,true);
                                        loading.data('on',true).fadeOut();
                                        clearTimeout(time);
                                    });
                                },800);
                            }else{
                                loading.text('没有更多图片了...');
                                return false;
                            }

                        }else{
                            layer.alert(data.msg,{icon:2,closeBtn:0,offset:['50px']});
                            return false;
                        }
                    },error:function(data){
                        layer.alert('网络连接失败，请稍后重试！',{icon:2,closeBtn:0,offset:['50px']});
                    }
                });
                page ++;
                viewer.update();
                /*(function(sqlJson){
                    //后台返回的数据条数来判断是否已经加载完毕
                    if(itemNum>counts){
                        loading.text('没有更多图片了...');
                    }else{
                        var html = '';
                        for(var i in sqlJson){
                            html += '<li class="item"><a href="javascript:" class="a-img"><img src="'+sqlJson[i].path+'"></a>';
                            html += '<div class="qianm clearfloat"><span class="sp1">'+sqlJson[i].size+'k</span>';
                            html += '<span class="sp2" onclick="delpic(\''+sqlJson[i].path+'\')">删除</span><span class="sp3">'+sqlJson[i].time+'</span></div></li>';
                        }
                        //模拟ajax请求数据时延时800毫秒
                        var time = setTimeout(function(){
                            $(html).find('img').each(function(index){
                                loadImage($(this).attr('src'));
                            });
                            var $newElems = $(html).css({opacity:0}).appendTo(container);
                            $newElems.imagesLoaded(function(){
                                $newElems.animate({opacity:1},800);
                                container.masonry('appended',$newElems,true);
                                loading.data('on',true).fadeOut();
                                clearTimeout(time);
                            });
                        },800);
                    }
                })(sqlJson);*/
            }

        });

        function loadImage(url){
            var img = new Image();
            img.src = url;
            if(img.complete){
                return img.src;
            }
            img.onload = function(){
                return img.src;
            };
        }

        var rbgB = ['#50c0f5','#F0A644','#F28386','#8BD38B','#F21082','#FF3300'];
        $('#waterfull').on('mouseover','.item',function(){
            var random = Math.floor(Math.random()*6);
            $(this).stop(true).animate({'backgroundColor':rbgB[random]},1000);
        });
        $('#waterfull').on('mouseout','.item',function(){
            $(this).stop(true).animate({'backgroundColor':'#fff'},1000);
        });
    });
    @endif
    function delpic(url){
        $.ajax({type:'post',url:'{{route("picdel")}}',data:{'url':url,'_token':'{{csrf_token()}}'},dataType:'json',success:function(data){
                if(data.status==1){
                    layer.alert(data.msg,{icon:1,closeBtn:0,offset:['50px']},function(){
                        location.href='{{route('piclist',['url'=>$path])}}';
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0,offset:['50px']});
                    return false;
                }
            },error:function(data){
                layer.alert('网络连接失败，请稍后重试！',{icon:2,closeBtn:0,offset:['50px']});
            }
        });
    }
</script>
</body>
</html>
