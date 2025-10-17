<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>帐号</th>
        <th>角色</th>
        <th class="hidden-sm">允许登录</th>
		<th class="hidden-sm hidden-xs">添加日期</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="t-c">
				<?php echo e(++$key+($page_count*($page-1))); ?>

            </td>
            <td><?php echo e($val->login_name); ?></td>
            <td>
                <?php if($val->roles()->count()): ?>
                    <?php $__currentLoopData = $val->roles()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="label label-success"><?php echo e($role->title); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <span class="badge">无</span>
                <?php endif; ?>
            </td>
            <td class="hidden-sm">
				<input type="checkbox" value="<?php echo e($val->id); ?>" <?php echo e(str_is($val->is_allow,'1')?'checked':''); ?>

					class="switch">
			</td>
			<td class="hidden-sm hidden-xs"><?php echo e($val->created_at); ?></td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="adminInfo('<?php echo e($val->id); ?>')">详情</a>
				&nbsp;|&nbsp;
                <a href="javascript:" class="text-red" onclick="delAdmin('<?php echo e($val->id); ?>')">删除</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<div class="box-info">
	<?php echo e($admins->links()); ?>

</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/admin/adminListAjax.blade.php ENDPATH**/ ?>