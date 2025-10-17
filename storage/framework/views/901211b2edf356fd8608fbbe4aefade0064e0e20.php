<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<!--CSS-->
<!--Flat icon CSS-->
<link href="<?php echo e('/css/font-awesome/css/font-awesome.css'); ?>" rel="stylesheet"/>
<!--AdminLTE CSS-->
<link href="<?php echo e('/plus/adminlte2.4.8/dist/css/AdminLTE.css?'.time()); ?>" rel="stylesheet">
<link href="<?php echo e('/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'); ?>" rel="stylesheet">
<!--common CSS-->
<link href="<?php echo e('/css/common.css?'.time()); ?>" rel="stylesheet"/>
<!--Bootstrap & Plus CSS-->
<link href="<?php echo e('/plus/bootstrap-3.3.6/css/bootstrap.min.css'); ?>" rel="stylesheet">
<link href="<?php echo e('/plus/bootstrap-switch/css/bootstrap-switch.css'); ?>" rel="stylesheet"/>
<link href="<?php echo e('/plus/bootstrap3-dialog/css/bootstrap-dialog.css'); ?>" rel="stylesheet"/>
<link href="<?php echo e('/plus/bootstrap-select/dist/css/bootstrap-select.min.css'); ?>" rel="stylesheet"/>
<!--Select2 CSS-->
<link href="<?php echo e('/plus/select2/css/select2.css'); ?>" rel="stylesheet"/>
<link href="<?php echo e('/plus/select2/css/select2-bootstrap.min.css'); ?>" rel="stylesheet"/>
<!--自定义CSS -->
<link href="<?php echo e('/css/custom.css?'.time()); ?>" rel="stylesheet"/>
<link href="<?php echo e('/css/xadmin.css?'.time()); ?>" rel="stylesheet"/>
<!--JS-->
<!--jQuery-->
<script src="<?php echo e('/plus/jQuery/jquery-1.11.3.min.js?'.time()); ?>"></script>
<!-- Bootstrap & Plus-->
<script src="<?php echo e('/plus/bootstrap-3.3.6/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo e('/plus/bootstrap-switch/js/bootstrap-switch.js'); ?>"></script>
<script src="<?php echo e('/plus/bootstrap3-dialog/js/bootstrap-dialog.js'); ?>"></script>
<script src="<?php echo e('/plus/bootstrap-select/dist/js/bootstrap-select.js'); ?>" ></script>
<script src="<?php echo e('/plus/bootstrap-select/dist/js/i18n/defaults-zh_CN.js'); ?>" ></script>
<script src="<?php echo e('/plus/select2/js/select2.full.min.js'); ?>"></script>
<!--laydate-->
<script src="<?php echo e('/plus/layer/laydate/laydate.js'); ?>" type="text/javascript"></script>
<!--自定义共用js-->
<script src="<?php echo e('/js/common.js?'.time()); ?>"></script>
<script src="<?php echo e('/js/manager.common.js?'.time()); ?>"></script>
<script src="<?php echo e('/js/myadmin.js?'.time()); ?>"></script>
<!--VUE-->
<script src="<?php echo e('/js/vue-2.5.16.js?'.time()); ?>"></script>
<script>
    $(function () {
        App.fixIframeCotent();
        AdminLteStyle();
        App.handleSidebarAjaxContent();
    });
</script>
<?php /**PATH D:\www\ganglan\resources\views/manager/layouts/common.blade.php ENDPATH**/ ?>