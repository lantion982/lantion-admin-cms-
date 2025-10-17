<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
    <tr>
        <th>序</th>
        <th>标题</th>
        <th class="hidden-sm hidden-xs">开始时间</th>
        <th class="hidden-sm hidden-xs">结束时间</th>
        <th class="t-c hidden-xs">排序</th>
        <th class="t-c hidden-xs">状态</th>
        <th class="t-c">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$new): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e(++$key+($page_count*($current_page-1))); ?></td>
            <td><?php echo e(str_limit($new->title,36)); ?></td>
            <td class="hidden-sm hidden-xs"><?php echo e($new->begin_time); ?></td>
            <td class="hidden-sm hidden-xs"><?php echo e($new->end_time); ?></td>
            <td class="t-c hidden-xs"><?php echo e($new->sorts); ?></td>
            <td class="t-c hidden-xs">
                <?php if($new->is_show==0): ?>
                    <bottn class="btn btn-default btn-sm" onclick="newsUpdate('<?php echo e($new->id); ?>',1)">未发布</bottn>
                <?php elseif($new->is_show==1): ?>
                    <bottn class="btn btn-success btn-sm" onclick="newsUpdate('<?php echo e($new->id); ?>',2)">发布中</bottn>
                <?php elseif($new->is_show==2): ?>
                    <bottn class="btn btn-danger btn-sm" onclick="newsUpdate('<?php echo e($new->id); ?>',0)">已结束</bottn>
                <?php endif; ?>
            </td>
            <td class="t-c">
                <a href="javascript:" class="text-blue" onclick="newsEdit('<?php echo e($new->id); ?>')">详情</a>&nbsp;|
                <a href="javascript:" class="text-red" onclick="newsDel('<?php echo e($new->id); ?>')">删除</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
   <?php echo e($news->links()); ?>

</div>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/news/listAjax.blade.php ENDPATH**/ ?>