<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
	<div class="row">
		<div class="col-md-2 col-sm-2">
			<input type="text" id="keyword" name="keyword" placeholder="关键字" class="form-control" value="<?php echo e($data['keyword']??''); ?>">
		</div>
		<div class="col-md-2 col-sm-2 hidden-sm hidden-xs">
            <select id="cid" name="cid" class="form-control selectpicker">
                <option value="0">全部分类</option>
                <?php $__currentLoopData = $linkClass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                   <option value="<?php echo e($val->id); ?>" <?php if($val->id==$data['cid']): ?> selected <?php endif; ?> ><?php echo e($val->title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
		</div>

		<div class="col-md-2 col-sm-2">
			<button type="button" class="form-control btn btn-info" onclick="getLink('1')" id="submitSearch">
				<i class="fa fa-search"></i>&nbsp;查询
			</button>
		</div>
		<div class="col-md-2 col-sm-2">
			<button type="button" class="form-control btn btn-danger" onclick="clearForm();">
				<i class="fa fa-times"></i>&nbsp;清空
			</button>
		</div>
	</div>
	<?php echo $__env->make('manager.searchScript', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</form>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/link/search.blade.php ENDPATH**/ ?>