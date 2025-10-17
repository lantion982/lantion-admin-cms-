<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
        <tr>
            <th>序</th>
            <th>类型</th>
            <th>帐号</th>
            <th>登录IP</th>
            <th>登录地区</th>
            <th>登录结果</th>
            <th>登录时间</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $logLogins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$logLogin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e(++$key+($page_count*($page-1))); ?></td>
                <td><?php echo e(config('enums.member_admin_type')[$logLogin->member_type]??''); ?></td>
                <td><?php echo e($logLogin->login_name); ?></td>
                <td><?php echo e($logLogin->login_ip); ?></td>
                <td><?php echo e($logLogin->login_area); ?></td>
                <td class="<?php if($logLogin->login_result=='success'): ?> text-green <?php else: ?> text-red <?php endif; ?>">
                    <?php if($logLogin->login_result=='success'): ?> 成功 <?php else: ?> 失败 <?php endif; ?>
                </td>
                <td><?php echo e($logLogin->created_at); ?></td>

            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<div class="box-info">
    <div class="col-lg-12">
        <?php echo e($logLogins->links()); ?>

    </div>
</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/log/loginList.blade.php ENDPATH**/ ?>