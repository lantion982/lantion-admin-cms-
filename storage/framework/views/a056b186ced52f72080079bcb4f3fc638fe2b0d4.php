<form id="searchForm" name="searchForm" class='form-horizontal form-bordered'>
<!--第一行-->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <label for='keyword' class="hidden-sm hidden-xs">会员信息</label>
        <input type="text" id="keyword" name="keyword" placeholder="账号|手机号" class="form-control" value="<?php echo e($keyword); ?>">
    </div>
	<div class="col-md-3 col-sm-6">
		<label class="hidden-sm hidden-xs">变动类型</label>
		<?php echo e(Form::select('move_type[]',$move_type,'common',['id'=>'move_type','class'=>'form-control selectpicker','multiple','data-live-search'=>'true'])); ?>

	</div>
    <div class="col-md-3 col-sm-6">
        <label for='bill_no' class="hidden-sm hidden-xs">会员信息</label>
        <input type="text" id="bill_no" name="bill_no" placeholder="订单号" class="form-control" value="<?php echo e($billNo); ?>">
    </div>
</div>
<div class="row">
	<?php echo $__env->make('manager.searchDateTime', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<div class="row">
	<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
		<select class="form-control" id="page_count" name="page_count">
			<?php $__currentLoopData = config('enums.page_count'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<option value="<?php echo e($pval); ?>" <?php if($page_count==$pval): ?> selected <?php endif; ?>><?php echo e($pval); ?>条信息/每页</option>
			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</select>
	</div>
	<div class="col-md-3 col-sm-6 hidden-sm hidden-xs">
		<input type="text" id="description" name="description" placeholder="备注" class="form-control">
	</div>
	<div class="col-md-3 col-sm-6">
		<button type="button" class="btn btn-info form-control" onclick="moneyMvmt(1)" id="submitSearch">
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
<?php /**PATH D:\www\ganglan\resources\views/manager/finance/moneySearch.blade.php ENDPATH**/ ?>