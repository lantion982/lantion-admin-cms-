<?php $__env->startSection('content'); ?>
    <div class="row mb1">
        <div class="col-sm-12">
            <h3>会员统计</h3>
        </div>
    </div>
    <div class='row'>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3><?php echo e($userCount); ?></h3>
                    <p>会员总数</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo e($todayUserCount); ?></h3>
                    <p>今日注册</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?php echo e($activeMemberCount); ?></h3>
                    <p> 活动会员</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb1">
        <div class="col-sm-12">
            <h3>财务统计</h3>
        </div>
    </div>
    <div class='row'>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3><?php echo e($todayDeposit); ?>

                    </h3>
                    <p>今日存款</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?php echo e($todayDraw); ?></h3>
                    <p>今日取款</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3><?php echo e($todayTransfer); ?></h3>
                    <p>今日转账</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb1">
        <div class="col-sm-12">
            <h3>额度统计</h3>
        </div>
    </div>
    <div class='row'>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3><?php echo e($quota_amount); ?>

                    </h3>
                    <p>本月额度</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-yellow-active">
                <div class="inner">
                    <h3><?php echo e($remain_amount); ?></h3>
                    <p>剩余额度</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3><?php echo e($regain_amount); ?></h3>
                    <p>下月额度</p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('manager.superUI', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/pic.magicma.net/resources/views/manager/mydesktop.blade.php ENDPATH**/ ?>