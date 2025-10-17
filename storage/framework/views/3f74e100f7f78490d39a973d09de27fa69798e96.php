
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
	<thead>
	<tr>
		<th class="t-c">序</th>
		<th>账号</th>
		<th class="hidden-xs">昵称</th>
		<th class="hidden-sm hidden-xs">等级</th>
		<th>余额</th>
		<th class="hidden-sm hidden-xs">注册时间</th>
		<th class="t-c">操作</th>
	</tr>
	</thead>
	<tbody>
	<?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<tr class="<?php if($member->is_allow==0): ?> text-gray <?php endif; ?>">
			<td class="t-c"><?php echo e(++$key+($page_count*($page-1))); ?></td>
			<td><?php echo e($member->login_name); ?></td>
			<td class="hidden-xs"><?php echo e($member->nick_name??'-'); ?></td>
			<td class="hidden-sm hidden-xs"><?php echo e($member->memberLevel->member_level_name??'-'); ?></td>
			<td class="<?php if($member->balance<0): ?> text-red <?php endif; ?> "><?php echo e($member->balance); ?></td>
			<td class="hidden-sm hidden-xs"><?php echo e($member->register_time); ?></td>
			<td class="t-c">
				<a href="javascript:" class="text-green" onclick="balanceInfo('<?php echo e($member->id); ?>')">额度</a>
			</td>
		</tr>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</tbody>
</table>
<div class="box-info">
	<?php echo e($members->links()); ?>

</div>
<?php /**PATH D:\www\ganglan\resources\views/manager/finance/memberList.blade.php ENDPATH**/ ?>