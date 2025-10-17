@include('UEditor::head')
@include('manager.layouts.common')
<style>
    .form-group{margin-bottom:5px;}
    .form-control{display:inline;}
</style>
<div class="box box-info">
    <div class="box-body" style="padding:15px;">
       {{Form::model('',['class'=>'form-horizontal form-bordered','id'=>'postForm','name'=>'postForm'])}}
        <div class="row mt1">
            <div class="form-group">
                <label for="member_agent_type">会员类型：</label>
            </div>
            <div class="form-group">
				{{Form::radio('member_agent_type','App\Models\Member','App\Models\Member',['class'=>'minimal'])}}会员
				{{Form::radio('member_agent_type','App\Models\Agent','',['class'=>'minimal'])}}代理
				<label for="username" class="ml2 text-red">会员类型，默认发送给会员</label>
            </div>
            <div class="form-group mt1">
                <label for="username">接收会员：</label>
				{{Form::text('username','',['id'=>'username','class'=>'form-control'])}}
            </div>
            <div class="form-group">
                <label for="username" class="text-red">发送给多个会员，请用|符号分隔，如fckk001|fcgood101</label>
            </div>
            <div class="form-group mt1">
                <label for="company_id">发送范围：</label>
                 {{Form::select('company_id',$company,'',['id'=>'company_id','class'=>'form-control selectpicker','title'=>'请先择发送范围'])}}
            </div>
            <div class="form-group mt1">
                <label for="username" class="text-red">请选择范围，指定会员或所有会员</label>
            </div>
            <div class="form-group mt1">
                <label for="message_body" style="float:left;">信息内容：</label>
                <textarea name="message_body" class="form-control" id="message_body" style="height:80px!important;"></textarea>
            </div>

        </div>
       {{Form::close()}}
    </div>
    <div class="box-footer  pull-right">
        <button type="button" class="btn btn-info" id="btn-Add" onclick="createMessage();">发送信息</button>
        <button type="button" class="btn btn-danger" id="btn-close" onclick="layerCloseMe();">关闭本页</button>
    </div>
</div>

<script>
	function createMessage(){
		if($('#username').val()==''&&$('#company_id').val()==null){
			layer.alert('接收用户和公司至少填写或选一顶！',{icon:2,closeBtn:0,offset:['50px']});
			return false;
		}
		if($('#message_body').val()==''){
			layer.alert('请输入信息内容！',{icon:2,closeBtn:0,offset:['50px']});
			return false;
		}
		$.ajax({
			type:'post',
			url:'{{route("messageSave")}}',
			data:$('#postForm').serialize(),
			dataType:'json',
			success:function(data){
				if(data.status==1){
					layer.alert(data.msg,{icon:1,closeBtn:0,offset:['50px']},function(){
						layerCloseMe();
						window.parent.getMessage(1);
					});
				}else{
					layer.alert(data.msg,{icon:2,closeBtn:0,offset:['50px']});
					return false;
				}
			},
			error:function(data){
				layer.alert('网络连接失败，请稍后重试！',{icon:2,closeBtn:0,offset:['50px']});
			}
		});
	}
</script>
