<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<input type="text" id="keyword" name="keyword" placeholder="账号" class="form-control">
		</div>
		<div class="col-md-3 col-sm-6">
			<select class="form-control selectpicker" id="is_allow" name="is_allow" title="是否允许登录">
				<option value="">不限</option>
				<option value="1">是</option>
				<option value="0">否</option>
			</select>
		</div>
		<div class="col-md-3 col-sm-6 hidden-xs">
			<select class="form-control selectpicker" id="page_count" name="page_count">
				<?php $__currentLoopData = config('enums.page_count'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<option value="<?php echo e($pval); ?>" <?php if($page_count==$pval): ?> selected <?php endif; ?>><?php echo e($pval); ?>条信息/页</option>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6">
			<button class="form-control btn btn-info" onclick="getAdmin(1)" id="submitSearch" style="width:49%;">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
			<button class="form-control btn btn-danger" onclick="clearForm();" style="width:49%;">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
	<?php echo $__env->make('manager.searchScript', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</form>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/admin/adminSearch.blade.php ENDPATH**/ ?>