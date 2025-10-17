<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
	.form-group{margin-bottom:5px;}
	.form-control{display:inline;}
</style>
<link href="<?php echo e('/css/fileinput.min.css'); ?>" rel="stylesheet"/>
<div class="box box-info" style="padding:10px;">
    <div class="box-body">
        <div class="col-md-7 col-sm-7">
        <?php echo e(Form::model($member,['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])); ?>

        <?php echo e(Form::hidden('id',old('id'))); ?>

        <!--第一列-->
            <div class="col-sm-6">
                <div class="form-group">
                    <?php echo e(Form::label('login_name','会员账号：',['class'=>'form-control-label'])); ?>

                    <?php echo e(Form::label('login_name',$member->login_name,['class'=>''])); ?>

                </div>
                <?php if(auth('admin')->user()->can('updateMemberLogPwd') ||\App\Libs\Helper::isAdmin()): ?>
                    <div class="form-group mt1">
                        <?php echo e(Form::label('login_pwd','登录密码：',['class'=>'form-control-label'])); ?>

                        <?php echo e(Form::password('login_pwd',['class'=>'form-control','style'=>'width:200px;'])); ?>

                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <?php echo e(Form::label('real_name','会员昵称：',['class'=>'form-control-label'])); ?>

                    <?php echo e(Form::text('nick_name',old('nick_name'),['class'=>'form-control','style'=>'width:200px;'])); ?>

                </div>
                <div class="form-group">
                    <?php echo e(Form::label('phone','联系电话：',['class'=>'form-control-label'])); ?>

                    <input type="hidden" value="<?php echo e($member->phone); ?>" id="phone" name="phone">
                    <?php echo e(Form::text('phone2',old('phone2'),['class'=>'form-control','style'=>'width:200px;'])); ?>

                </div>
                <div class="form-group mt1">
                    <?php echo e(Form::label('member_level_id','会员等级：',['class'=>'form-control-label'])); ?>

                    <div class="atmax200">
                        <?php echo e(Form::select('level_id',$levels,old('level_id'),['class'=>'form-control selectpicker','style'=>'width:200px;'])); ?>

                    </div>
                </div>

                <div class="form-group">
                    <label>登录状态：</label>
                    <input type="radio" name="is_allow" value="1" class="mt1"
                        <?php if($member->is_allow==1): ?> checked <?php endif; ?>>正常
                    <input type="radio" name="is_allow" value="0" class="mt1"
                        <?php if($member->is_allow==0): ?> checked <?php endif; ?>>锁定 &nbsp;

                </div>
            </div>
            <!--第一列-->
            <!--第二列-->
            <div class="col-sm-6">
                <div class="form-group">
                    <label>会员性别：</label>
                    <input type="radio" name="sex" value="1" class="mt1" <?php if($member->sex==1): ?> checked <?php endif; ?>>男 &nbsp;
                    <input type="radio" name="sex" value="0" class="mt1" <?php if($member->sex==0): ?> checked <?php endif; ?>>女
                </div>
                <div class="form-group">
                    <?php echo e(Form::label('email','邮箱地址：',['class'=>'form-control-label'])); ?>

                    <?php echo e(Form::text('email',old('email'),['class'=>'form-control','style'=>'width:200px;'])); ?>

                </div>
                <div class="form-group mt1">
                    <?php echo e(Form::label('failed_count','登录失败：',['class'=>'form-control-label'])); ?>

                    <?php echo e(Form::label($member->failed_count,'',['class'=>'form-control-label','style'=>'width:200px;padding-left:0px!important'])); ?>

                </div>
            </div>
            <!--第二列-->
            <div class="box-footer form-group col-sm-12" style="padding-left:2px;">
                <button type="button" class="btn btn-info" id="btn-Add" onclick="updateMemberInfo()">提交保存</button>
                <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
            </div>
            <?php echo e(Form::close()); ?>

        </div>
        <div class="col-md-5 col-sm-5" style="padding:0">
            <div class="form-group">
                <table id="tbl-activities" class="table table-hover">
                    <thead>
                        <tr>
                            <th><label for="member_remark">会员事件</label></th>
                        </tr>
                        <tr>
                            <td><textarea name="member_remark" rows="2" id="member_remark" style="width:100%;"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button type="button" class="btn btn-info" onclick="addRemark('<?php echo e($member->id); ?>')">
                                    提交保存
                                </button>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $commits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php echo e($commit->commits,100); ?>-<?php echo e($commit->admin->login_name??''); ?>|<?php echo e($commit->created_at); ?>

                                    <a href="javascript:" class="btn btn-white" title="删除" onclick="confirm('确定删除吗？')?delCommit('<?php echo e($commit->id); ?>'):''">
                                        <i class="glyphicon glyphicon-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        lay(".query_time").each(function(){
            laydate.render({
                elem:this
                ,type:"date"
                ,trigger:"click",
            });
        });
    });

    function updateMemberInfo(){
        $.ajax({
            type:"post",
            url:"/manager/updateMemberInfo",
            data:$("#postForm").serialize(),
            dataType:"json",
            success:function(data){
                if (data.status == 0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        layerCloseMe();
                        window.parent.getMAccount(1);
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0});
                }
            },
            error:function(data){
                layer.alert("提交失败！",{icon:2,closeBtn:0});
            },
        });
    }

    function addRemark(id){
        let commit = $("#member_remark").val();
        if (commit == ''){
            layer.alert("请输入会员事件内容！",{icon:2,closeBtn:0,timeout:1000});
            return false;
        }
        $.ajax({
            type:"post",
            url:"/manager/addMemberRemark",
            data:{commit:commit,member_id:id,_token:'<?php echo e(csrf_token()); ?>'},
            dataType:"json",
            success:function(data){
                if (data.status == 0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        location.reload();
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0,timeout:2000});
                }
            },
            error:function(data){
                layer.alert("提交失败！",{icon:2,closeBtn:0,timeout:2000});
            },
        });
    }

    function delCommit(id){
        $.ajax({
            type:"post",
            url:"/manager/deleteMemberRemark",
            data:{id:id},
            dataType:"json",
            headers:{"X-CSRF-TOKEN":$("meta[name=\"csrf-token\"]").attr("content")},
            success:function(data){
                if (data.status == 0){
                    layer.alert(data.msg,{icon:1,closeBtn:0},function(){
                        location.reload();
                    });
                }else{
                    layer.alert(data.msg,{icon:2,closeBtn:0,timeout:2000});
                }
            },
            error:function(data){
                layer.alert('网络连接失败，请刷新后重试！',{icon:2,closeBtn:0,timeout:2000});
            },
        });
    }
</script>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/member/memberInfo.blade.php ENDPATH**/ ?>