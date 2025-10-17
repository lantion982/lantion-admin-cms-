@include('manager.layouts.common')
<style>
    .form-group {margin-bottom:5px;}
    .form-control {display: inline;}
</style>
<div class="box box-info" style="padding-left:25px;padding-right:25px; ">
    <div class="box-body"  style="padding-left:35px;">
    {!! Form::model($tempPhone, ['class' => 'form-horizontal form-bordered', 'id' => 'postForm', 'name' => 'postForm' ]) !!}
    {!! Form::hidden('log_phone_sms_id', old('log_phone_sms_id')) !!}
    @if(!\App\Libs\Helper::isSuper())
        {!! Form::hidden('company_id', \App\Libs\Helper::getCompanyId()) !!}
    @endif
        <div class="row mt1">
            <div class="form-group">
                {!! Form::label('labelForName', '手机号码：',['class'=>'col-md-4 control-label']) !!}
                {!! Form::text('phone', old('phone'), ['id'=>'phone','placeholder'=>'手机号码','maxlength'=>'11','class' => 'col-md-8 form-control-static','style'=>'width: 240px;','onkeyup'=>"this.value=this.value.replace(/[^\d.]/g,'')"]) !!}
            </div>
        </div>

        <div class="row mt1">
            <div class="form-group">
                {!! Form::label('labelForMin', '验 证 码：',['class'=>'col-md-4 control-label','style'=>'margin-left:5px;']) !!}
                {!! Form::text('code', old('code'), ['placeholder'=>'验证码','id'=>'code','maxlength'=>'4','class' => 'col-md-8 form-control-static','style'=>'width: 240px;']) !!}
            </div>
        </div>

        <div class="row mt1">
            <div class="form-group">
                {!! Form::label('labelForLoginUrl', '发送时间：',['class'=>'col-md-4 control-label']) !!}
                {!! Form::text('time_send', old('time_send'), ['placeholder'=>'发送时间','id'=>'time_send','class' => 'col-md-8 form-control-static query_time','style'=>'width: 240px;','readonly'=>"readonly"]) !!}
            </div>
        </div>

        <div class="row mt1">
            <div class="form-group">
                {!! Form::label('labelForCompanyName', '到期时间：',['class'=>'col-md-4 control-label']) !!}
                {!! Form::text('time_out', old('time_out'), ['placeholder'=>'到期时间','id'=>'time_out','class' => 'col-md-8 form-control-static query_time','style'=>'width: 240px;','readonly'=>"readonly"]) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <div class="box-footer  pull-right">
        @if(empty($tempPhone))
            <button type="button" class="btn btn-default" id="btn-Add" onclick="createLogViewSMSInfo()">新增验证码</button>
        @else
            <button type="button" class="btn btn-default" id="btn-Update" onclick="updateLogViewSMSInfo()">提交保存</button>
        @endif

        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>

<script>
	$(document).ready(function(){
		lay('.query_time').each(function(){
			laydate.render({
				elem:this
				, type:'datetime'
				, trigger:'click'
			});
		});
	});
	
    function createLogViewSMSInfo(){
	    if($('#phone').val()==''){
		    layer.alert('请输入手机号码！', {icon:2,closeBtn:0});
		    return false;
	    }
	    
	    if($('#code').val()==''){
		    layer.alert('请输入码证码！', {icon:2,closeBtn:0});
		    return false;
	    }
	    
	    if($("#time_send").val()==''){
		    layer.alert('请选择发送时间！', {icon:2,closeBtn:0});
		    return false;
	    }
	
	    if($("#time_out").val()==''){
		    layer.alert('请选择到期时间！', {icon:2,closeBtn:0});
		    return false;
	    }
	    
        $.ajax({
            type: 'post',
            url: '/manager/createLogViewSMSInfo',
            data: $('#postForm').serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg,{icon:1}, function(){
                        layerCloseMe();
                        window.parent.getLogViewSMS(1);
                    });
                }else{
                    layer.alert(data.msg,{icon:2});
                }
            },
            error: function (data) {
                layer.alert('网络连接失败，请稍后重试！', {icon:2,closeBtn:0});
            }
        });
    }


    function updateLogViewSMSInfo(){
	    if($('#phone').val()==''){
		    layer.alert('请输入手机号码！', {icon:2,closeBtn:0});
		    return false;
	    }
	
	    if($('#code').val()==''){
		    layer.alert('请输入码证码！', {icon:2,closeBtn:0});
		    return false;
	    }
	
	    if($("#time_send").val()==''){
		    layer.alert('请选择发送时间！', {icon:2,closeBtn:0});
		    return false;
	    }
	
	    if($("#time_out").val()==''){
		    layer.alert('请选择到期时间！', {icon:2,closeBtn:0});
		    return false;
	    }
	    
        $.ajax({
            type: 'post',
            url: '/manager/updateLogViewSMSInfo',
            data: $('#postForm').serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg,{icon:1}, function(){
                        layerCloseMe();
                        window.parent.getLogViewSMS(parseInt($('#CURRENT_PAGE',window.parent.document).val()));
                    });
                }else{
                    layer.alert(data.msg, {icon:2});
                }
            },
            error: function (data) {
                layer.alert('提交失败！', {icon:2,closeBtn:0});
            }
        });
    }

</script>