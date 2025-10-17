<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>后台帐号</title>
</head>
@include('manager.layouts.common')
<link href="{{'/css/xadmin.css'}}" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="{{'/plus/layui/layui.js'}}"></script>
<script type="text/javascript" src="{{'/js/myadmin.js'}}"></script>
<body>
<div class="box box-info" style="padding:20px;">
    <div class="box-body" id="boxvue">
        <div class="box-body">
            {{Form::model($admins,['id'=>'postForm','name'=>'postForm'])}}
            @if(auth('admin')->user()->is_admin || auth('admin')->user()->can('assignRoles'))
                <div class="row">
                    <div class="form-group" style="">
                        <div style="width:75px;padding:0;float:left;">
                            <label for="roles">所属角色：</label>
                        </div>
                        <div class="atmaxlg">
                            <select id="roles_id" name="roles_id" class="form-control">
                                <option value="0">请选择角色</option>
                                @foreach($roles as $key=>$val)
                                    @if($admins&&$admins->roles)
                                        <option value="{{$key}}" @if($admins->roles[0]==$key) selected @endif>{{$val}}</option>
                                    @else
                                        <option value="{{$key}}">{{$val}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            @endif
            <div class="row">
                <div class="form-group">
                    {{Form::label('display_name','登录账号：',['class'=>'control-label'])}}
                    <div class="atmaxlg">
                        @if(empty($admins->display_name))
                            {{Form::text('login_name',old('login_name'),['id'=>'login_name','class'=>'form-control'])}}
                        @else
                            <input type="hidden" id="admin_id" name="admin_id" value="{{$admins->id}}">
                            {{Form::label('login_name',$admins->login_name,['class'=>'control-label','style'=>'text-align:left;'])}}
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    {{Form::label('display_name','管理昵称：',['class'=>'control-label'])}}
                    <div class="atmaxlg">
                        {{Form::text('display_name',null,['id'=>'display_name','class'=>'form-control'])}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    {{Form::label('login_pwd','登录密码：',['class'=>'control-label'])}}
                    <div class="atmaxlg">
                        {{Form::password('login_pwd',['id'=>'login_pwd','class'=>'form-control'])}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    {{Form::label('phone','联系电话：',['class'=>'control-label'])}}
                    <div class="atmaxlg">
                        {{Form::text('phone',null,['class'=>'form-control'])}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    {{Form::label('is_allow','启用状态：',['class'=>'control-label'])}}
                    <div class="atmaxlg">
                        {{Form::select('is_allow',config('enums.active_status'),old('is_allow'),['class'=>'form-control'])}}
                    </div>
                </div>
            </div>

            <div class="box-footer  pull-right mb3 mt3">
                @if(empty($admins->id))
                    <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="createAdmin()">新增账号</button>
                @else
                    <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="updateAdmin()">提交更新</button>
                @endif
                <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
            </div>
            {{Form::close()}}
        </div>
    </div>
</div>

<script>
    AdminLteStyle();
    function createAdmin(){
        if($('#roles_id').val()==0){
            layer.alert('请选择所属角色！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#login_name').val()==""){
            layer.alert('请输入登录帐号！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#display_name').val()==""){
            layer.alert('请输入管理昵称！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#login_pwd').val()==""){
            layer.alert('请输入登录密码！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#login_pwd').val().length<6||$('#login_pwd').val().length>16){
            layer.alert('请输入6-16位的登录密码！',{icon:2,closeBtn:0});
            return false;
        }
        $.ajax({
            type:'post',
            url:'/manager/createAdminInfo',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getAdmin(1);
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error:function(data){
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            },
        });
    }

    function updateAdmin(){
        if($('#roles_id').val()==0){
            layer.alert('请选择所属角色！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#login_pwd').val().length>0){
            if($('#login_pwd').val().length<6||$('#login_pwd').val().length>16){
                layer.alert('请输入6-16位的登录密码！',{icon:2,closeBtn:0});
                return false;
            }
        }

        $.ajax({
            type:'post',
            url:'/manager/updateAdminInfo',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getAdmin(parseInt($('#page',window.parent.document).val()));
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error:function(data){
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            },
        });
    }

</script>
</body>
</html>
