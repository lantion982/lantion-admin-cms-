<aside class="main-sidebar">
    <section class="sidebar" style="height: auto;">
        <!--会员面版-->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?php echo e('/images/admin/user2019.jpg'); ?>" class="img-circle" alt="我的头像">
            </div>
            <div class="pull-left info">
                 <p><?php echo e(Auth::guard('admin')->user()->display_name); ?></p>
                <a href="#" title="在线">
                    <i class="fa fa-circle text-success"></i>online
                </a>
            </div>
        </div>
        <!--会员面版-->
        <!--三级菜单-->
        <ul id="nav" class="sidebar-menu tree" data-widget="tree">
            <li class="treeview">
                <a href="javascript:" onclick="addTabsLocal('dashboard','我的桌面','<?php echo e(route('dashboard')); ?>',this);">
                    <i class="fa fa-home"></i>
                    <span>我的桌面</span>
                </a>
            </li>
            <?php $__currentLoopData = RBAC::getMenuslv3(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="treeview">
                    <a href="#">
                        <?php echo $menu['icon_html']; ?>

                        <span><?php echo e($menu['title']); ?></span>
                        <?php if(isset($menu['subMenu'])): ?>
                            <!--有二级菜单显示向下箭头-->
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        <?php endif; ?>
                    </a>
                    <?php if(isset($menu['subMenu'])): ?>
                        <ul class="treeview-menu">
                            <?php $__currentLoopData = $menu['subMenu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lv2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <?php if(isset($lv2['subMenulv3'])): ?>
                                        <!--有三级子菜单-显示向下箭头-->
                                        <a href="javascript:">
                                            <i class="fa fa-play-circle-o"></i> <span><?php echo e($lv2['title']); ?></span>
                                            <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>
                                        <!--显示三级菜单-->
                                        <ul class="treeview-menu">
                                            <?php $__currentLoopData = $lv2['subMenulv3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lv3): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li>
                                                    <a class="ml1" href="javascript:" onclick="addTabsLocal('<?php echo e($lv3['name']); ?>','<?php echo e($lv3['title']); ?>','<?php echo e($lv3['href']); ?>',this);">
														<i class="fa fa-minus-circle"></i> <span><?php echo e($lv3['title']); ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    <?php else: ?>
                                        <!--无三级子菜单-显示菜并增加点击事件-->
                                        <a href="javascript:" onclick="addTabsLocal('<?php echo e($lv2['name']); ?>','<?php echo e($lv2['title']); ?>','<?php echo e($lv2['href']); ?>',this);">
                                            <i class="fa fa-stop-circle-o"></i> <span><?php echo e($lv2['title']); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

    </section>
</aside>
<?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/layouts/sidebar.blade.php ENDPATH**/ ?>