<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" name="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th>序</th>
        <th>IP地址</th>
        <th class="hidden-xs">主机名称</th>
        <th class="hidden-xs">名单类型</th>
        <th class="hidden-sm hidden-xs">针对平台</th>
        <th>是否生效</th>
        <th class="hidden-sm hidden-xs">备注</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $ipList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(++$key+$page_count*($page-1)); ?></td>
            <td><?php echo e($var->ip_addr); ?></td>
            <td class="hidden-xs"><?php echo e($var->host_name); ?></td>
            <td class="hidden-xs"><?php echo e(config('enums.block_type')[$var->block_type]); ?></td>
            <td class="hidden-sm hidden-xs"><?php echo e(config('enums.host_type')[$var->host_type]); ?></td>
            <td>
                <input type="checkbox" value="<?php echo e($var->id); ?>" class="switch" <?php echo e(str_is($var->is_active,'1')?'checked':''); ?>>
            </td>
            <td class="hidden-sm hidden-xs"><?php echo e(str_limit($var->remarks,32)); ?></td>
            <td class="t-c">
                <a href="#" class="btn btn-info btn-sm" onclick="editIP('<?php echo e($var->id); ?>')">
                    <i class="fa fa-pencil"></i>&nbsp;详情
                </a>
                <a href="javascript:" class="btn btn-danger btn-sm" onclick="delIP('<?php echo e($var->id); ?>')">
                    <i class=" fa fa-trash-o"></i>&nbsp;删除
                </a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
    <div class="col-lg-12">
        <?php echo e($ipList->links()); ?>

    </div>
</div>
<?php /**PATH D:\www\guanglan\admin\resources\views/manager/config/IPList.blade.php ENDPATH**/ ?>