<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th>序号</th>
        <th>IP地址</th>
        <th>域名</th>
        <th>注册次数</th>
        <th>登录失败次数</th>
        <th>最后记录时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $ipLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($var->ip_addr); ?></td>
            <td><?php echo e($var->domain); ?></td>
            <td><?php echo e($var->register_count); ?></td>
            <td><?php echo e($var->failed_count); ?></td>
            <td><?php echo e(str_limit($var->record_time,32)); ?></td>
            <td>
                <a href="#" class="btn btn-info btn-sm" onclick="ipReset('<?php echo e($var->id); ?>')">
                    重置归零</a>
	            <?php if($var->blackId==''): ?>
                    <a href="#" class="btn btn-danger btn-sm" onclick="addIpBlack('<?php echo e($var->id); ?>','<?php echo e($var->blackId); ?>')">
	                   <?php echo e($var->blackId==''?'设置':'移除'); ?>黑名单</a>
				<?php else: ?>
		            <a href="#" class="btn btn-warning btn-sm" onclick="addIpBlack('<?php echo e($var->id); ?>','<?php echo e($var->blackId); ?>')">
	                   <?php echo e($var->blackId==''?'设置':'移除'); ?>黑名单</a>
				<?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
   <?php echo e($ipLogs->links()); ?>

</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/log/IpLogList.blade.php ENDPATH**/ ?>