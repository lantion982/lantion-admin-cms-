<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="PAGE_COUNT" value="<?php echo e($page_count); ?>">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th class="t-c">序</th>
        <th>编号</th>
        <th>名称</th>
        <th class="hidden-xs">会员数</th>
        <th class="hidden-xs t-c" title="赠送积分">赠送积分</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="t-c"><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($level->level_code); ?> </td>
            <td><?php echo e($level->level_name); ?></td>
            <td class="hidden-xs"><?php echo e($level->members()->count()??0); ?></td>
            <td class="hidden-xs t-r"><?php echo e(mynumber($level->gift_money)); ?></td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="levelInfo('<?php echo e($level->id); ?>')">详情</a>
				&nbsp;|&nbsp;
                <a href="javascript:" class="text-red" onclick="delLevel('<?php echo e($level->id); ?>')">删除</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<div class="box-info">
	<?php echo e($levels->links()); ?>

</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/member/levelAjax.blade.php ENDPATH**/ ?>