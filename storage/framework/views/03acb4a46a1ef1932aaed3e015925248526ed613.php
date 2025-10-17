<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<label for="keyword" class="hidden-sm hidden-xs">帐号信息</label>
			<input type="text" id="keyword" name="keyword" placeholder="账号|昵称|手机号" class="form-control">
		</div>
		<div class="col-md-3 col-sm-6">
			<label for="level_id" class="hidden-sm hidden-xs">帐号等级</label>
			<select id="level_id" class="form-control selectpicker" name="level_id" title="帐号等级">
				<option value="">不限</option>
				<?php $__currentLoopData = $level; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<option value="<?php echo e($var->id); ?>"><?php echo e($var->level_name); ?></option>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6">
			<label for="is_allow" class="hidden-sm hidden-xs">帐号状态</label>
			<select class="form-control selectpicker" id="is_allow" name="is_allow" title="帐号状态">
                <option value="2">全部</option>
				<option value="1">正常</option>
				<option value="0">冻结</option>
			</select>
		</div>
	</div>
	<div class="row">
		<?php echo $__env->make('manager.searchDateTime', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
			<select class="form-control" id="page_count" name="page_count">
				<?php $__currentLoopData = config('enums.page_count'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<option value="<?php echo e($pval); ?>" <?php if($page_count==$pval): ?> selected <?php endif; ?>><?php echo e($pval); ?>条信息/页</option>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-md-3 col-sm-6">
			<button type="button" class="form-control btn btn-info" onclick="getMAccount('1')" id="submitSearch">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-3 col-sm-6">
			<button type="button" class="form-control btn btn-danger" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
	<?php echo $__env->make('manager.searchScript', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</form>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/member/search.blade.php ENDPATH**/ ?>