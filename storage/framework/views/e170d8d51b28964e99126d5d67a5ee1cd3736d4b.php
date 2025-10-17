<?php $__env->startSection('content'); ?>
	<div class="row">
		<div class="topbox">
			<div class="box box-info box-solid">
				<div class="box-header">
					<h5 class="box-title">会员等级列表</h5>
					<div class="box-tools pull-right">
						<button type="button" title="新增帐号" class="btn btn-default btn-sm" onclick="createLevel()">
							<i class="fa fa-plus-square"></i>&nbsp;新增等级
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
					<div class="panel panel-default">
                        <div id="ajaxContent"  class="box-body table-responsive no-padding">
                            <?php echo $__env->make('manager.member.levelAjax', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="<?php echo e('/js/page/member/memberLevel.js?'.time()); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('manager.superUI', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/member/level.blade.php ENDPATH**/ ?>