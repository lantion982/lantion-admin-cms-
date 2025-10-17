<header class="main-header">
    <a href="#" class="logo hidden-xs">
        <span class="logo-mini">GL</span>
        <span class="logo-lg"><b>力揽狂蓝</b></span>
    </a>
    <!-- 顶部菜单 -->
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"></a>
        <!-- 右侧菜单 -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown notifications-menu">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"  title="取款统计">
                        <i class="fa fa-credit-card-alt"></i>
                        <span class="label label-warning" id="drawCount">0</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header"></li>
                        <li>
                            <ul class="menu" id="drawCount-ul">

                            </ul>
                        </li>
                        <li class="footer">
                            <a href="javascript:void(0)" onclick="clearDrawCount();">请注意：点击这里清空提示！</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img  src="<?php echo e('/images/admin/user2019.jpg'); ?>" class="user-image" alt="User Image">
                        <span class="hidden-xs"><?php echo e(auth('admin')->user()->login_name); ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="<?php echo e('/images/admin/user2019.jpg'); ?>" class="img-circle" alt="User Image">
                            <p>
                               <?php echo e(auth('admin')->user()->login_name); ?>

                            </p>
                        </li>

                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat" onClick="adminPassword()">修改密码</a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo e(url('/manager/logout')); ?>"  class="btn btn-default btn-flat">退出</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
<audio id="myAudio" src="" controls="controls"  autoplay="autoplay"  style="display:none"></audio>
<?php /**PATH D:\www\ganglan\resources\views/manager/layouts/header.blade.php ENDPATH**/ ?>