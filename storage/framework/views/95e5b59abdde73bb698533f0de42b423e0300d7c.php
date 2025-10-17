<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
</head>
<?php echo $__env->make('manager.layouts.common', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<style>
    .content {min-height: 870px;}
</style>

<body class="hold-transition skin-blue sidebar-mini fixed">
    <section class="container-fluid">
        <?php echo $__env->yieldContent('content'); ?>
    </section>

    
    <script language="javascript">
       $("[class='modal fade']").on('show.bs.modal', function (e) {
            $(this).find('.modal-dialog').css({
                'margin-top': function () {
                    return ($(window).height()/4);
                }
            });
        });
       function centerModals(){
            $('.modal').each(function(i){
                var $clone = $(this).clone().css('display', 'block').appendTo('body');
                var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
                top = top > 0 ? top : 0;
                $clone.remove();
                $(this).find('.modal-content').css("margin-top", top);
            });
        }

       $(document).ready(function() {
           $('.modal').on('show.bs.modal', centerModals);
           $(window).on('resize', centerModals);
           lay('.query_time').each(function(){
               laydate.render({
                   elem:this
                   ,type:'datetime'
                   ,trigger:'click'
               });
           });
           lay('.query_date').each(function(){
               laydate.render({
                   elem: this
                   ,trigger: 'click'
               });
           });
       });

    </script>
    <!--Jquery DataTables-->
    <link href="<?php echo e('/plus/dataTables/css/jquery.dataTables.css'); ?>" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="<?php echo e('/plus/dataTables/js/jquery.dataTables.js'); ?>"></script>
    <!--Jquery 输入框限制-->
    <script type="text/javascript" src="<?php echo e('/plus/inputmask/jquery.inputmask.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo e('/plus/inputmask/jquery.inputmask.extensions.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo e('/plus/inputmask/jquery.inputmask.date.extensions.js'); ?>"></script>
    <!--LayUI laydate-->
    <script type="text/javascript" src="<?php echo e('/plus/layer/laydate/laydate.js'); ?>"></script>
</body>
</html>
<?php /**PATH D:\www\ganglan\resources\views/manager/superUI.blade.php ENDPATH**/ ?>