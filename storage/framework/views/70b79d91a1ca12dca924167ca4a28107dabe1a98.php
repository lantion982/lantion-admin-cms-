<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="<?php echo e($page_count); ?>">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>账号</th>
        <th>昵称</th>
        <th>等级</th>
        <th>余额</th>
        <th class="hidden-sm hidden-xs">相册目录</th>
		<th class="hidden-sm hidden-xs">注册时间</th>
        <th class="hidden-md hidden-sm hidden-xs">最后登录</th>
        <th class="hidden-xs">状态</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="<?php if(str_is($member->allow_login,'0')): ?> text-gray <?php endif; ?>">
            <td clas="t-c"><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($member->login_name); ?></td>
            <td><?php echo e($member->nick_name); ?></td>
            <td><?php echo e($member->memberLevel->level_name??'-'); ?></td>
            <td class="<?php if($member->balance<0): ?> text-red <?php endif; ?> "><?php echo e($member->balance); ?></td>
			<td class="hidden-sm hidden-xs"><?php echo e($member->picpath); ?></td>
            <td class="hidden-sm hidden-xs"><?php echo e($member->created_at); ?></td>
            <td class="hidden-md hidden-sm hidden-xs"><?php echo e($member->late_login_time); ?></td>
            <td class="<?php if(str_is($member->is_allow,'0')): ?> text-red <?php endif; ?> hidden-xs">
				<?php echo e(str_is($member->is_allow,'1')?'正常':'冻结'); ?>

			</td>
            <td class="t-c">
                <a href="javascript:" onclick="memberAccountInfo('<?php echo e($member->id); ?>')" class="text-blue">详情</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
	<?php echo e($members->links()); ?>

</div>
<?php /**PATH D:\www\ganglan\resources\views/manager/member/memberAjax.blade.php ENDPATH**/ ?>