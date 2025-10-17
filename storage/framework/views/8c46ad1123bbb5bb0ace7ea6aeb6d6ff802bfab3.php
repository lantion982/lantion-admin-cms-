<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="topbox">
        <div class="box box-info box-solid">
            <div class="box-header">
                <h5 class="box-title">会员登录IP列表</h5>
                <div class="box-tools pull-right">
                    <button type="button" title="刷新" class="btn btn-box-tool" onclick="location.reload();">
                        <i class="fa fa-refresh"></i>
                    </button>
                    <button type="button" title="展开&收缩" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <form id="searchForm" name="searchForm" class='form-horizontal form-bordered' method="get" action="<?php echo e(route('getLoginIpList')); ?>">
                        <div class="row">
                            <div class="col-sm-2">
                                <label for="keyword" class="hidden-sm hidden-xs">关键字</label>
                                <input type="text" id="keyword" name="keyword" placeholder="会员帐号" class="form-control" value="<?php echo e($keyword); ?>">
                            </div>
                            <div class="col-sm-2">
                                <label for="keyword" class="hidden-sm hidden-xs">IP</label>
                                <input type="text" id="ip" name="ip" placeholder="IP" class="form-control" value="<?php echo e($ip); ?>">
                            </div>
                            <div class="col-sm-2">
                                <label class="hidden-sm hidden-xs">开始时间</label>
                                <input name="startDate" id="startDate" class="form-control query_time" placeholder="开始时间" type="text" value="<?php echo e($startDate); ?>">
                            </div>
                            <div class="col-sm-2">
                                <label class="hidden-sm hidden-xs">结束时间</label>
                                <input name="endDate" id="endDate" class="form-control query_time" placeholder="结束时间" type="text" value="<?php echo e($endDate); ?>">
                            </div>
                            <div class="col-sm-1">
                                <label class="hidden-sm hidden-xs">每页显示</label>
                                <select class="form-control" id="page_count" name="page_count">
                                    <?php $__currentLoopData = config('enums.page_count'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pval); ?>" <?php if($page_count==$pval): ?> selected <?php endif; ?>><?php echo e($pval); ?>条</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="form-control btn btn-info" id="submitSearch" style="width:48%;margin-top:20px;">
                                    <i class="fa fa-search"></i>&nbsp;查询
                                </button>
                                <button type="button" class="form-control btn btn-danger" onclick="clearForm();" style="width:48%;margin-top:20px;">
                                    <i class="fa fa-times"></i>&nbsp;清空
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div id="ajaxContent" class="box-body table-responsive no-padding">
        <table id="tbl-activities" class="table table-hover">
            <input type="hidden" id="page" value="<?php echo e($page); ?>">
            <thead>
                <tr>
                    <th>帐号</th>
                    <th>登录IP</th>
                    <th>登录地区</th>
                    <th>登录结果</th>
                    <th>登录时间</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $loginLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($log->Member->display_name??$log->login_name); ?></td>
                    <td><?php echo e($log->login_ip); ?></td>
                    <td><?php echo e($log->login_area); ?></td>
                    <td><?php echo e(config('enums.login_result')[$log->login_result]??'-'); ?></td>
                    <td><?php echo e($log->created_at); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="box-info">
        <?php echo e($loginLogs->links()); ?>

    </div>
</div>
<script>
    function clearForm(){
        $('#keyword').val('');
        $('#ip').val('');
        $('#startDate').val('<?php echo e(date('')); ?>');
        $('#endDate').val('<?php echo e(date('')); ?>');
    }

    $(document).ready(function(){
        dataTables();
        lay('.query_time').each(function(){
            laydate.render({
                elem:this
                ,type:'datetime'
                ,trigger:'click'
            });
        });
        lay('.query_date').each(function(){
            laydate.render({
                elem:this
                ,trigger:'click'
            });
        });
    });
    $(document).keypress(function(e){
        if(!e){
            e = window.event;
        }
        if((e.keyCode||e.which)==13){
            $('#submitSearch').click();
            return false;
        }
    });
</script><?php $__env->stopSection(); ?>

<?php echo $__env->make('manager.superUI', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\www\ganglan\resources\views/manager/member/loginIpList.blade.php ENDPATH**/ ?>