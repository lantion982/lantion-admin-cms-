<?php echo e(Form::model(null,['class'=>'form-horizontal form-bordered','id'=>'searchForm','name'=>'searchForm','method'=>'GET'])); ?>

<div class="row">
    <div class="col-lg-1 tabmin">
        <label for="page_count">每页显示</label>
    </div>
    <div class="col-lg-2">
        <select class="form-control selectpicker" id="page_count" name="page_count">
            <?php $__currentLoopData = config('enums.page_count'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($pval); ?>" <?php if($page_count==$pval): ?> selected <?php endif; ?>><?php echo e($pval); ?>条信息</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-lg-1 tabmin">
        <label>关键字</label>
    </div>
    <div class="col-lg-2">
        <input type="text" id="keyword" name="keyword" placeholder="IP|主机地址" class="form-control">
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-info form-control" onclick="getIP(1)"><i class="fa fa-search"></i> 查询</a>
    </div>
    <div class="col-lg-1">
        <a href="#" class="btn btn-danger form-control" onclick="clearForm();" style="margin-top:1px;">
            <i class="fa fa-times"></i> 清空</a>
    </div>
</div>
<?php echo $__env->make('manager.searchScript', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo e(Form::close()); ?>

<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/config/search.blade.php ENDPATH**/ ?>