<table id="tbl-activities" class="table table-hover">
    <input type="hidden" id="page" value="<?php echo e($page); ?>">
    <thead>
        <tr>
            <th></th>
            <th>显示名称</th>
            <th>路由</th>
            <th>图标</th>
            <th>类型</th>
            <th style="text-align:center;">操作</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <label for="id-<?php echo e($permission->id); ?>"></label>
                    <?php if(count($permission->sub_permission)>0): ?>
                        <a class="show-sub-permissions" data-id="<?php echo e($permission['id']); ?>">
                            <span class="glyphicon glyphicon-chevron-right"></span>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <p class="text-info"><?php echo e($permission->title); ?></p>
                </td>
                <td><?php echo e($permission->name); ?></td>
                <td><?php echo e($permission->icon); ?></td>
                <td>
                    <?php if($permission->ptype == 'menu'): ?>
                        <span class="label label-danger"><?php echo e($permission->ptype); ?></span>
                    <?php else: ?>
                        <span class="label label-success"><?php echo e($permission->ptype); ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <a class="btn btn-info btn-sm" onclick="permissionInfo('<?php echo e($permission->id); ?>')">
                        <i class="fa fa-pencil"></i>&nbsp;编辑
                    </a>
                    <a class="btn btn-danger btn-sm" onclick="delPermission('<?php echo e($permission->id); ?>')">
                        <i class=" fa fa-trash-o"></i>&nbsp;删除
                    </a>
                </td>
            </tr>
            <!--二级-->
            <?php if(count($permission->sub_permission)>0): ?>
                <?php $__currentLoopData = $permission->sub_permission; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($sub->permission_type <> 'func'): ?>
                        <tr class=" parent-permission-<?php echo e($permission->id); ?> hide">
                            <td>
                                <label></label>
                                <label for="id-<?php echo e($sub->id); ?>"></label>
                            </td>
                            <td>
                                |-- <?php echo e($sub->title); ?>

                                <?php if(count($sub->sub_permission)>0): ?>
                                    <a class="show-sub-permissions" data-id="<?php echo e($sub['id']); ?>">
                                        <span class="glyphicon glyphicon-chevron-right"></span>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($sub->name); ?></td>
                            <td><?php echo e($sub->icon); ?></td>
                            <td>
                                <?php if($sub->ptype == 'menu'): ?>
                                    <span class="label label-danger"><?php echo e($sub->ptype); ?></span>
                                <?php else: ?>
                                    <span class="label label-success"><?php echo e($sub->ptype); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <a class="btn btn-info btn-sm" onclick="permissionInfo('<?php echo e($sub->id); ?>')">
                                    <i class="fa fa-pencil"></i>&nbsp;编辑
                                </a>
                                <?php if($sub->ptype == 'menu'): ?>
                                    <a class="btn btn-success btn-sm" onclick="createSubPermission('<?php echo e($sub->id); ?>')"
                                        data-original-title="新增下级权限">
                                        <i class="glyphicon glyphicon-plus"></i>&nbsp;新增权限
                                    </a>
                                <?php endif; ?>
                                <?php if($sub->ptype == 'page'): ?>
                                    <a class="btn btn-success btn-sm" onclick="permPageFunc('<?php echo e($sub->id); ?>')">
                                        <i class="fa fa-wrench"></i>&nbsp;页面权限</a>
                                <?php endif; ?>
                                <a class="btn btn-danger btn-sm" onclick="delPermission('<?php echo e($sub->id); ?>')">
                                    <i class=" fa fa-trash-o"></i>&nbsp;删除
                                </a>
                            </td>
                        </tr>
                        <!--三级-->
                        <?php if(count($sub->sub_permission)>0): ?>
                            <?php $__currentLoopData = $sub->sub_permission; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lv3): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class=" parent-permission-<?php echo e($sub->id); ?> hide">
                                    <td>
                                        <label></label>
                                        <label for="id-<?php echo e($lv3->id); ?>"></label>
                                    </td>
                                    <td>
                                        &nbsp;&nbsp;&nbsp;|---<?php echo e($lv3->title); ?>

                                    </td>
                                    <td><?php echo e($lv3->name); ?></td>
                                    <td><?php echo e($lv3->icon); ?></td>
                                    <td>
                                        <?php if($lv3->ptype == 'menu'): ?>
                                            <span class="label label-danger"><?php echo e($lv3->ptype); ?></span>
                                        <?php elseif($lv3->ptype == 'func'): ?>
                                            <span class="label label-warning"><?php echo e($lv3->ptype); ?></span>
                                        <?php else: ?>
                                            <span class="label label-success"><?php echo e($lv3->ptype); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a class="btn btn-info btn-sm" onclick="permissionInfo('<?php echo e($lv3->id); ?>')">
                                            <i class="fa fa-pencil"></i>&nbsp;编辑
                                        </a>
                                        <a class="btn btn-success btn-sm <?php if($lv3->ptype != 'page'): ?> disabled <?php endif; ?>" onclick="permPageFunc('<?php echo e($lv3->id); ?>')">
                                            <i class="fa fa-wrench"></i>&nbsp;页面权限
                                        </a>
                                        <a class="btn btn-danger btn-sm" onclick="delPermission('<?php echo e($lv3->id); ?>')">
                                            <i class=" fa fa-trash-o"></i>&nbsp;删除
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="box-info">
    <div class="col-lg-12">
        <?php echo e($permissions->links()); ?>

    </div>
</div>
<?php /**PATH D:\www\ganglan\resources\views/manager/admin/permissionList.blade.php ENDPATH**/ ?>