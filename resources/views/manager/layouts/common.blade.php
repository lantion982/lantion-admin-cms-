<meta name="csrf-token" content="{{csrf_token()}}">
<!--CSS-->
<!--Flat icon CSS-->
<link href="{{'/css/font-awesome/css/font-awesome.css'}}" rel="stylesheet"/>
<!--AdminLTE CSS-->
<link href="{{'/plus/adminlte2.4.8/dist/css/AdminLTE.css?'.time()}}" rel="stylesheet">
<link href="{{'/plus/adminlte2.4.8/dist/css/skins/_all-skins.min.css'}}" rel="stylesheet">
<!--common CSS-->
<link href="{{'/css/common.css?'.time()}}" rel="stylesheet"/>
<!--Bootstrap & Plus CSS-->
<link href="{{'/plus/bootstrap-3.3.6/css/bootstrap.min.css'}}" rel="stylesheet">
<link href="{{'/plus/bootstrap-switch/css/bootstrap-switch.css'}}" rel="stylesheet"/>
<link href="{{'/plus/bootstrap3-dialog/css/bootstrap-dialog.css'}}" rel="stylesheet"/>
<link href="{{'/plus/bootstrap-select/dist/css/bootstrap-select.min.css'}}" rel="stylesheet"/>
<!--Select2 CSS-->
<link href="{{'/plus/select2/css/select2.css'}}" rel="stylesheet"/>
<link href="{{'/plus/select2/css/select2-bootstrap.min.css'}}" rel="stylesheet"/>
<!--自定义CSS -->
<link href="{{'/css/custom.css?'.time()}}" rel="stylesheet"/>
<link href="{{'/css/xadmin.css?'.time()}}" rel="stylesheet"/>
<!--JS-->
<!--jQuery-->
<script src="{{'/plus/jQuery/jquery-1.11.3.min.js?'.time()}}"></script>
<!-- Bootstrap & Plus-->
<script src="{{'/plus/bootstrap-3.3.6/js/bootstrap.min.js'}}"></script>
<script src="{{'/plus/bootstrap-switch/js/bootstrap-switch.js'}}"></script>
<script src="{{'/plus/bootstrap3-dialog/js/bootstrap-dialog.js'}}"></script>
<script src="{{'/plus/bootstrap-select/dist/js/bootstrap-select.js'}}" ></script>
<script src="{{'/plus/bootstrap-select/dist/js/i18n/defaults-zh_CN.js'}}" ></script>
<script src="{{'/plus/select2/js/select2.full.min.js'}}"></script>
<!--laydate-->
<script src="{{'/plus/layer/laydate/laydate.js'}}" type="text/javascript"></script>
<!--自定义共用js-->
<script src="{{'/js/common.js?'.time()}}"></script>
<script src="{{'/js/manager.common.js?'.time()}}"></script>
<script src="{{'/js/myadmin.js?'.time()}}"></script>
<!--VUE-->
<script src="{{'/js/vue-2.5.16.js?'.time()}}"></script>
<script>
    $(function () {
        App.fixIframeCotent();
        AdminLteStyle();
        App.handleSidebarAjaxContent();
    });
</script>
