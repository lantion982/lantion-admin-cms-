<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>角色信息</title>
    @include('manager.layouts.common')
    <link href="{{'/css/xadmin.css'}}" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="{{'/plus/layui/layui.js'}}"></script>
    <script type="text/javascript" src="/js/myadmin.js"></script>
</head>
<body>
<div class="box box-info" style="padding:15px;">
    <div class="box-header with-border">
        <h3 class="box-title">角色信息</h3>
    </div>
    <div class="box-body">
        {{Form::model($roles,['class'=>'form-bordered','id'=>'postForm','name'=>'postForm' ])}}
        {{Form::hidden('id',old('id'))}}
        <div class="row mt2">
            <div class="col-sm-8">
                <label>角色名称：</label>
                {{Form::text('title',old('title'),['class'=>'form-control'])}}
            </div>
        </div>

        <div class="row mt2">
            <div class="col-sm-8">
                <label>角色描述：</label>
                {{Form::text('remarks',old('remarks'),['class'=>'form-control'])}}
            </div>
        </div>

        <div class="box-footer pull-right mt2">
            @if(empty($roles->id))
                <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="createRole()">新增角色
                </button>
            @else
                <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="updateRoleInfo()">提交保存
                </button>
            @endif
            <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
        </div>

    </div>
    {{Form::close()}}
</div>
<script>
    function createRole(){
        $.ajax({
            type:'post',
            url:'/manager/createRole',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getEntrustRole('1');
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error:function(data){
                layer.alert('新增失败！',{icon:2,closeBtn:0});
            }
        });
    }

    function updateRoleInfo(){
        $.ajax({
            type:'post',
            url:'/manager/updateRoleInfo',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getEntrustRole('1');
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error:function(data){
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            }
        });
    }
</script>
</body>
</html>
