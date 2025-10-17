<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="box box-info" style="padding:15px;">
    <form class="form-horizontal form-bordered" id="postForm" name="postForm">
        <?php echo e(csrf_field()); ?>

        <div class="box-body" style="padding-left:55px;">
            <div class="row">
                <div class="form-group">
                    <label for="old_pass">原登录密码</label>
                    <input id="old_pass" name="old_pass" type="password" class="form-control" placeholder="请输入原登录密码">
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for="new_pass">新登录密码</label>
                    <input id="new_pass" name="new_pass" type="password" class="form-control" placeholder="请输入新登录密码">
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for="cof_pass">确认密码</label>
                    <input id="cof_pass" name="cof_pass" type="password" class="form-control" placeholder="请输入确认密码">
                </div>
            </div>

        </div>
    </form>
    <div class="box-footer  pull-right">
        <button type="button" class="btn btn-success" id="btn-Update" onclick="updateAdminPassword()">
            提交更新
        </button>
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">
            关闭本页
        </button>
    </div>
</div>

<script>
    function updateAdminPassword(){
        if($('#old_pass').val()==''){
            layer.alert('请输入原登录密码！',{icon:2,closeBtn:0,time:1000});
            return false;
        }
        if($('#new_pass').val().length<6||$('#new_pass').val().length>15){
            layer.alert('请输入6-15位的新登录密码！',{icon:2,closeBtn:0,time:1000});
            return false;
        }
        if($('#new_pass').val()!=$('#cof_pass').val()){
            layer.alert('确认密码输入不一致！',{icon:2,closeBtn:0,time:1000});
            return false;
        }
        $.ajax({
            type:'post',
            url:'/manager/updateAdminPassword',
            data:$('#postForm').serialize(),
            dataType:'json',
            success:function(data){
                if(data.status==0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        if(data.msg=='修改成功，请重新登录！'){
                            parent.location.reload();
                        }
                    });
                }else{
                    let index = layer.alert(data.msg,{icon:2,closeBtn:0},function(){
                        layer.close(index);
                    });
                }
            },
            error:function(data){
                layer.alert('提交失败！',{icon:2,closeBtn:0});
            }
        });
    }
</script>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/admin/adminEditPwd.blade.php ENDPATH**/ ?>