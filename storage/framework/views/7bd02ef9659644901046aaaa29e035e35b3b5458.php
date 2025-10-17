<div class="row" style="margin:0;">
    <div class="col-lg-12" style="font-size:15px;line-height:30px;padding:0;">
        <ol class="breadcrumb mb1">
            <?php $__currentLoopData = $pclass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
                <a href="<?php echo e(route('linkList')); ?>?pid=<?php echo e($val->id); ?>">
                    <?php echo e($val->title); ?>

                </a>
            </li>
           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </div>
</div>
<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th>序</th>
        <th>标题</th>
        <th class="t-c hidden-sm hidden-xs">图标</th>
        <th class="hidden-sm hidden-xs">网址</th>
        <th class="t-c hidden-xs">排序</th>
        <th class="t-c hidden-xs">状态</th>
        <th class="t-c hidden-xs">推荐</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(++$key+(20*($page-1))); ?></td>
            <td><?php echo e($link->title); ?></td>
            <td class="t-c hidden-sm hidden-xs"><img src="<?php echo e($link->icon??''); ?>" height="25"></td>
            <td class="hidden-sm hidden-xs"><?php echo e($link->link); ?></td>
            <td class="t-c hidden-xs"><?php echo e($link->sorts); ?></td>
            <td class="t-c hidden-xs">
                <?php if($link->is_show==0): ?>
                    <bottn class="btn btn-default btn-sm" onclick="updateLink('<?php echo e($link->id); ?>',1)" name="isshow">隐藏</bottn>
                <?php elseif($link->is_show==1): ?>
                    <bottn class="btn btn-success btn-sm" onclick="updateLink('<?php echo e($link->id); ?>',0)" name="isshow">显示</bottn>
                <?php endif; ?>
            </td>
            <td class="t-c hidden-xs">
                <?php if($link->is_hot==0): ?>
                    <bottn class="btn btn-default btn-sm" onclick="updateHot('<?php echo e($link->id); ?>',1)" name="ishot">否</bottn>
                <?php elseif($link->is_hot==1): ?>
                    <bottn class="btn btn-success btn-sm" onclick="updateHot('<?php echo e($link->id); ?>',0)" name="ishot">是</bottn>
                <?php endif; ?>
            </td>
            <td class="t-c">
                <a href="javascript:" class="btn btn-success btn-sm" onclick="editLink('<?php echo e($link->id); ?>')">详情</a>&nbsp;
                <a href="javascript:" class="btn btn-danger btn-sm" onclick="delLink('<?php echo e($link->id); ?>')">删除</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
   <?php echo e($links->links()); ?>

</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/link/listAjax.blade.php ENDPATH**/ ?>