<!DOCTYPE html>
<html>
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>角色信息</title>
	</head>
	@include('manager.layouts.common')
	<link href="{{'/css/xadmin.css'}}" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="{{'/plus/layui/layui.js'}}"></script>
	<script type="text/javascript" src="/js/myadmin.js"></script>
<style>
    .form-group {margin-bottom:5px;}
    .form-control {display: inline;}
</style>
<body>
<div class="box box-info">
    <div class="box-body" style="padding:15px;">
        {{Form::model($permission,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
        <input type="hidden" id="id" name="id" value="{{$permission->id??0}}">
        <div class="row">
			<div class="col-sm-12">
				<label for="parent_id">所属菜单：</label>
				@if(empty($topPermissions))
					@if(empty($permission->parent_id))
						{{Form::hidden('parent_id',$parentPermission->id)}}
						{{Form::text('parent_name',$parentPermission->title,['class'=>'form-control','ReadOnly'=>'true','style'=>'width:300px;'])}}
					@else
						{{Form::hidden('parent_id',old('parent_id'))}}
						{{Form::text('parent_name',$parentPermission->title,['class'=>'form-control','ReadOnly'=>'true','style'=>'width:300px;'])}}
					@endif
				@else
					{{Form::select('parent_id',$topPermissions,null,['class'=>'form-control','style'=>'width:300px;'])}}
				@endif
			</div>
			<div class="col-sm-12">
				<label for="name">权限路由：</label>
				{{Form::text('name',old('name'),['class'=>'form-control','style'=>'width:300px;'])}}
			</div>
			<div class="col-sm-12">
				<label for="title">显示名称：</label>
				{{Form::text('title',old('title'),['class'=>'form-control','style'=>'width:300px;'])}}
			</div>
			<div class="col-sm-12">
				<label for="icon">相关图标：</label>
				{{Form::text('icon',old('icon'),['class'=>'form-control','style'=>'width:300px;'])}}
			</div>
			<div class="col-sm-12">
				<label for="parent_id">是否显示：</label>
				@if(isset($permission->is_show))
                    <input type="radio" name="is_show" id="is_show" value="1" class="mt1" @if($permission->is_show==1) checked @endif>显示&nbsp;
					<input type="radio" name="is_show" id="is_show" value="0" class="mt1" @if($permission->is_show==0) checked @endif>不显示
				@else
                    <input type="radio" name="is_show" id="is_show" value="1" class="mt1" checked>显示&nbsp;
					<input type="radio" name="is_show" id="is_show" value="0" class="mt1">不显示
				@endif
			</div>
			<div class="col-sm-12">
				<label for="ptype">权限类型：</label>
				{{Form::select('ptype',config('enums.auth_type'),old('ptype'),['class'=>'form-control','style'=>'width:300px;'])}}
			</div>
			<div class="col-sm-12">
				<label for="sorts">排序序号：</label>
				{{Form::text('sorts',old('sorts'),['class'=>'form-control','style'=>'width:300px;'])}}
			</div>
        </div>
        {{Form::close()}}
    </div>
    <div class="box-footer pull-right mt2 mb2">
        @if(empty($permission->id))
            <button type="button" class="btn btn-info" id="btn-Add" onclick="createPermission()">新增权限</button>
        @else
            <button type="button" class="btn btn-info" id="btn-Update" onclick="updatePermissionInfo()">提交更新</button>
        @endif
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>
<script>
    function createPermission(){
        if($('#name').val()=='') {
	        layer.alert('请输入权限路由！',{icon:2,closeBtn:0});
        	return false;
        }
	    if($('#display_name').val()=='') {
		    layer.alert('请输入显示名称！',{icon:2,closeBtn:0});
		    return false;
	    }
        $.ajax({
            type: 'post',
            url: '/manager/createPermission',
            data: $('#postForm').serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.location.reload();
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error: function (data) {
                layer.alert('新增失败！',{icon:2,closeBtn:0});
            }
        });
    }

    function updatePermissionInfo(){
        $.ajax({
            type: 'post',
            url: '/manager/updatePermissionInfo',
            data: $('#postForm').serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        window.parent.location.reload();
                        layerCloseMe();
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error: function (data) {
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            }
        });
    }
</script>
</body>
</html>
