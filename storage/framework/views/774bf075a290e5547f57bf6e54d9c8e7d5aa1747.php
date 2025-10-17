<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>名称</th>
        <th class="hidden-sm hidden-xs">描述</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="t-c"><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($role->title); ?></td>
            <td class="hidden-sm hidden-xs"><?php echo e($role->remarks); ?></td>
			<td class="t-c">
				<a href="javascript:" class="text-blue" onclick="roleInfo('<?php echo e($role->id); ?>')">详情</a>
				&nbsp;|&nbsp;
				<a href="javascript:" class="text-green" onclick="rolePermission('<?php echo e($role->id); ?>')">权限</a>
				&nbsp;|&nbsp;
				<a href="javascript:" class="text-red" onclick="delRole('<?php echo e($role->id); ?>')">删除</a>
			</td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
	<?php echo e($roles->links()); ?>

</div>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/admin/roleListAjax.blade.php ENDPATH**/ ?>