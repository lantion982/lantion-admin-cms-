<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th>序</th>
        <th>操作人</th>
        <th>时间</th>
        <th>操作内容</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $logOperations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$operateLog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($operateLog->admin->login_name); ?></td>
            <td><?php echo e($operateLog->created_at); ?></td>
            <td><abbr title="<?php echo e($operateLog->content); ?>"><?php echo e(str_limit($operateLog->content,90)); ?></abbr></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
       <?php echo e($logOperations->links()); ?>

    </div>
</div>
<?php /**PATH D:\www\ganglan\resources\views/manager/log/operateList.blade.php ENDPATH**/ ?>