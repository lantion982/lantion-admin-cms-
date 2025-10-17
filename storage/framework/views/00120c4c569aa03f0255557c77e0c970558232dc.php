<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>后台帐号</title>
</head>
<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<link href="<?php echo e('/css/xadmin.css'); ?>" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo e('/plus/layui/layui.js'); ?>"></script>
<script type="text/javascript" src="<?php echo e('/js/myadmin.js'); ?>"></script>
<body>
<div class="box box-info" style="padding:20px;">
    <div class="box-body" id="boxvue">
        <div class="box-body">
            <?php echo e(Form::model($admins,['id'=>'postForm','name'=>'postForm'])); ?>

            <?php if(auth('admin')->user()->is_admin || auth('admin')->user()->can('assignRoles')): ?>
                <div class="row">
                    <div class="form-group" style="">
                        <div style="width:75px;padding:0;float:left;">
                            <label for="roles">所属角色：</label>
                        </div>
                        <div class="atmaxlg">
                            <select id="roles_id" name="roles_id" class="form-control">
                                <option value="0">请选择角色</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($admins&&$admins->roles): ?>
                                        <option value="<?php echo e($key); ?>" <?php if($admins->roles[0]==$key): ?> selected <?php endif; ?>><?php echo e($val); ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo e($key); ?>"><?php echo e($val); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="form-group">
                    <?php echo e(Form::label('display_name','登录账号：',['class'=>'control-label'])); ?>

                    <div class="atmaxlg">
                        <?php if(empty($admins->display_name)): ?>
                            <?php echo e(Form::text('login_name',old('login_name'),['id'=>'login_name','class'=>'form-control'])); ?>

                        <?php else: ?>
                            <input type="hidden" id="admin_id" name="admin_id" value="<?php echo e($admins->id); ?>">
                            <?php echo e(Form::label('login_name',$admins->login_name,['class'=>'control-label','style'=>'text-align:left;'])); ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <?php echo e(Form::label('display_name','管理昵称：',['class'=>'control-label'])); ?>

                    <div class="atmaxlg">
                        <?php echo e(Form::text('display_name',null,['id'=>'display_name','class'=>'form-control'])); ?>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <?php echo e(Form::label('login_pwd','登录密码：',['class'=>'control-label'])); ?>

                    <div class="atmaxlg">
                        <?php echo e(Form::password('login_pwd',['id'=>'login_pwd','class'=>'form-control'])); ?>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <?php echo e(Form::label('phone','联系电话：',['class'=>'control-label'])); ?>

                    <div class="atmaxlg">
                        <?php echo e(Form::text('phone',null,['class'=>'form-control'])); ?>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <?php echo e(Form::label('is_allow','启用状态：',['class'=>'control-label'])); ?>

                    <div class="atmaxlg">
                        <?php echo e(Form::select('is_allow',config('enums.active_status'),old('is_allow'),['class'=>'form-control'])); ?>

                    </div>
                </div>
            </div>

            <div class="box-footer  pull-right mb3 mt3">
                <?php if(empty($admins->id)): ?>
                    <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="createAdmin()">新增账号</button>
                <?php else: ?>
                    <button type="button" class="btn btn-info" id="btn-Add" lay-filter="btn-Add" onclick="updateAdmin()">提交更新</button>
                <?php endif; ?>
                <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
            </div>
            <?php echo e(Form::close()); ?>

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
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/admin/adminInfo.blade.php ENDPATH**/ ?>