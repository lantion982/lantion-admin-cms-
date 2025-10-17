<!DOCTYPE html>
<html>
	<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>角色信息</title>
	</head>
	<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
	<link href="<?php echo e('/css/xadmin.css'); ?>" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="<?php echo e('/plus/layui/layui.js'); ?>"></script>
	<script type="text/javascript" src="/js/myadmin.js"></script>
<style>
    .form-group {margin-bottom:5px;}
    .form-control {display: inline;}
</style>
<body>
<div class="box box-info">
    <div class="box-body" style="padding:15px;">
        <?php echo e(Form::model($permission,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])); ?>

        <input type="hidden" id="id" name="id" value="<?php echo e($permission->id??0); ?>">
        <div class="row">
			<div class="col-sm-12">
				<label for="parent_id">所属菜单：</label>
				<?php if(empty($topPermissions)): ?>
					<?php if(empty($permission->parent_id)): ?>
						<?php echo e(Form::hidden('parent_id',$parentPermission->id)); ?>

						<?php echo e(Form::text('parent_name',$parentPermission->title,['class'=>'form-control','ReadOnly'=>'true','style'=>'width:300px;'])); ?>

					<?php else: ?>
						<?php echo e(Form::hidden('parent_id',old('parent_id'))); ?>

						<?php echo e(Form::text('parent_name',$parentPermission->title,['class'=>'form-control','ReadOnly'=>'true','style'=>'width:300px;'])); ?>

					<?php endif; ?>
				<?php else: ?>
					<?php echo e(Form::select('parent_id',$topPermissions,null,['class'=>'form-control','style'=>'width:300px;'])); ?>

				<?php endif; ?>
			</div>
			<div class="col-sm-12">
				<label for="name">权限路由：</label>
				<?php echo e(Form::text('name',old('name'),['class'=>'form-control','style'=>'width:300px;'])); ?>

			</div>
			<div class="col-sm-12">
				<label for="title">显示名称：</label>
				<?php echo e(Form::text('title',old('title'),['class'=>'form-control','style'=>'width:300px;'])); ?>

			</div>
			<div class="col-sm-12">
				<label for="icon">相关图标：</label>
				<?php echo e(Form::text('icon',old('icon'),['class'=>'form-control','style'=>'width:300px;'])); ?>

			</div>
			<div class="col-sm-12">
				<label for="parent_id">是否显示：</label>
				<?php if(isset($permission->is_show)): ?>
                    <input type="radio" name="is_show" id="is_show" value="1" class="mt1" <?php if($permission->is_show==1): ?> checked <?php endif; ?>>显示&nbsp;
					<input type="radio" name="is_show" id="is_show" value="0" class="mt1" <?php if($permission->is_show==0): ?> checked <?php endif; ?>>不显示
				<?php else: ?>
                    <input type="radio" name="is_show" id="is_show" value="1" class="mt1" checked>显示&nbsp;
					<input type="radio" name="is_show" id="is_show" value="0" class="mt1">不显示
				<?php endif; ?>
			</div>
			<div class="col-sm-12">
				<label for="ptype">权限类型：</label>
				<?php echo e(Form::select('ptype',config('enums.auth_type'),old('ptype'),['class'=>'form-control','style'=>'width:300px;'])); ?>

			</div>
			<div class="col-sm-12">
				<label for="sorts">排序序号：</label>
				<?php echo e(Form::text('sorts',old('sorts'),['class'=>'form-control','style'=>'width:300px;'])); ?>

			</div>
        </div>
        <?php echo e(Form::close()); ?>

    </div>
    <div class="box-footer pull-right mt2 mb2">
        <?php if(empty($permission->id)): ?>
            <button type="button" class="btn btn-info" id="btn-Add" onclick="createPermission()">新增权限</button>
        <?php else: ?>
            <button type="button" class="btn btn-info" id="btn-Update" onclick="updatePermissionInfo()">提交更新</button>
        <?php endif; ?>
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
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/admin/permissionInfo.blade.php ENDPATH**/ ?>