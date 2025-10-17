<?php $__env->startSection('content'); ?>
	<div class="row">
	    <div class="topbox">
	        <div class="box box-info box-solid">
	            <div class="box-header">
	                <h5 class="box-title">权限列表</h5>
	                <div class="box-tools pull-right">
			            <button type="button" title="添加" class="btn btn-default btnn-sm"
                                onclick="addPermission()">
	                        <i class="fa fa-plus-square"></i>&nbsp;新增权限
	                    </button>
		                <button type="button" title="刷新" class="btn btn-box-tool" onclick="location.reload();">
                            <i class="fa fa-refresh"></i>
                        </button>
	                    <button type="button" title="展开&收缩" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
	                </div>
	            </div>
	            <div class="box-body">
	                <div class="row">
	                    <div id="ajaxContent" class="box-body table-responsive no-padding">
				            <?php echo $__env->make('manager.admin.permissionList', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
				        </div>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
    <script type="text/javascript" src="<?php echo e('/js/page/admin/entrustPermission.js'); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('manager.superUI', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\www\ganglan\resources\views/manager/admin/permission.blade.php ENDPATH**/ ?>