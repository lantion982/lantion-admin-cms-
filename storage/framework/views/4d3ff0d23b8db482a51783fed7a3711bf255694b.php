<?php echo e(Form::model(null, ['class' => 'form-horizontal form-bordered', 'id' => 'searchForm', 'name' => 'searchForm','method'=>'GET'])); ?>

<!--第一行-->
<div class="row">
    <div class="col-lg-1 tabmin">
        <label>操作内容</label>
    </div>
    <div class="col-lg-2">
        <input type="text" id="keyword" name="keyword" placeholder="操作内容关键字" class="form-control">
    </div>
    <div class="col-lg-1 tabmin">
        <label for="startDate">开始时间</label>
    </div>
    <div class="col-lg-1 dtcol">
        <input name="startDate" id="startDate" class="form-control mr1 query_time" placeholder="开始时间" type="text" value="<?php echo e(date('Y-01-01 00:00:00')); ?>">
    </div>
    <div class="col-lg-1 tabmin">
        <label for="endDate">结束时间</label>
    </div>
    <div class="col-lg-1 dtcol">
        <input name="endDate" id="endDate" class="form-control query_time" placeholder="结束时间" type="text" value="<?php echo e(date('Y-m-d 23:59:59')); ?>">
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-info form-control" onclick="getLogOperate(1);"><i class="fa fa-search"></i> 查询</a>
    </div>
    <div class="col-lg-1">
        <a href="#" class="form-control btn btn-danger" onclick="clearForm();"><i class="fa fa-times"></i> 清空</a>
    </div>
</div>
<!--第三行-->
<?php echo $__env->make('manager.searchScript', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo e(Form::close()); ?>

<?php /**PATH D:\www\guanglan\admin\resources\views/manager/log/operateSearch.blade.php ENDPATH**/ ?>