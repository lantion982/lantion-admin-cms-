<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>管理平台登录</title>
    <meta name="keywords" content="管理平台登录">
    <meta name="description" content="管理平台登录">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="{{"/css/font.css?".time()}}" type="text/css">
    <link rel="stylesheet" href="{{"/css/xadmin.css?".time()}}" type="text/css">
    <link rel="stylesheet" href="{{"/css/login/login.css?".time()}}" type="text/css">
    <link rel="stylesheet" href="{{"/css/login/slideshow.css?".time()}}" type="text/css">
    <script type="text/javascript" src="{{"/plus/jQuery/jquery-3.2.1.min.js?".time()}}"></script>
    <script type="text/javascript" src="{{"/plus/layui/layui.js?".time()}}"></script>
    <script type="text/javascript" src="{{"/js/myadmin.js?".time()}}"></script>
</head>
<body id="body" bgcolor="#0170E4">
<div class="slideshow">
    <div class="slideshow-image" style="background-image: url('/images/login/bg1.jpg')"></div>
    <div class="slideshow-image" style="background-image: url('/images/login/bg2.jpg')"></div>
    <div class="slideshow-image" style="background-image: url('/images/login/bg3.jpg')"></div>
    <div class="slideshow-image" style="background-image: url('/images/login/bg4.jpg')"></div>
</div>
<div class="main-body">
    <!-- 登录界面 -->
    <div class="new-login">
        <div class="top">
            管理平台登录
            <span class="bg1"></span>
            <span class="bg2"></span>
        </div>
        <div class="bottom">
            <form class="layui-form" id="loginform" name="loginform" method="POST">
                {{csrf_field()}}
                <div class="center">
                    <div class="item">
                        <span class="icon icon-2"></span>
                        <input type="text" id="login_name" name="login_name" placeholder="请输入登录账号" maxlength="16" lay-verify="loginname" value="superadmin">
                    </div>
                    <div class="item">
                        <span class="icon icon-3"></span>
                        <input type="password" id="password" name="password" placeholder="请输入登录密码" maxlength="20" lay-verify="loginpwd" value="9841877a">
                    </div>
                    <div class="item">
                        <span class="icon icon-7"></span>
                        <input type="text" id="captcha" name="captcha" placeholder="请输入验证码" maxlength="4" lay-verify="captcha">
                        <img id="validateImg" class="validateImg" src="{{captcha_src()}}" onclick="this.src='/captcha/default?'+Math.random()" alt="验证码"
                            title="点击刷新验证码">
                    </div>
                </div>
                <div class="tip">
                    <label for="remember"></label>
                    <input type="checkbox" id="remember" name="remember" lay-skin="primary">
                    <span class="login-tip">记住帐号</span>
                    <a href="#" class="no-pwd">忘记密码？</a>
                </div>
                <div style="text-align:center;">
                    <button class="btnsumbit" lay-filter="login" lay-submit id="loginBtn">
                        立即登录
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{--{{dd(bcrypt('9841877'))}}--}}
<div class="foot">
    <a class="foot_txt">Copy Right @2020-2030 All Right Reserver.</a>
</div>
<script>
    layui.use(['form','layer'],function(){
        $ = layui.jquery;
        let form = layui.form,layer = layui.layer;

        //自定义验证规则
        form.verify({
            loginname:function(value){
                if(value.length<1){
                    return '请输入登录帐号，再进行登录操作！';
                }
            },
            loginpwd:function(value){
                if(value.length<1){
                    return '请输入登录密码，再进行登录操作！';
                }
            },
            captcha:function(value){
                if(value.length<1){
                    return '请输入验证码，再进行登录操作！';
                }
            }
        });

        //监听提交
        form.on('submit(login)',function(data){
            ajaxurl = '{{route("mylogin")}}';
            $.ajax({
                type:'POST',
                url:ajaxurl,
                data:$('#loginform').serialize(),
                dataType:'json',
                success:function(data){
                    if(data['status']==0){
                        parent.location.reload();
                    }else{
                        layer.alert(data['msg'],{icon:2,time:2000,title:'操作提示'});
                        $('#validateImg').attr('src','/captcha/default?'+Math.random());
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败!',{icon:2,time:2000,title:'操作提示'});
                    $('#validateImg').attr('src','/captcha/default?'+Math.random());
                }
            });
            return false;
        });
    });
</script>
</body>
</html>
