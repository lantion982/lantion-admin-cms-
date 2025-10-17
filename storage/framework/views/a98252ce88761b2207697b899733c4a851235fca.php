<div class="row listtotal">
	<div class="col-xs-12 listtotaltxt">
		合计：<?php echo e($list->total()); ?> 笔&nbsp;<?php echo e(mynumber($results['money_change'])); ?> 元
	</div>
</div>
<table id="tbl-activities" class="table table-hover">
	<input type="hidden" id="page" value="<?php echo e($page); ?>">
	<thead>
	<tr>
		<th class="t-c">序</th>
		<th>单号</th>
        <th>账号</th>
		<th class="hidden-sm hidden-xs">变动前</th>
		<th>变动金额</th>
		<th class="hidden-sm hidden-xs">变动后</th>
		<th class="hidden-xs">类型</th>
		<th>日期时间</th>
		<th class="hidden-sm hidden-xs">操作人</th>
		<th class="hidden-sm hidden-xs">备注</th>
	</tr>
	</thead>
	<tbody>
	<?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<tr>
			<td class="t-c"><?php echo e(++$key+($page_count*($page-1))); ?></td>
            <td><?php echo e($val->bill_no); ?></td>
			<td><?php echo e($val->member->login_name??'-'); ?></td>
			<td class="<?php if($val->money_before<0): ?> text-red <?php endif; ?> hidden-sm hidden-xs">
				<?php echo e($val->money_before); ?>

			</td>
			<td class="<?php if($val->money_change<0): ?> text-red <?php endif; ?>"><?php echo e($val->money_change); ?></td>
			<td class="<?php if($val->money_after<0): ?> text-red <?php endif; ?> hidden-sm hidden-xs">
				<?php echo e($val->money_after); ?>

			</td>
			<td class="hidden-xs"><?php echo e(config('enums.move_type')[$val->move_type]); ?></td>
			<td title="<?php echo e($val->created_at); ?>">
				<?php echo e($val->created_at); ?>

			</td>
			<td class="hidden-sm hidden-xs"><?php echo e($val->admin->login_name??'系统'); ?></td>
			<td class="hidden-sm hidden-xs"><?php echo e(str_limit($val->remarks,100)); ?></td>
		</tr>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</tbody>
</table>
<div class="box-info">
	<?php echo e($list->links()); ?>

</div>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/finance/moneyMvmtList.blade.php ENDPATH**/ ?>