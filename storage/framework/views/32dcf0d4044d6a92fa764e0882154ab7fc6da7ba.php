<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="box box-info" style="padding:20px;">
    <div class="box-header with-border">
        <h3 class="box-title">【<?php echo e($members->login_name); ?>】额度操作</h3>
    </div>
    <div class="box-body">
        <div class="col-md-6 col-sm-6">
            <?php echo e(Form::model($members,['class'=>'form-bordered','id'=>'theform','name'=>'theform','method'=>'post'])); ?>

            <?php echo e(Form::hidden('id',old('id'),['id'=>'id'])); ?>

            <div class="form-group mt1">
                <?php echo e(Form::label('balance','余额：',['class'=>'control-label'])); ?>

                <?php echo e(Form::label('balance',$members->balance,['class'=>'form-control-static'])); ?>

            </div>
            <div class="form-group mt1">
                <?php echo e(Form::label('move_type','类型：',['class'=>'control-label'])); ?>

                <?php echo e(Form::select('move_type',$moveType,old('move_type'),['class'=>'form-control'])); ?>

            </div>
            <div class="form-group mt1">
                <?php echo e(Form::label('money','金额：',['class'=>'control-label'])); ?>

                <?php echo e(Form::text('money',old('money'),['id'=>'money','class'=>'form-control','onkeyup'=>"this.value=this.value.replace(/[^\d.]/g,'');"])); ?>

            </div>
            <div class="form-group mt1">
                <?php echo e(Form::label('commit','备注：',['class'=>'control-label'])); ?>

                <?php echo e(Form::text('commit',old('commit'),['id'=>'recomnt','class'=>'form-control',])); ?>

            </div>
            <div class="form-group mt1">
                <button type="button" id="btn-update" class="btn btn-info" onclick="updateInfo('<?php echo e($members->id); ?>')">确认提交</button>
                <button type="button" class="btn btn-danger" onclick="layerCloseMe();">关闭本页</button>
            </div>
            <?php echo e(Form::close()); ?>

        </div>
    </div>
</div>

<script>
    function updateInfo(){
        if($('#money').val()==''){
            layer.alert('请输入要变动的金额！',{icon:2,closeBtn:0});
            return false;
        }
        if($('#recomnt').val()==''){
            layer.alert('请在备注中输入操作原因！',{icon:2,closeBtn:0});
            return false;
        }
        if(confirm('金额变动确认？请不要重复点击！')){
            $('#btn-updateMemberMoney').attr('disabled',true);
            $.ajax({
                type:'post',url:'/manager/updateMemberMoney',data:$('#theform').serialize(),dataType:'json',
                headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    if(data.status==0){
                        layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                            layerCloseMe();
                            window.parent.members(1);
                        });
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,timeout:2000});
                    }
                },
                error:function(data){
                    layer.alert('网络连接失败，请刷新后重试!',{icon:2,closeBtn:0,timeout:2000});
                }
            });
            return true;
        }
        return false;
    }
</script>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/finance/balanceInfo.blade.php ENDPATH**/ ?>